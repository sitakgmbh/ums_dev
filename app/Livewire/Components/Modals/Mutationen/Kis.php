<?php

namespace App\Livewire\Components\Modals\Mutationen;

use App\Livewire\Components\Modals\BaseModal;
use App\Models\Mutation;
use App\Services\Orbis\OrbisUserUpdater;
use App\Services\Orbis\OrbisHelper;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Validate;

class Kis extends BaseModal
{
    public ?Mutation $entry = null;
    public string $modalType = 'mutation';
    
    #[Validate('required|string|min:2')]
    public string $username = '';
    
    public ?array $userDetails = null;
    public ?array $employeeDetails = null;
    public bool $userFound = false;
    public bool $isSearching = false;
    
    #[Validate('nullable|integer')]
    public ?int $employeeFunction = null;
    
    #[Validate('required|in:merge,replace')]
    public string $permissionMode = 'merge';
    
    public array $selectedOrgUnits = [];
    public array $selectedOrgGroups = [];
    public array $selectedRoles = [];
    public ?int $selectedUserId = null;
    
    public string $errorMessage = '';
    public string $successMessage = '';

    protected function openWith(array $payload): bool
    {
        if (!isset($payload['entryId'])) {
            return false;
        }

        $this->entry = Mutation::with('vorlageBenutzer')->find($payload['entryId']);
        
        if (!$this->entry) {
            return false;
        }

        $this->reset([
            'username', 
            'userDetails', 
            'employeeDetails', 
            'userFound', 
            'errorMessage',
            'successMessage',
            'selectedOrgUnits',
            'selectedOrgGroups',
            'selectedRoles',
            'selectedUserId'
        ]);
        
        if ($this->entry->vorlageBenutzer && $this->entry->vorlageBenutzer->username) {
            $this->username = strtoupper($this->entry->vorlageBenutzer->username);
        } else {
            $this->username = strtoupper($this->entry->berechtigung ?? '');
        }
        
        $this->permissionMode = $this->shouldUseMergeMode() ? 'merge' : 'replace';
        
        if ($this->entry->is_lei) {
            $this->employeeFunction = 34;
        }
        
        $this->title = "KIS Benutzerverwaltung";
        $this->size = "xl";
        $this->position = "centered";
        $this->backdrop = true;
        $this->headerBg = "bg-primary";
        $this->headerText = "text-white";

        return true;
    }
    
    protected function shouldUseMergeMode(): bool
    {
        return !empty($this->entry->abteilung2_id);
    }

    public function searchUser(OrbisHelper $helper): void
    {
        $this->errorMessage   = '';
        $this->successMessage = '';
        $this->userFound      = false;
        $this->isSearching    = true;

        try {
            $this->validate(['username' => 'required|string|min:2']);
            
            $details = $helper->getUserDetails($this->username);
            
            $this->userDetails     = $details['user'];
            $this->employeeDetails = $details['employee'];
            $this->userFound       = true;
			$this->employeeFunction = $this->employeeDetails['employeefunction']['id'] ?? null;
            $this->preselectItems();

        } catch (\Exception $e) {
            $this->errorMessage = $e->getMessage();
        } finally {
            $this->isSearching = false;
        }
    }

    protected function preselectItems(): void
    {
        $this->selectedOrgUnits = collect($this->employeeDetails['organizationalunits'] ?? [])
            ->pluck('id')->toArray();

        $this->selectedOrgGroups = collect($this->employeeDetails['organizationalunitgroups'] ?? [])
            ->pluck('id')->toArray();

		$users = $this->employeeDetails['users'] ?? [];

        if (!empty($users)) {
            $first = $users[0];
            $this->selectedUserId = $first['id'];
            $this->selectedRoles  = collect($first['roles'] ?? [])
                ->pluck('id')->toArray();
        }
    }

    public function updatedSelectedUserId($userId): void
    {
        $users = $this->employeeDetails['users'] ?? [];
        $selectedUser = collect($users)->firstWhere('id', $userId);

        if ($selectedUser) {
            $this->selectedRoles = collect($selectedUser['roles'] ?? [])
                ->pluck('id')->toArray();
        }
    }

    public function submitUser(OrbisUserUpdater $updater): void
    {
        $this->errorMessage   = '';
        $this->successMessage = '';

        if (!$this->employeeFunction) {
            $this->dispatch('confirm-no-function');
            return;
        }

        $this->processSubmit($updater);
    }

    public function confirmSubmitWithoutFunction(OrbisUserUpdater $updater): void
    {
        $this->processSubmit($updater);
    }

    protected function processSubmit(OrbisUserUpdater $updater): void
    {
        try {
            $this->validate([
                'employeeFunction' => 'nullable|integer',
                'permissionMode'   => 'required|in:merge,replace',
                'selectedUserId'   => 'required|integer',
            ]);

            // Referenzuser
            $users = $this->employeeDetails['users'] ?? [];
            $selectedUser   = collect($users)->firstWhere('id', $this->selectedUserId);
            $referenceUser  = $selectedUser['username'] ?? null;

            // Orgunits
            $orgUnits = collect($this->selectedOrgUnits)->map(function ($id) {
                $unit = collect($this->employeeDetails['organizationalunits'] ?? [])
                    ->firstWhere('id', $id);

                $r = ['id' => $id];
                if (isset($unit['rank']['id'])) {
                    $r['rank'] = $unit['rank']['id'];
                }

                return $r;
            })->toArray();

            $input = [
                'username'         => $this->username,
                'referenceUser'    => $referenceUser,
                'orgunits'         => $orgUnits,
                'orggroups'        => $this->selectedOrgGroups,
                'roles'            => $this->selectedRoles,
                'employeeStateId'  => $this->employeeDetails['state']['id'] ?? null,
                'employeeFunction' => $this->employeeFunction,
                'permissionMode'   => $this->permissionMode,
            ];

            $result = app(OrbisUserUpdater::class)->update($this->entry->id, $input);

            if ($result['success']) {
                $this->successMessage = implode('<br>', $result['log']);
                $this->entry->update(['status_kis' => 2]);
                $this->dispatch('kis-user-updated', log: $result['log']);
            }

        } catch (\Exception $e) {
            $this->errorMessage = 'Fehler: '.$e->getMessage();
        }
    }

    public function markAsComplete(): void
    {
        if ($this->entry) {
            $this->entry->update(['status_kis' => 2]);
            $this->successMessage = "Status erfolgreich auf 'Erledigt' gesetzt.";
            $this->dispatch('kis-updated');
        }
    }

    public function render()
    {
        return view('livewire.components.modals.mutationen.kis');
    }
}
