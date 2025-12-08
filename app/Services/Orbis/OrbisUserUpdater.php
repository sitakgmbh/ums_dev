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

    public function update(int $id, array $input): array
    {
		$log = [];
		$entry = Mutation::with(['adUser', 'vorlageBenutzer'])->find($id);
		
		if (!$entry) {
			$log[] = "Kein gültiger Antrag gefunden.";
			return ["success" => false, "log" => $log];
		}

		if (!$entry->adUser) {
			$log[] = "AD-Benutzer nicht gefunden.";
			return ["success" => false, "log" => $log];
		}

		$username = strtoupper($entry->adUser->username);

		$employeeFunctionId = $input['employeeFunction'] ?? null;
		$signingLevelId     = $input['signinglevel'] ?? null;
	
        $orgUnits    = $input['orgunits']  ?? [];
        $orgGroups   = $input['orggroups'] ?? [];
        $roles       = $input['roles']     ?? [];

        $lookupOe    = $input['orgunits_lookup']  ?? [];
        $lookupGrp   = $input['orggroups_lookup'] ?? [];
        $lookupRol   = $input['roles_lookup']     ?? [];

		$employeeStateId = $input['employeeStateId'] ?? null;
		$signingLevelId  = $input['signinglevel'] ?? null;

        $this->helper->validateInput($input);

        $username = strtoupper($entry->aduser->username);
        $today = date("Y-m-d");

        $employeeFunctionId = $input['employeeFunction'] ?? null;

        if (!is_array($roles)) {
            $roles = [];
        }

        $user = $this->helper->getUserByUsername($username);

        if (!$user || !isset($user["id"])) {
            $log[] = "Benutzer '{$username}' nicht gefunden.";
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

        // Merge / Replace
		if ($input['permissionMode'] === 'replace') {

			$log[] = "Setze bestehende Zuweisungen von OEs, OE-Gruppen und Rollen auf ungültig";

			$this->helper->disableAllEmployeeOrganizationalUnits($employeeId);
			$this->helper->disableAllEmployeeOrganizationalUnitGroups($employeeId);
			$this->helper->disableAllUserRoles($userId);

		}

        // ===============================
        // ORGUNITS
        // ===============================
        $oeLogs = [];

        foreach ($orgUnits as $unit) {

            if (is_numeric($unit)) {
                $unit = ['id' => (int)$unit];
            }

            $item = collect($lookupOe)->firstWhere('id', $unit['id']);
            $unitName = $item['name'] ?? null;

            $oeLogs[] = $unitName ?: $unit['id'];

            $payload = [
                "employee" => ["id" => $employeeId],
                "organizationalunit" => ["id" => $unit["id"]],
                "validityperiod" => [
                    "from" => ["date" => $today, "handling" => "inclusive"]
                ]
            ];

            if (!empty($unit["rank"])) {
                $payload["rank"] = ["id" => (int)$unit["rank"]];
            }

            $this->client->send(
                $this->client->getBaseUrl() . "/resources/external/employeeorganizationalunitassignments",
                "POST",
                $payload
            );
        }

        if (!empty($oeLogs)) {
            $log[] = "OE verarbeitet (" . implode(", ", $oeLogs) . ")";
        } else {
            $log[] = "Keine OE übernommen";
        }

        // ===============================
        // OE-GROUPS
        // ===============================
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
                    "validityperiod" => [
                        "from" => ["date" => $today, "handling" => "inclusive"]
                    ]
                ]
            );
        }

        if (!empty($grpLogs)) {
            $log[] = "OE-Gruppen verarbeitet (" . implode(", ", $grpLogs) . ")";
        } else {
            $log[] = "Keine OE-Gruppen übernommen";
        }

        if (empty($orgUnits) && empty($orgGroups)) {
            $log[] = "ACHTUNG: Keine OE und keine OE-Gruppe zugewiesen – bitte manuell hinterlegen!";
        }

        // ===============================
        // Rollen
        // ===============================			
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
                        "validityperiod" => [
                            "from" => ["date" => $today, "handling" => "inclusive"]
                        ]
                    ]
                );
            }

            $log[] = "Rollen verarbeitet (" . implode(", ", $roleLogs) . ")";

        } else {
            $log[] = "ACHTUNG: Keine Rollen übernommen – bitte manuell hinterlegen!";
        }




// ===============================
// State aktualisieren
// ===============================
$stateName = $employeeStateId 
    ? $this->helper->getCatalogNameById((int)$employeeStateId) 
    : "entfernt";

$this->helper->updateEmployeeState($employeeId, $employeeStateId);
$log[] = "Status (Funktion) aktualisiert";


// ===============================
// Signing-Level aktualisieren
// ===============================
$signName = $signingLevelId
    ? $this->helper->getCatalogNameById((int)$signingLevelId)
    : "entfernt";

$this->helper->updateEmployeeSigningLevel($employeeId, $signingLevelId);
$log[] = "Signierlevel aktualisiert";



		// ===============================
		// Mitarbeiterfunktion
		// ===============================

		// immer zuerst alte Funktionen deaktivieren
		$this->helper->disableAllEmployeeFunctions($employeeId);

		if ($employeeFunctionId) {

			// neue Funktion setzen
			$this->client->send(
				$this->client->getBaseUrl() . "/resources/external/employeeemployeefunctionassignments",
				"POST",
				[
					"employee" => ["id" => $employeeId],
					"employeefunction" => ["id" => (int)$employeeFunctionId],
					"validityperiod" => [
						"from" => ["date" => $today, "handling" => "inclusive"]
					]
				]
			);

			$log[] = "Mitarbeiterfunktion aktualisiert";

		} else {

			// keine neue → nur alte loeschen
			$log[] = "Mitarbeiterfunktion entfernt";
		}



		// ===============================
		// Signierstufe (Signinglevel)
		// ===============================
		if ($signingLevelId) {

			$this->client->send(
				$this->client->getBaseUrl() . "/resources/external/employeeemployeesigninglevelassignments",
				"POST",
				[
					"employee" => ["id" => $employeeId],
					"signinglevel" => ["id" => (int)$signingLevelId],
					"validityperiod" => [
						"from" => ["date" => $today, "handling" => "inclusive"]
					]
				]
			);

			$log[] = "Signierstatus aktualisiert";
		}


        return ["success" => true, "log" => $log];
    }

}
