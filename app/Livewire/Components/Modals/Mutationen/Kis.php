<?php

namespace App\Livewire\Components\Modals\Mutationen;

use App\Livewire\Components\Modals\BaseModal;
use App\Models\Mutation;
use App\Services\Orbis\OrbisUserUpdater;
use App\Services\Orbis\OrbisHelper;
use Livewire\Attributes\Validate;
use App\Utils\Logging\Logger;

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

	public string $funktionAktuell = '';
	public string $funktionErwartet = '';
	public bool $funktionAbweichend = false;

    protected function openWith(array $payload): bool
    {
        if (!isset($payload['entryId'])) return false;

        $this->entry = Mutation::with('vorlageBenutzer')->find($payload['entryId']);
        if (!$this->entry) return false;

        $this->reset([
            'username', 'userDetails', 'employeeDetails', 'userFound',
            'errorMessage', 'successMessage',
            'selectedOrgUnits', 'selectedOrgGroups', 'selectedRoles',
            'selectedUserId'
        ]);

        $this->username = strtoupper(
            $this->entry->vorlageBenutzer->username
            ?? $this->entry->berechtigung
            ?? ''
        );

        $this->permissionMode = !empty($this->entry->abteilung2_id)
            ? 'merge'
            : 'replace';

        if ($this->entry->is_lei) {
            $this->employeeFunction = 34;
        }

		// Erwartete Funktion aus dem Antrag
		$this->funktionErwartet = $this->entry->funktion->name ?? '';

		// Initialer aktueller Wert: erst mal gleich wie erwartet
		$this->funktionAktuell = $this->funktionErwartet;

		// Noch keine Abweichung
		$this->funktionAbweichend = false;


        $this->title = "KIS Benutzerverwaltung";
        $this->size = "xl";
        $this->position = "centered";

        return true;
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
			
			Logger::debug("EMPLOYEE DETAILS: " . json_encode($this->employeeDetails, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

			// ================================
			// Mitarbeiterfunktion AUTOVORSELECTION
			// ================================
			$assignments = $this->employeeDetails['employeefunctionassignments'] ?? [];

			$first = $assignments[0]['employeefunction']['id'] ?? null;

			if ($first === 34) {
				$this->employeeFunction = 34; // Leistungserbringer
			} elseif ($first === 74) {
				$this->employeeFunction = 74; // Pflege
			} else {
				$this->employeeFunction = null; // Keine Funktion
			}


			// ================================
			// OrgUnits + OrgGroups + Rollen einlesen
			// ================================
			$this->preselectItems();

			// Automatisch ersten User setzen (falls keiner manuell gewaehlt)
			if (!$this->selectedUserId && !empty($this->employeeDetails['users'])) {
				$this->selectedUserId = $this->employeeDetails['users'][0]['id'];
			}

			// ================================
			// Funktionsabweichung pruefen
			// ================================
			$funktionOrbis = $this->employeeDetails['state']['longname'] ?? '';
			$this->funktionAktuell = $funktionOrbis;

			$this->funktionAbweichend =
				trim($funktionOrbis) !== trim($this->funktionErwartet);

		} catch (\Exception $e) {
			$this->errorMessage = $e->getMessage();
		} finally {
			$this->isSearching = false;
		}
	}


    protected function preselectItems(): void
    {
        $this->selectedOrgUnits =
            collect($this->employeeDetails['organizationalunits'] ?? [])
            ->pluck('id')->toArray();

        $this->selectedOrgGroups =
            collect($this->employeeDetails['organizationalunitgroups'] ?? [])
            ->pluck('id')->toArray();

        $users = $this->employeeDetails['users'] ?? [];

        if (!empty($users)) {
            $first = $users[0];

            $this->selectedUserId = $first['id'];
            $this->selectedRoles =
                collect($first['roles'] ?? [])
                ->pluck('id')->toArray();
        }
    }

    public function updatedSelectedUserId($userId): void
    {
        $users = $this->employeeDetails['users'] ?? [];
        $selectedUser = collect($users)->firstWhere('id', $userId);

        if ($selectedUser) {
            $this->selectedRoles =
                collect($selectedUser['roles'] ?? [])
                ->pluck('id')->toArray();
        }
    }

public function submitUser(): void
{
    $this->errorMessage   = '';
    $this->successMessage = '';

    if (!$this->employeeFunction) {
        $this->dispatch('confirm-no-function');
        return;
    }

    $updater = app(OrbisUserUpdater::class);
    $this->processSubmit($updater);
}

public function confirmSubmitWithoutFunction(): void
{
    $updater = app(OrbisUserUpdater::class);
    $this->processSubmit($updater);
}




    protected function processSubmit(OrbisUserUpdater $updater): void
    {
        try {
            $this->validate([
                'employeeFunction' => 'nullable|integer',
                'permissionMode'   => 'required|in:merge,replace',
                'selectedUserId'   => 'required|integer'
            ]);

            // Lookup
            $users = $this->employeeDetails['users'] ?? [];
            $selectedUser = collect($users)->firstWhere('id', $this->selectedUserId);

            // Orgunits → id + rank
            $orgUnits = collect($this->selectedOrgUnits)->map(function ($id) {
                $unit = collect($this->employeeDetails['organizationalunits'] ?? [])
                    ->firstWhere('id', $id);

                $r = ['id' => $id];

                if (isset($unit['rank']['id'])) {
                    $r['rank'] = $unit['rank']['id'];
                }

                return $r;
            })->toArray();

            // Input identisch wie Eroeffnung
            $input = [
                'username'          => $this->username,
                'referenceUser'     => $selectedUser['username'] ?? null,

                'orgunits'          => $orgUnits,
                'orggroups'         => $this->selectedOrgGroups,
                'roles'             => $this->selectedRoles,

			    'signinglevel'      => $this->employeeDetails['signinglevel']['id'] ?? null,

                'orgunits_lookup'   => $this->employeeDetails['organizationalunits'] ?? [],
                'orggroups_lookup'  => $this->employeeDetails['organizationalunitgroups'] ?? [],
                'roles_lookup'      => $selectedUser['roles'] ?? [],

                'employeeStateId'   => $this->employeeDetails['state']['id'] ?? null,
                'employeeFunction'  => $this->employeeFunction,
                'permissionMode'    => $this->permissionMode
            ];

            $result = $updater->update($this->entry->id, $input);

			if ($result['success']) {
				$this->successMessage = implode('<br>', $result['log']);
				// $this->entry->update(['status_kis' => 2]);
				$this->dispatch('kis-user-updated', log: $result['log']);
			} else {
				$this->errorMessage = implode('<br>', $result['log']);
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
        return view('livewire.components.modals.mutationen.kis');
    }
}
