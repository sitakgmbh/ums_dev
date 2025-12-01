<?php

namespace App\Services\Orbis;

use App\Models\Mutation;
use App\Services\Orbis\OrbisClient;
use App\Services\Orbis\OrbisHelper;
use App\Utils\Logging\Logger;

class OrbisUserUpdater
{
    private OrbisClient $client;
    private OrbisHelper $helper;

    public function __construct(OrbisClient $client, OrbisHelper $helper)
    {
        $this->client = $client;
        $this->helper = $helper;
    }

	public function update(int $id): array
	{
		$log = [];

		$entry = Mutation::find($id);

		if (!$entry || empty($entry->benutzername)) {
			$log[] = "Kein gueltiger Antrag gefunden";
			return ["success" => false, "log" => $log];
		}

		$input = request()->all();

		// Mapping
		$input['orgunits']  = $input['selectedOrgUnits']  ?? [];
		$input['orggroups'] = $input['selectedOrgGroups'] ?? [];
		$input['roles']     = $input['selectedRoles']     ?? [];

		// Lookups aus Livewire (Namen!)
		$lookupOe  = $input['orgunits_lookup']  ?? [];
		$lookupGrp = $input['orggroups_lookup'] ?? [];
		$lookupRol = $input['roles_lookup']     ?? [];

		// Validation
		$this->helper->validateInput($input);

		$username = strtoupper($entry->benutzername);
		$today = date("Y-m-d");

		$orgUnits            = $input["orgunits"]  ?? [];
		$orgGroups           = $input["orggroups"] ?? [];
		$roles               = $input["roles"]     ?? [];
		$employeeFunctionId  = $input["employeeFunction"] ?? null;

		if (!is_array($roles)) {
			$roles = [];
		}

		// User suchen
		$user = $this->helper->getUserByUsername($username);
		if (!$user || !isset($user["id"])) {
			$log[] = "Benutzer '{$username}' nicht gefunden";
			return ["success" => false, "log" => $log];
		}
		$userId = $user["id"];

		// Mitarbeiter suchen
		$employee = $this->helper->getEmployeeByUserId($userId, $today);
		if (!$employee || !isset($employee["id"])) {
			$log[] = "Kein Mitarbeiter gefunden";
			return ["success" => false, "log" => $log];
		}
		$employeeId = $employee["id"];

		// Merge / Replace Mode
		if (empty($entry->abteilung2_id)) {
			$log[] = "Replace-Mode: bestehende Zuweisungen entfernt";

			$this->helper->disableAllEmployeeOrganizationalUnits($employeeId);
			$this->helper->disableAllEmployeeOrganizationalUnitGroups($employeeId);
			$this->helper->disableAllUserRoles($userId);
		} else {
			$log[] = "Merge-Mode: Zuweisungen werden ergaenzt";
		}

		// ORGUNITS
		$oeLogs = [];
		foreach ($orgUnits as $unit) {

			// Unit fixen (kann nur eine ID sein)
			if (is_numeric($unit)) {
				$unit = ['id' => (int)$unit];
			}

			// Name aus Lookup
			$item = collect($lookupOe)->firstWhere('id', $unit['id']);
			$unitName = $item['name'] ?? null;
			$oeLogs[] = $unitName ?: $unit['id'];

			$assignment = [
				"employee" => ["id" => $employeeId],
				"organizationalunit" => ["id" => $unit["id"]],
				"validityperiod" => ["from" => ["date" => $today, "handling" => "inclusive"]]
			];

			if (!empty($unit["rank"])) {
				$assignment["rank"] = ["id" => (int)$unit["rank"]];
			}

			$this->client->send(
				$this->client->getBaseUrl() . "/resources/external/employeeorganizationalunitassignments",
				"POST",
				$assignment
			);
		}

		if (!empty($oeLogs)) {
			$log[] = "OE verarbeitet (" . implode(", ", $oeLogs) . ")";
		} else {
			$log[] = "Keine OE uebernommen";
		}


		// OE-GROUPS
		$grpLogs = [];
		foreach ($orgGroups as $idGroup) {

			$item = collect($lookupGrp)->firstWhere('id', $idGroup);
			$name = $item['name'] ?? null;

			$grpLogs[] = $name ?: $idGroup;

			$this->client->send(
				$this->client->getBaseUrl() . "/resources/external/employeeorganizationalunitgroupassignments",
				"POST",
				[
					"employee" => ["id" => $employeeId],
					"organizationalunitgroup" => ["id" => $idGroup],
					"validityperiod" => ["from" => ["date" => $today, "handling" => "inclusive"]]
				]
			);
		}

		if (!empty($grpLogs)) {
			$log[] = "OE-Gruppen verarbeitet (" . implode(", ", $grpLogs) . ")";
		} else {
			$log[] = "Keine OE-Gruppen uebernommen";
		}


		// Warnung, falls weder OE noch Gruppe
		if (empty($orgUnits) && empty($orgGroups)) {
			$log[] = "ACHTUNG: Keine OE und keine OE-Gruppe – bitte manuell in ORBIS setzen!";
		}


		// Rollen
		if (!empty($roles)) {

			$roleLogs = [];

			foreach ($roles as $roleId) {

				$item = collect($lookupRol)->firstWhere('id', $roleId);
				$roleName = $item['name'] ?? null;

				$roleLogs[] = $roleName ?: $roleId;

				$this->client->send(
					$this->client->getBaseUrl() . "/resources/external/userroleassignments",
					"POST",
					[
						"user" => ["id" => $userId],
						"role" => ["id" => $roleId],
						"validityperiod" => ["from" => ["date" => $today, "handling" => "inclusive"]]
					]
				);
			}

			$log[] = "Rollen verarbeitet (" . implode(", ", $roleLogs) . ")";

		} else {
			$log[] = "ACHTUNG: Keine Rollen uebernommen – bitte manuell hinterlegen!";
		}


		// Mitarbeiterfunktion
		if ($employeeFunctionId) {
			$this->client->send(
				$this->client->getBaseUrl() . "/resources/external/employeeemployeefunctionassignments",
				"POST",
				[
					"employee" => ["id" => $employeeId],
					"employeefunction" => ["id" => (int)$employeeFunctionId],
					"validityperiod" => ["from" => ["date" => $today, "handling" => "inclusive"]]
				]
			);

			$log[] = "Mitarbeiterfunktion aktualisiert";
		}

		return ["success" => true, "log" => $log];
	}

}
