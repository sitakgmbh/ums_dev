<?php

namespace App\Livewire\Components\Modals\Eroeffnungen;

use App\Livewire\Components\Modals\BaseModal;
use App\Models\Eroeffnung;
use App\Services\Orbis\OrbisUserCreator;
use App\Services\Orbis\OrbisHelper;
use Livewire\Attributes\Validate;

class Kis extends BaseModal
{
    public ?Eroeffnung $entry = null;
    public string $modalType = 'eroeffnung';

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

        $this->entry = Eroeffnung::with('vorlageBenutzer')->find($payload['entryId']);

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

        $this->modalType = $payload['type'] ?? 'eroeffnung';

        if ($this->entry->vorlageBenutzer && $this->entry->vorlageBenutzer->username) {
            $this->username = strtoupper($this->entry->vorlageBenutzer->username);
        } else {
            $this->username = strtoupper($this->entry->berechtigung ?? '');
        }

        $this->permissionMode = $this->shouldUseMergeMode() ? 'merge' : 'replace';

        if ($this->entry->is_lei) {
            $this->employeeFunction = 34;
        }

        return true;
    }

    protected function shouldUseMergeMode(): bool
    {
        return !empty($this->entry->abteilung2_id);
    }

    public function searchUser(OrbisHelper $helper): void
    {
        $this->errorMessage = '';
        $this->successMessage = '';
        $this->userFound = false;
        $this->isSearching = true;

        try {
            $this->validate(['username' => 'required|string|min:2']);

            $details = $helper->getUserDetails($this->username);

            $this->userDetails     = $details['user'];
            $this->employeeDetails = $details['employee'];
            $this->userFound       = true;
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

    public function submitUser(OrbisUserCreator $creator): void
    {
        $this->processSubmit($creator);
    }

    public function confirmSubmitWithoutFunction(OrbisUserCreator $creator): void
    {
        $this->processSubmit($creator);
    }

    protected function processSubmit(OrbisUserCreator $creator): void
    {
        $this->errorMessage = '';
        $this->successMessage = '';

        try {
            $this->validate([
                'employeeFunction' => 'nullable|integer',
                'permissionMode'   => 'required|in:merge,replace',
            ]);

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
                'username'          => $this->username,
                'orgunits'          => $orgUnits,
                'orggroups'         => $this->selectedOrgGroups,
                'roles'             => $this->selectedRoles,
                'employeeStateId'   => $this->employeeDetails['state']['id'] ?? null,
                'employeeFunction'  => $this->employeeFunction,
                'permissionMode'    => $this->permissionMode,
            ];

            $result = $creator->create($this->entry->id, $input);

            if ($result['success']) {
                $this->successMessage = implode('<br>', $result['log']);
                $this->entry->update(['status_kis' => 2]);
                $this->dispatch('kis-user-updated', log: $result['log']);
            }

        } catch (\Exception $e) {
            $this->errorMessage = 'Fehler: ' . $e->getMessage();
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
        return view('livewire.components.modals.eroeffnungen.kis');
    }
}
