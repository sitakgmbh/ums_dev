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

        // Antrag laden
        $entry = Mutation::find($id);

        if (!$entry || empty($entry->benutzername)) {
            $log[] = "Kein gueltiger Antrag gefunden";
            return ["success" => false, "log" => $log];
        }

        // Input
        $input = request()->all();
        Logger::debug("ORBIS INPUT (UPDATE): " . json_encode($input, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        $this->helper->validateInput($input);

        $username = strtoupper($entry->benutzername);
        $today = date("Y-m-d");

        $orgUnits  = $input["orgunits"]  ?? [];
        $orgGroups = $input["orggroups"] ?? [];
        $roles     = $input["roles"]     ?? [];
        $employeeFunctionId = $input["employeeFunction"] ?? null;

        if (!is_array($roles)) {
            $roles = [];
        }

        // Benutzer laden
        $user = $this->helper->getUserByUsername($username);

        if (!$user || !isset($user["id"])) {
            $log[] = "Benutzer '{$username}' nicht gefunden";
            return ["success" => false, "log" => $log];
        }

        $userId = $user["id"];

        // Mitarbeiter
        $employee = $this->helper->getEmployeeByUserId($userId, $today);

        if (!$employee || !isset($employee["id"])) {
            $log[] = "Kein Mitarbeiter gefunden";
            return ["success" => false, "log" => $log];
        }

        $employeeId = $employee["id"];

        // Replace Mode
        if (empty($entry->abteilung2_id)) {
            $log[] = "Replace-Mode: alle bestehenden Zuweisungen deaktiviert";

            $this->helper->disableAllEmployeeOrganizationalUnits($employeeId);
            $this->helper->disableAllEmployeeOrganizationalUnitGroups($employeeId);
            $this->helper->disableAllUserRoles($userId);
        } else {
            $log[] = "Merge-Mode: Zuweisungen werden ergaenzt";
        }

        // OrgUnits
        foreach ($orgUnits as $unit) {
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

        $log[] = "Organisationseinheiten verarbeitet";

        // OrgGroups
        foreach ($orgGroups as $groupId) {
            $this->client->send(
                $this->client->getBaseUrl() . "/resources/external/employeeorganizationalunitgroupassignments",
                "POST",
                [
                    "employee" => ["id" => $employeeId],
                    "organizationalunitgroup" => ["id" => $groupId],
                    "validityperiod" => ["from" => ["date" => $today, "handling" => "inclusive"]]
                ]
            );
        }

        $log[] = "Organisationseinheitengruppen verarbeitet";

        // Rollen (optional!)
        if (!empty($roles)) {
            foreach ($roles as $roleId) {
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

            $log[] = "Benutzerrollen verarbeitet (" . implode(", ", $roles) . ")";
        } else {
            $log[] = "Keine Rollen uebermittelt — Rollen nicht angepasst";
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
