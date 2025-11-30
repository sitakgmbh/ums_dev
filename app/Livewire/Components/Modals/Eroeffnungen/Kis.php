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

        if ($this->entry->is_lei) {
            $this->employeeFunction = 34;
        }

        $this->title = "KIS Benutzerverwaltung";
        $this->size = "xl";
        $this->position = "centered";
        $this->backdrop = true;

        return true;
    }

    public function submitUser(OrbisUserCreator $creator): void
    {
        $this->errorMessage = '';
        $this->successMessage = '';

        try {
            $input = [
                'username'          => $this->username,
                'referenceUser'     => null,
                'orgunits'          => collect($this->selectedOrgUnits)
                    ->map(fn($id) => collect($this->employeeDetails['organizationalunits'] ?? [])
                        ->firstWhere('id', $id)
                    )
                    ->map(fn($u) => [
                        'id'   => $u['id'],
                        'rank' => $u['rank']['id'] ?? null
                    ])
                    ->toArray(),
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
            }

        } catch (\Exception $e) {
            $this->errorMessage = "Fehler: ".$e->getMessage();
        }
    }

    public function render()
    {
        return view('livewire.components.modals.eroeffnungen.kis');
    }
}
