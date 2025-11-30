<?php

namespace App\Services\Orbis;

use App\Models\Mutation;

class OrbisUserUpdater
{
    public function __construct(
        private OrbisService $service,
        private OrbisClient $client
    ) {}

    public function update(int $id): array
    {
        $log = [];

        // Original: Antrag laden
        $entry = Mutation::getMutation($id);

        if (!$entry || empty($entry["benutzername"])) {
            $log[] = "Kein gueltiger Antrag gefunden";
            return ["success" => false, "log" => $log];
        }

        $username = strtoupper($entry["benutzername"]);

        // Original: Input lesen
        $input = request()->all();
        OrbisService::validateInput($input);

        $today = date("Y-m-d");
        $orgUnits = $input["orgunits"] ?? [];
        $orgGroups = $input["orggroups"] ?? [];
        $roles = $input["roles"] ?? [];
        $employeeFunctionId = $input["employeeFunction"] ?? null;

        // Benutzer suchen
        $user = $this->service->getUserByUsername($username);

        if (!$user || !isset($user["id"])) {
            $log[] = "Benutzer '{$username}' nicht gefunden";
            return ["success" => false, "log" => $log];
        }

        $userId = $user["id"];

        // Mitarbeiter laden
        $employee = $this->service->getEmployeeByUserId($userId, $today);

        if (!$employee || !isset($employee["id"])) {
            $log[] = "Kein zugeordneter Mitarbeiter gefunden";
            return ["success" => false, "log" => $log];
        }

        $employeeId = $employee["id"];

        // Falls keine zweite Abteilung: alle bestehenden Zuweisungen deaktivieren
        if (empty($entry["zweite_abteilung"])) {
            $log[] = "Existierende Zuweisungen werden vollstaendig ersetzt";

            $this->service->disableAllEmployeeOrganizationalUnits($employeeId);
            $this->service->disableAllEmployeeOrganizationalUnitGroups($employeeId);
            $this->service->disableAllUserRoles($userId);
        } else {
            $log[] = "Zweite Abteilung vorhanden – Ergaenzung statt Ersatz";
        }

        /** --------------------------------------------
         *  Organisationseinheiten zuweisen
         * -------------------------------------------- */
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

            $this->client->send(
                $this->client->getBaseUrl() . "/resources/external/employeeorganizationalunitassignments",
                "POST",
                $assignment
            );
        }

        $log[] = "Organisationseinheiten verarbeitet";

        /** --------------------------------------------
         *  OE-Gruppen zuweisen
         * -------------------------------------------- */
        foreach ($orgGroups as $groupId) {
            $this->client->send(
                $this->client->getBaseUrl() . "/resources/external/employeeorganizationalunitgroupassignments",
                "POST",
                [
                    "employee" => ["id" => $employeeId],
                    "organizationalunitgroup" => ["id" => $groupId],
                    "validityperiod" => [
                        "from" => [
                            "date" => $today,
                            "handling" => "inclusive"
                        ]
                    ]
                ]
            );
        }

        $log[] = "Organisationseinheitengruppen verarbeitet";

        /** --------------------------------------------
         *  Rollen zuweisen
         * -------------------------------------------- */
        foreach ($roles as $roleId) {
            $this->client->send(
                $this->client->getBaseUrl() . "/resources/external/userroleassignments",
                "POST",
                [
                    "user" => ["id" => $userId],
                    "role" => ["id" => $roleId],
                    "validityperiod" => [
                        "from" => [
                            "date" => $today,
                            "handling" => "inclusive"
                        ]
                    ]
                ]
            );
        }

        $log[] = "Benutzerrollen verarbeitet";

        /** --------------------------------------------
         *  Mitarbeiterfunktion aktualisieren
         * -------------------------------------------- */
        if ($employeeFunctionId) {
            $this->client->send(
                $this->client->getBaseUrl() . "/resources/external/employeeemployeefunctionassignments",
                "POST",
                [
                    "employee" => ["id" => $employeeId],
                    "employeefunction" => ["id" => (int)$employeeFunctionId],
                    "validityperiod" => [
                        "from" => [
                            "date" => $today,
                            "handling" => "inclusive"
                        ]
                    ]
                ]
            );

            $log[] = "Mitarbeiterfunktion aktualisiert";
        }

        return ["success" => true, "log" => $log];
    }
}
