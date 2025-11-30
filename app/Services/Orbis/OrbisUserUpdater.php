<?php

namespace App\Services\Orbis;

use App\Models\Mutation;

class OrbisUserUpdater
{
    protected OrbisClient $client;
    protected OrbisHelper $helper;

    public function __construct(OrbisClient $client, OrbisHelper $helper)
    {
        $this->client  = $client;
        $this->helper  = $helper;
    }

    public function update(int $id): array
    {
        $log = [];

        // Antrag laden
        $entry = Mutation::find($id);

        if (!$entry || empty($entry["benutzername"])) {
            $log[] = "Kein gueltiger Antrag gefunden";
            return ["success" => false, "log" => $log];
        }

        $username = strtoupper($entry["benutzername"]);

        // Input validieren
        $input = request()->all();
        $this->helper->validateInput($input);

        $today = date("Y-m-d");
        $orgUnits  = $input["orgunits"]  ?? [];
        $orgGroups = $input["orggroups"] ?? [];
        $roles     = $input["roles"]     ?? [];
        $employeeFunctionId = $input["employeeFunction"] ?? null;

        // Benutzer suchen
        $user = $this->helper->getUserByUsername($username);

        if (!$user || !isset($user["id"])) {
            $log[] = "Benutzer '{$username}' nicht gefunden";
            return ["success" => false, "log" => $log];
        }

        $userId = $user["id"];

        // Mitarbeiter laden
        $employee = $this->helper->getEmployeeByUserId($userId, $today);

        if (!$employee || !isset($employee["id"])) {
            $log[] = "Kein zugeordneter Mitarbeiter gefunden";
            return ["success" => false, "log" => $log];
        }

        $employeeId = $employee["id"];

        // Falls keine zweite Abteilung → alles deaktivieren
        if (empty($entry["zweite_abteilung"])) {
            $log[] = "Existierende Zuweisungen werden vollstaendig ersetzt";

            $this->helper->disableAllEmployeeOrganizationalUnits($employeeId);
            $this->helper->disableAllEmployeeOrganizationalUnitGroups($employeeId);
            $this->helper->disableAllUserRoles($userId);
        } else {
            $log[] = "Zweite Abteilung vorhanden – Ergaenzung statt Ersatz";
        }

        /** Organisationseinheiten  */
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

        $log[] = "Organisationseinheiten verarbeitet";

        /** OE-Gruppen */
        foreach ($orgGroups as $groupId) {
            $this->client->post("/resources/external/employeeorganizationalunitgroupassignments", [
                "employee" => ["id" => $employeeId],
                "organizationalunitgroup" => ["id" => $groupId],
                "validityperiod" => [
                    "from" => [
                        "date" => $today,
                        "handling" => "inclusive"
                    ]
                ]
            ]);
        }

        $log[] = "Organisationseinheitengruppen verarbeitet";

        /** Rollen */
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

        $log[] = "Benutzerrollen verarbeitet";

        /** Mitarbeiterfunktion */
        if ($employeeFunctionId) {
            $this->client->post("/resources/external/employeeemployeefunctionassignments", [
                "employee" => ["id" => $employeeId],
                "employeefunction" => ["id" => (int)$employeeFunctionId],
                "validityperiod" => [
                    "from" => [
                        "date" => $today,
                        "handling" => "inclusive"
                    ]
                ]
            ]);

            $log[] = "Mitarbeiterfunktion aktualisiert";
        }

        return ["success" => true, "log" => $log];
    }
}
