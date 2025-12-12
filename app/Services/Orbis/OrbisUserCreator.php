<?php

namespace App\Services\Orbis;

use App\Models\Eroeffnung;
use App\Services\Orbis\OrbisClient;
use App\Services\Orbis\OrbisHelper;
use App\Utils\Logging\Logger;

class OrbisUserCreator
{
    private OrbisClient $client;
    private OrbisHelper $helper;

    public function __construct(OrbisClient $client, OrbisHelper $helper)
    {
        $this->client = $client;
        $this->helper = $helper;
    }

    public function create(int $id, array $input): array
    {
        $log = [];
        $entry = Eroeffnung::with(['anrede', 'titel'])->find($id);

        if (!$entry) 
		{
            $log[] = "Kein gültiger Antrag gefunden.";
            return ["success" => false, "log" => $log];
        }

        if (!$entry->benutzername) 
		{
            $log[] = "Benutzername fehlt in Antrag.";
            return ["success" => false, "log" => $log];
        }

        Logger::debug("ORBIS INPUT (CREATE): " . json_encode($input, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        $input['orgunits']  = $input['orgunits']  ?? [];
        $input['orggroups'] = $input['orggroups'] ?? [];
        $input['roles']     = $input['roles']     ?? [];

        $this->helper->validateInput($input);
        $baseUsername = strtoupper($entry->benutzername);
        $search = $this->helper->findAvailableUsername($baseUsername);

        $username = $search["username"];
        $log = array_merge($log, $search["log"]);

        $today = date("Y-m-d");

        $orgUnits  = $input["orgunits"]  ?? [];
        $orgGroups = $input["orggroups"] ?? [];
        $roles     = $input["roles"]     ?? [];

		// Lookup-Daten aus Livewire übergeben
		$lookupOe  = $input['orgunits_lookup']  ?? [];
		$lookupGrp = $input['orggroups_lookup'] ?? [];
		$lookupRol = $input['roles_lookup']     ?? [];

        if (!is_array($roles)) 
		{
            $roles = [];
        }

        // Mapping
        $salutationMap = ["Herr" => 29309, "Frau" => 29310];
        $sexMap        = ["Herr" => 29615, "Frau" => 29614];
        $titleMap = [
            "Dipl. med." => 66686,
            "Dr. med." => 35744,
            "Dr. phil." => 39844,
            "Dr.med.Dr.phil." => 91268
        ];

        $anredeId = $salutationMap[$entry->anrede?->name ?? 'Frau'] ?? 29310;
        $sexId    = $sexMap[$entry->anrede?->name ?? 'Frau'] ?? 29614;
        $titleId  = $titleMap[$entry->titel?->name ?? null] ?? null;

        // Human Being
        $humanbeing = [
            "firstname"   => $entry->vorname,
            "surname"     => $entry->nachname,
            "sex"         => ["id" => $sexId],
            "nationality" => ["id" => "CH"],
            "salutation"  => ["id" => $anredeId]
        ];

        if ($titleId) 
		{
            $humanbeing["title"] = ["id" => $titleId];
        }

        // Mitarbeiter anlegen
        $employeePayload = [
            "shortname" => $username,
            "humanbeing" => $humanbeing,
            // "language" => ["id" => "de_CH"],
            "state" => ["id" => $input['employeeStateId'] ?? null],
			"signinglevel" => $input['signinglevel'] ? ["id" => $input['signinglevel']] : null,
            "validityperiod" => [
                "from" => ["date" => $today, "handling" => "inclusive"]
            ]
        ];

        $employeeId = $this->helper->createEmployee($employeePayload);

        if (!$employeeId) 
		{
            $log[] = "Fehler beim Erstellen des Mitarbeiters.";
            return ["success" => false, "log" => $log];
        }

        $log[] = "Mitarbeiter erstellt (ID: {$employeeId})";

        // Facility
        $this->client->send(
            $this->client->getBaseUrl() . "/resources/external/employeefacilityassignments",
            "POST",
            [
                "employee" => ["id" => $employeeId],
                "facility" => ["id" => 1],
                "type" => ["id" => 41280],
                "validityperiod" => ["from" => ["date" => $today, "handling" => "inclusive"]]
            ]
        );

        $log[] = "Mitarbeiter Facility zugewiesen";

        // Mitarbeiterfunktion
        if (!empty($input["employeeFunction"])) 
		{
            $this->client->send(
                $this->client->getBaseUrl() . "/resources/external/employeeemployeefunctionassignments",
                "POST",
                [
                    "employee" => ["id" => $employeeId],
                    "employeefunction" => ["id" => (int)$input["employeeFunction"]],
                    "validityperiod" => ["from" => ["date" => $today, "handling" => "inclusive"]]
                ]
            );

            $log[] = "Mitarbeiterfunktion gesetzt";
        } 
		else 
		{
            $log[] = "Keine Mitarbeiterfunktion angegeben";
        }

        // Benutzer erstellen
        $userPayload = [
            "name" => $username,
            "password" => base64_encode($entry->passwort),
            "canchangepassword" => true,
            "mustchangepassword" => true,
            "passwordrefreshinterval" => 120,
            "locked" => false,
            "validityperiod" => ["from" => ["date" => $today, "handling" => "inclusive"]],
            "facility" => ["id" => 1],
            "languages" => ["language" => [["id" => "de_CH"], ["id" => "de"]]]
        ];

        $userId = $this->helper->createUser($employeeId, $userPayload);

        if (!$userId) 
		{
            $log[] = "Fehler beim Erstellen des Benutzers";
            return ["success" => false, "log" => $log];
        }

        $log[] = "Benutzer erstellt (ID: {$userId})";

        // User Facility
        $this->client->send(
            $this->client->getBaseUrl() . "/resources/external/userfacilityassignments",
            "POST",
            [
                "user" => ["id" => $userId],
                "facility" => ["id" => 1],
                "type" => ["id" => 41280],
                "validityperiod" => ["from" => ["date" => $today, "handling" => "inclusive"]]
            ]
        );

        $log[] = "Benutzer Facility zugewiesen";

		// Organisationseinheiten
		$oeLogs = [];

		if (!empty($orgUnits)) 
		{
			foreach ($orgUnits as $unit) 
			{
				if (is_numeric($unit)) 
				{
					$unit = ['id' => (int)$unit];
				}

				$item = collect($lookupOe)->firstWhere('id', $unit['id']);
				$unitName = $item['name'] ?? null;
				$oeLogs[] = $unitName ?: $unit['id'];

				$assignment = [
					"employee" => ["id" => $employeeId],
					"organizationalunit" => ["id" => $unit["id"]],
					"validityperiod" => ["from" => ["date" => $today, "handling" => "inclusive"]]
				];

				if (!empty($unit["rank"])) 
				{
					$assignment["rank"] = ["id" => (int)$unit["rank"]];
				}

				$this->client->send(
					$this->client->getBaseUrl() . "/resources/external/employeeorganizationalunitassignments",
					"POST",
					$assignment
				);
			}

			$log[] = "OE zugewiesen (" . implode(", ", $oeLogs) . ")";
		} 
		else 
		{
			$log[] = "Keine OE übernommen.";
		}

		// OE-Gruppen
		$groupLogs = [];

		if (!empty($orgGroups)) 
		{
			foreach ($orgGroups as $idGroup) 
			{
				$item = collect($lookupGrp)->firstWhere('id', $idGroup);
				$groupName = $item['name'] ?? null;
				$label = $groupName ?: $idGroup;
				$groupLogs[] = $label;

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

			$log[] = "OE-Gruppen zugewiesen (" . implode(", ", $groupLogs) . ")";
		} 
		else 
		{
			$log[] = "Keine OE-Gruppen übernommen.";
		}

		$hasOe = !empty($orgUnits);
		$hasGroup = !empty($orgGroups);

		if (!$hasOe && !$hasGroup) 
		{
			$log[] = "ACHTUNG: Keine OE und keine OE-Gruppe zugewiesen – bitte manuell hinterlegen!";
		}

		if (!empty($roles)) 
		{
			$roleLogs = [];

			foreach ($roles as $roleId) 
			{

				$item = collect($lookupRol)->firstWhere('id', $roleId);

				$roleName = $item['name'] ?? null;

				$label = $roleName ?: $roleId;

				$roleLogs[] = $label;

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

			$log[] = "Rollen zugewiesen (" . implode(", ", $roleLogs) . ")";
		} 
		else 
		{
			$log[] = "ACHTUNG: Keine Rolle übernommen – bitte manuell hinterlegen!";
		}

        return ["success" => true, "log" => $log];
    }
}
