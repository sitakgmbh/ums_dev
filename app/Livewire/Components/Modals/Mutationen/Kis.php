<?php

namespace App\Livewire\Components\Modals\Mutationen;

use App\Livewire\Components\Modals\BaseModal;
use App\Models\Mutation;
use App\Services\Orbis\OrbisUserService;
use App\Services\Orbis\Exceptions\OrbisValidationException;
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
        if (!isset($payload['entryId'])) 
		{
            return false;
        }

		$this->entry = Mutation::with('vorlageBenutzer')->find($payload['entryId']);
        
        if (!$this->entry) 
		{
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
        
        $this->modalType = $payload['type'] ?? 'mutation';
        
        if ($this->entry->vorlageBenutzer && $this->entry->vorlageBenutzer->username) 
		{
            $this->username = strtoupper($this->entry->vorlageBenutzer->username);
        } 
		else 
		{
            $this->username = strtoupper($this->entry->berechtigung ?? '');
        }
        
        $this->permissionMode = $this->shouldUseMergeMode() ? 'merge' : 'replace';
        
        if ($this->entry->is_lei) 
		{
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
        $zweiteAbteilung = $this->entry->abteilung2_id ?? null;
        return !empty($zweiteAbteilung) && $zweiteAbteilung !== '0' && $zweiteAbteilung !== 0;
    }

    public function searchUser(OrbisUserService $service): void
    {
        $this->errorMessage = '';
        $this->successMessage = '';
        $this->userFound = false;
        $this->isSearching = true;

        try 
		{
            $this->validate(['username' => 'required|string|min:2']);
            
            $details = $service->getUserDetails($this->username);
            
            $this->userDetails = $details['user'];
            $this->employeeDetails = $details['employee'];
            $this->userFound = true;
            
            $this->preselectItems();
        } 
		catch (\InvalidArgumentException $e) 
		{
            $this->errorMessage = $e->getMessage();
        } 
		catch (\RuntimeException $e) 
		{
            $statusCode = $e->getCode();
			
            if ($statusCode === 404) 
			{
                $this->errorMessage = "Benutzer '{$this->username}' wurde nicht gefunden.";
            } 
			else 
			{
                $this->errorMessage = $e->getMessage();
            }
			
            Log::error('Orbis API-Fehler bei Benutzersuche', [
                'username' => $this->username,
                'status' => $statusCode,
                'error' => $e->getMessage()
            ]);
        } 
		catch (\Exception $e) 
		{
            Log::error('Fehler bei Orbis-Benutzersuche', [
                'username' => $this->username,
                'error' => $e->getMessage()
            ]);
			
            $this->errorMessage = 'Ein Fehler ist aufgetreten. Bitte versuche es erneut.';
        } 
		finally 
		{
            $this->isSearching = false;
        }
    }
    
    protected function preselectItems(): void
    {
        $this->selectedOrgUnits = collect($this->employeeDetails['organizationalunits'] ?? [])
            ->pluck('id')
            ->toArray();
        
        $this->selectedOrgGroups = collect($this->employeeDetails['organizationalunitgroups'] ?? [])
            ->pluck('id')
            ->toArray();
        
        $users = $this->employeeDetails['users'] ?? [];
		
        if (!empty($users)) 
		{
            $firstUser = $users[0];
            $this->selectedUserId = $firstUser['id'];
            
            $this->selectedRoles = collect($firstUser['roles'] ?? [])
                ->pluck('id')
                ->toArray();
        }
    }
    
    public function updatedSelectedUserId($userId): void
    {
        $users = $this->employeeDetails['users'] ?? [];
        $selectedUser = collect($users)->firstWhere('id', $userId);
        
        if ($selectedUser) 
		{
            $this->selectedRoles = collect($selectedUser['roles'] ?? [])
                ->pluck('id')
                ->toArray();
        }
    }

    public function submitUser(OrbisUserService $service): void
    {
        $this->errorMessage = '';
        $this->successMessage = '';

        if (!$this->employeeFunction) 
		{
            $this->dispatch('confirm-no-function');
            return;
        }
        
        $this->processSubmit($service);
    }
    
    public function confirmSubmitWithoutFunction(OrbisUserService $service): void
    {
        $this->processSubmit($service);
    }

    public function markAsComplete(): void
    {
        if ($this->entry) 
		{
            $this->entry->update(['status_kis' => 2]);
            $this->successMessage = "Status erfolgreich auf 'Erledigt' gesetzt.";
            $this->dispatch('kis-updated');
            
            $this->dispatch('close-modal-delayed');
        }
    }

    protected function processSubmit(OrbisUserService $service): void
    {
        try 
		{
            $this->validate([
                'employeeFunction' => 'nullable|integer',
                'permissionMode' => 'required|in:merge,replace',
                'selectedUserId' => 'required|integer',
            ]);

            $users = $this->employeeDetails['users'] ?? [];
            $selectedUser = collect($users)->firstWhere('id', $this->selectedUserId);
            $referenceUser = $selectedUser['username'] ?? null;

            $orgUnits = collect($this->selectedOrgUnits)->map(function ($unitId) {
                $unit = collect($this->employeeDetails['organizationalunits'] ?? [])
                    ->firstWhere('id', $unitId);
                
                $result = ['id' => $unitId];
                if (isset($unit['rank']['id'])) {
                    $result['rank'] = $unit['rank']['id'];
                }
                return $result;
            })->toArray();

            $input = [
                'username' => $this->username,
                'referenceUser' => $referenceUser,
                'orgunits' => $orgUnits,
                'orggroups' => $this->selectedOrgGroups,
                'roles' => $this->selectedRoles,
                'employeeStateId' => $this->employeeDetails['state']['id'] ?? null,
                'employeeFunction' => $this->employeeFunction,
                'permissionMode' => $this->permissionMode,
            ];

            $result = $service->updateKisUser($this->entry->id, $input);

            if ($result['success']) 
			{
                $this->successMessage = implode('<br>', $result['log']);
                $this->entry->update(['status_kis' => 2]);
                $this->dispatch('kis-user-updated', log: $result['log']);
                
                $this->dispatch('close-modal-delayed');
            }

        } 
		catch (\Illuminate\Validation\ValidationException $e) 
		{
            $this->errorMessage = 'Bitte überprüfe deine Eingaben.';
        } 
		catch (\Exception $e) 
		{
            Log::error('Fehler beim Erstellen des KIS-Benutzers', [
                'entry_id' => $this->entry->id,
                'error' => $e->getMessage()
            ]);
			
            $this->errorMessage = 'Fehler beim Erstellen des Benutzers: ' . $e->getMessage();
        }
    }

    public function render()
    {
        return view('livewire.components.modals.mutationen.kis');
    }
}