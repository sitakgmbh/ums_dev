<?php

namespace App\Services\Orbis;

use App\Models\Eroeffnung;
use Illuminate\Http\Request;

class OrbisUserCreator
{
    protected OrbisClient $client;
    protected OrbisHelper $helper;

    public function __construct(OrbisClient $client, OrbisHelper $helper)
    {
        $this->client = $client;
        $this->helper = $helper;
    }

    public function create(int $id): array
    {
        $log = [];

        // Original: Eroeffnung laden
        $entry = Eroeffnung::getEroeffnung($id);

        if (!$entry) {
            $log[] = "Kein gueltiger Antrag gefunden";
            return ["success" => false, "log" => $log];
        }

        // Original: Input validieren
        $input = request()->all();
        $this->helper->validateInput($input);

        // Username-Basis
        $baseUsername = strtoupper($entry["benutzername"]);

        // Username finden (inkl. Log)
        $search = $this->helper->findAvailableUsername($baseUsername);
        $username = $search["username"];
        $log = array_merge($log, $search["log"]);

        $today = date("Y-m-d");

        $orgUnits  = $input["orgunits"]  ?? [];
        $orgGroups = $input["orggroups"] ?? [];
        $roles     = $input["roles"]      ?? [];

        // Mappings
        $salutationMap = ["Herr" => 29309, "Frau" => 29310];
        $sexMap        = ["Herr" => 29615, "Frau" => 29614];
        $titleMap = [
            "Dipl. med."        => 66686,
            "Dr. med."          => 35744,
            "Dr. phil."         => 39844,
            "Dr.med.Dr.phil."   => 91268
        ];

        $anredeId = $salutationMap[$entry['anrede_name']] ?? 29310;
        $sexId    = $sexMap[$entry['anrede_name']] ?? 29614;
        $titleId  = $titleMap[$entry['titel_name']] ?? null;

        // Humanbeing
        $humanbeing = [
            "firstname"   => $entry['vorname'],
            "surname"     => $entry['nachname'],
            "sex"         => ["id" => $sexId],
            "nationality" => ["id" => "CH"],
            "salutation"  => ["id" => $anredeId]
        ];

        if ($titleId) {
            $humanbeing["title"] = ["id" => $titleId];
        }

        // Employee Payload
        $employeePayload = [
            "shortname" => $username,
            "humanbeing" => $humanbeing,
            "language" => ["id" => "de_CH"],
            "state" => ["id" => $input['employeeStateId'] ?? null],
            "validityperiod" => [
                "from" => [
                    "date" => $today,
                    "handling" => "inclusive"
                ]
            ]
        ];

        // Employee erstellen
        $employeeId = $this->helper->createEmployee($employeePayload);

        if (!$employeeId) {
            $log[] = "Fehler beim Erstellen des Mitarbeiters.";
            return ["success" => false, "log" => $log];
        }

        $log[] = "Mitarbeiter erfolgreich erstellt (ID: {$employeeId})";

        // Employee Facility
        $this->client->post("/resources/external/employeefacilityassignments", [
            "employee" => ["id" => $employeeId],
            "facility" => ["id" => 1],
            "type" => ["id" => 41280],
            "validityperiod" => [
                "from" => [
                    "date" => $today,
                    "handling" => "inclusive"
                ]
            ]
        ]);

        $log[] = "Zuweisung Facility an Mitarbeiter abgeschlossen";

        // Mitarbeiterfunktion
        if (!empty($input["employeeFunction"])) {
            $this->client->post("/resources/external/employeeemployeefunctionassignments", [
                "employee" => ["id" => $employeeId],
                "employeefunction" => ["id" => (int)$input["employeeFunction"]],
                "validityperiod" => [
                    "from" => [
                        "date" => $today,
                        "handling" => "inclusive"
                    ]
                ]
            ]);

            $log[] = "Zuweisung Mitarbeiterfunktion abgeschlossen";
        } else {
            $log[] = "Keine Mitarbeiterfunktion angegeben";
        }

        // User Payload
        $userPayload = [
            "name" => $username,
            "password" => base64_encode($entry["passwort"]),
            "canchangepassword" => true,
            "mustchangepassword" => true,
            "passwordrefreshinterval" => 120,
            "locked" => false,
            "validityperiod" => [
                "from" => [
                    "date" => $today,
                    "handling" => "inclusive"
                ]
            ],
            "facility" => ["id" => 1],
            "languages" => [
                "language" => [
                    ["id" => "de_CH"],
                    ["id" => "de"]
                ]
            ]
        ];

        // User erstellen
        $userId = $this->helper->createUser($employeeId, $userPayload);

        if (!$userId) {
            $log[] = "Fehler beim Erstellen des Benutzers";
            return ["success" => false, "log" => $log];
        }

        $log[] = "Benutzer erfolgreich erstellt (ID: {$userId})";

        // User Facility
        $this->client->post("/resources/external/userfacilityassignments", [
            "user" => ["id" => $userId],
            "facility" => ["id" => 1],
            "type" => ["id" => 41280],
            "validityperiod" => [
                "from" => [
                    "date" => $today,
                    "handling" => "inclusive"
                ]
            ]
        ]);

        $log[] = "Zuweisung Facility an Benutzer abgeschlossen";

        // Org Units
        foreach ($orgUnits as $unit) {

            $assignment = [
                "employee" => ["id" => $employeeId],
                "organizationalunit" => ["id" => $unit["id"]],
                "validityperiod" => [
                    "from" => [
                        "date" => $today,
                        "handling" => "inclusive"
                    ]
                ]
            ];

            if (!empty($unit["rank"])) {
                $assignment["rank"] = ["id" => (int)$unit["rank"]];
            }

            $this->client->post("/resources/external/employeeorganizationalunitassignments", $assignment);
        }

        $log[] = "Zuweisung Organisationseinheiten abgeschlossen";

        // Org Groups
        foreach ($orgGroups as $idGroup) {
            $this->client->post("/resources/external/employeeorganizationalunitgroupassignments", [
                "employee" => ["id" => $employeeId],
                "organizationalunitgroup" => ["id" => $idGroup],
                "validityperiod" => [
                    "from" => [
                        "date" => $today,
                        "handling" => "inclusive"
                    ]
                ]
            ]);
        }

        $log[] = "OE-Gruppen zugewiesen";

        // Rollen
        foreach ($roles as $roleId) {
            $this->client->post("/resources/external/userroleassignments", [
                "user" => ["id" => $userId],
                "role" => ["id" => $roleId],
                "validityperiod" => [
                    "from" => [
                        "date" => $today,
                        "handling" => "inclusive"
                    ]
                ]
            ]);
        }

        $log[] = "Zuweisung Benutzerrollen abgeschlossen";

        return ["success" => true, "log" => $log];
    }
}
