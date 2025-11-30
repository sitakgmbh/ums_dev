<?php

namespace App\Services\Orbis;

use Illuminate\Support\Facades\Log;

class OrbisHelper
{
    public function __construct(
        private OrbisClient $client
    ) {}

    public function employeeExists(string $shortname): bool
    {
        $url = $this->client->getBaseUrl() . "/resources/external/employees?shortname=" . urlencode($shortname) . "&maxresults=1";
        $result = $this->client->send($url, "GET");

        return isset($result["employee"]) && count($result["employee"]) > 0;
    }

    public function userExists(string $username): bool
    {
        $url = $this->client->getBaseUrl() . "/resources/external/users?name=" . urlencode($username) . "&maxresults=1";
        $result = $this->client->send($url, "GET");

        return isset($result["user"]) && count($result["user"]) > 0;
    }

public function getUserDetails(string $username): array
{
    $username = strtoupper($username);
    $today = date("Y-m-d");
    $user = $this->getUserByUsername($username);

    if (!$user || !isset($user["id"])) {
        throw new \RuntimeException("Benutzer nicht gefunden.", 404);
    }

    $userId = $user["id"];
    $employee = $this->getEmployeeByUserId($userId, $today);

    if (!$employee || !isset($employee["id"])) {
        throw new \RuntimeException("Kein Mitarbeiter gefunden.");
    }

    $employeeData = $this->getEmployeeDetails($employee, $today);
    $orgUnits = $this->getEmployeeOrganizationalUnits($employeeData["id"], $today);
    $orgGroups = $this->getEmployeeOrganizationalUnitGroups($employeeData["id"], $today);
    $users = $this->getUsersByEmployeeId($employeeData["id"], $today);
    $facility = $employeeData["facilities"][0] ?? null;

    return [
        "user" => $user,
        "employee" => [
            "id" => $employeeData["id"],
            "salutation" => $employeeData["salutation"],
            "title" => $employeeData["title"] ?? null,
            "surname" => $employeeData["surname"],
            "firstname" => $employeeData["firstname"],
            "sex" => $employeeData["sex"],
            "facility" => $facility,
            "state" => $employeeData["state"],
            "signinglevel" => $employeeData["signinglevel"],
            "validfrom" => $employeeData["validfrom"],
            "validthru" => $employeeData["validthru"],
            "organizationalunits" => $orgUnits,
            "organizationalunitgroups" => $orgGroups,
            "users" => $users
        ]
    ];
}


    public function getUserByUsername(string $username): ?array
    {
        $url = $this->client->getBaseUrl() . "/resources/external/users?name=" . urlencode($username);
        $users = $this->client->send($url)["user"] ?? [];
        $today = date("Y-m-d");

        foreach ($users as $user) {
            $from = $user["validityperiod"]["from"]["date"] ?? null;
            $thru = $user["validityperiod"]["thru"]["date"] ?? null;

            if ((!$from || $from <= $today) && (!$thru || $thru >= $today)) {
                return $user;
            }
        }

        return null;
    }

    public function getEmployeeByUserId($userId, string $today)
    {
        $url = $this->client->getBaseUrl() . "/resources/external/users/{$userId}/employees?referencedate={$today}&includecatalogtranslations=true";
        return $this->client->send($url)["employee"][0] ?? null;
    }

    public function getEmployeeDetails(array $employee, string $today): array
    {
        $id = $employee["id"];
        $human = $employee["humanbeing"] ?? [];

        return [
            "id" => $id,
            "firstname" => $human["firstname"] ?? null,
            "surname" => $human["surname"] ?? null,
            "sex" => $this->getCatalogTranslation("SEX", $human["sex"]["catalogcoding"]["code"] ?? ""),
            "salutation" => $this->getCatalogTranslation("SALUTATIONS", $human["salutation"]["catalogcoding"]["code"] ?? ""),
            "title" => $this->getCatalogTranslation("TITLES", $human["title"]["catalogcoding"]["code"] ?? ""),
            "state" => $this->getCatalogTranslation("STATEOFEMPLOYEE", $employee["state"]["catalogcoding"]["code"] ?? ""),
            "signinglevel" => $employee["signinglevel"] ?? null,
            "validfrom" => $employee["validityperiod"]["from"]["date"] ?? null,
            "validthru" => $employee["validityperiod"]["thru"]["date"] ?? null,
            "facilities" => $this->getEmployeeFacilities($id, $today)
        ];
    }

    public function getEmployeeFacilities(int $employeeId, string $today): array
    {
        $result = [];
        $url = $this->client->getBaseUrl() . "/resources/external/employees/{$employeeId}/facilityassignments?referencedate={$today}";
        $response = $this->client->send($url);

        foreach ($response["employeefacilityassignment"] ?? [] as $entry) {
            $fid = $entry["facility"]["id"] ?? null;

            if ($fid) {
                $details = $this->client->send($this->client->getBaseUrl() . "/resources/external/facilities/{$fid}");
                $result[] = [
                    "id" => $fid,
                    "name" => $details["name"] ?? null,
                    "shortname" => $details["shortname"] ?? null
                ];
            }
        }

        return $result;
    }

    public function getEmployeeOrganizationalUnits(int $employeeId, string $today): array
    {
        $result = [];
        $url = $this->client->getBaseUrl() . "/resources/external/employees/{$employeeId}/organizationalunitassignments?referencedate={$today}";
        $response = $this->client->send($url);

        foreach ($response["employeeorganizationalunitassignment"] ?? [] as $a) {
            $unitId = $a["organizationalunit"]["id"] ?? null;

            if ($unitId) {
                $detail = $this->client->send(
                    $this->client->getBaseUrl() . "/resources/external/organizationalunits/{$unitId}"
                );

                $result[] = [
                    "id" => $unitId,
                    "name" => $detail["name"] ?? null,
                    "shortname" => $detail["shortname"] ?? null,
                    "type" => $detail["type"]["catalogcoding"]["code"] ?? null,
                    "rank" => $this->getRank($a["rank"] ?? null)
                ];
            }
        }

        return $result;
    }

    public function getEmployeeOrganizationalUnitGroups(int $employeeId, string $today): array
    {
        $result = [];
        $url = $this->client->getBaseUrl() . "/resources/external/employees/{$employeeId}/organizationalunitgroupassignments?referencedate={$today}";
        $response = $this->client->send($url);

        foreach ($response["employeeorganizationalunitgroupassignment"] ?? [] as $a) {
            $id = $a["organizationalunitgroup"]["id"] ?? null;
            if (!$id) continue;

            $detail = $this->client->send(
                $this->client->getBaseUrl() . "/resources/external/organizationalunitgroups/{$id}"
            );

            $name = $detail["name"] ?? null;
            $shortname = $detail["shortname"] ?? null;
            $type = $detail["organizationalunitgrouptypeassignments"]["organizationalunitgrouptypeassignment"][0]["type"]["catalogcoding"]["code"] ?? null;

            if ($name && $shortname && $type) {
                $result[] = [
                    "id" => $id,
                    "name" => $name,
                    "shortname" => $shortname,
                    "type" => $type
                ];
            }
        }

        return $result;
    }

    public function getUsersByEmployeeId(int $employeeId, string $today): array
    {
        $result = [];
        $url = $this->client->getBaseUrl() . "/resources/external/employees/{$employeeId}/users?referencedate={$today}";
        $users = $this->client->send($url)["user"] ?? [];

        foreach ($users as $user) {
            $result[] = [
                "id" => $user["id"] ?? null,
                "username" => $user["name"] ?? null,
                "description" => $user["description"] ?? null,
                "validfrom" => $user["validityperiod"]["from"]["date"] ?? null,
                "validthru" => $user["validityperiod"]["thru"]["date"] ?? null,
                "locked" => $user["locked"] ?? null,
                "mustchangepassword" => $user["mustchangepassword"] ?? null,
                "passwordrefreshinterval" => $user["passwordrefreshinterval"] ?? null,
                "roles" => $this->getUserRoles($user["id"], $today)
            ];
        }

        return $result;
    }

    public function getUserRoles(int $userId, string $today): array
    {
        $url = $this->client->getBaseUrl() . "/resources/external/users/{$userId}/roleassignments?referencedate={$today}";
        $result = [];

        foreach ($this->client->send($url)["userroleassignment"] ?? [] as $a) {
            $rid = $a["role"]["id"] ?? null;
            if (!$rid) continue;

            $details = $this->client->send(
                $this->client->getBaseUrl() . "/resources/external/roles/{$rid}"
            );

            $result[] = [
                "id" => $rid,
                "name" => $details["name"] ?? "Unbekannt"
            ];
        }

        return $result;
    }

    public function getRank(?array $rank): ?array
    {
        if (!isset($rank["id"])) {
            return null;
        }

        $details = $this->client->send(
            $this->client->getBaseUrl() . "/resources/external/catalogs/{$rank["id"]}"
        );

        return [
            "id" => $rank["id"],
            "code" => $details["catalogcoding"]["code"] ?? null
        ];
    }

    public function getCatalogTranslation(string $codesystem, string $code): array
    {
        if (!$code) {
            return [];
        }

        $url = $this->client->getBaseUrl() . "/resources/external/catalogs?codesystem={$codesystem}&code=" . urlencode($code) . "&includecatalogtranslations=true";
        $data = $this->client->send($url);

        $result = [
            "id" => $data["id"] ?? null,
            "code" => $code,
            "id_language" => null,
            "shortname" => null,
            "longname" => null
        ];

        foreach ($data["catalogtranslations"]["catalogtranslation"] ?? [] as $trans) {
            if (in_array($trans["languageoftranslation"]["id"] ?? "", ["de", "de_CH"])) {
                $result["shortname"] = $trans["shortname"] ?? null;
                $result["longname"] = $trans["longname"] ?? null;
                $result["id_language"] = $trans["languageoftranslation"]["id"] ?? null;
                break;
            }
        }

        return $result;
    }

    public function createEmployee(array $payload): ?int
    {
        $url = $this->client->getBaseUrl() . "/resources/external/employees";
        $response = $this->client->send($url, "POST", $payload, true);

        foreach ($response["headers"] ?? [] as $key => $values) {
            foreach ($values as $value) {
                if (str_contains($value, "Location:") && preg_match('#/employees/(\d+)#', $value, $m)) {
                    return (int)$m[1];
                }
            }
        }

        return null;
    }

    public function createUser(int $employeeId, array $payload): ?int
    {
        $url = $this->client->getBaseUrl() . "/resources/external/employees/{$employeeId}/users";
        $response = $this->client->send($url, "POST", $payload, true);

        foreach ($response["headers"] ?? [] as $key => $values) {
            foreach ($values as $value) {
                if (str_contains($value, "Location:") && preg_match('#/users/(\d+)#', $value, $m)) {
                    return (int)$m[1];
                }
            }
        }

        return null;
    }

    public function findAvailableUsername(string $base): array
    {
        $log = [];
        $prefix = $base;
        $counter = 0;

        if (preg_match('/^(.*?)(\d+)$/', $base, $matches)) {
            $prefix = $matches[1];
            $counter = (int)$matches[2];
        }

        while (true) {
            $testname = $counter === 0 ? $prefix : $prefix . $counter;

            $employeeExists = $this->employeeExists($testname);
            $userExists = $this->userExists($testname);

            if (!$employeeExists && !$userExists) {
                $log[] = "Benutzername '{$testname}' ist verfuegbar.";
                return ["username" => $testname, "log" => $log];
            }

            $log[] = "Benutzername '{$testname}' ist bereits vergeben.";
            $counter++;
        }
    }

public function validateInput(array $input): void
{
    // --- Snapshot-Muell und nested Arrays entfernen ---
    $input['orgunits']  = $this->cleanList($input['orgunits']  ?? []);
    $input['orggroups'] = $this->cleanList($input['orggroups'] ?? []);
    $input['roles']     = $this->cleanList($input['roles']     ?? []);

    // --- Alte Logik, unveraendert ---
    $hasOrgUnits  = count($input['orgunits']) > 0;
    $hasOrgGroups = count($input['orggroups']) > 0;

    if (!$hasOrgUnits && !$hasOrgGroups) {
        throw new \InvalidArgumentException(
            "Bitte waehle mindestens eine Organisationseinheit oder eine Organisationseinheitgruppe aus."
        );
    }
}

private function cleanList($list): array
{
    if (!is_array($list)) {
        return [];
    }

    $clean = [];

    foreach ($list as $item) {

        // Livewire Snapshot Marker entfernen
        if (is_array($item) && isset($item['s']) && $item['s'] === 'arr') {
            continue;
        }

        // Nested Arrays flatten
        if (is_array($item)) {
            foreach ($item as $id) {
                if (is_numeric($id)) {
                    $clean[] = (int)$id;
                }
            }
        }

        // Einzelwerte
        elseif (is_numeric($item)) {
            $clean[] = (int)$item;
        }
    }

    return array_values(array_unique($clean));
}



    public function disableAllEmployeeOrganizationalUnits(int $employeeId): void
    {
        $today = date("Y-m-d");
        $url = $this->client->getBaseUrl() . "/resources/external/employees/{$employeeId}/organizationalunitassignments?referencedate={$today}";
        $response = $this->client->send($url);

        foreach ($response["employeeorganizationalunitassignment"] ?? [] as $a) {
            if (!empty($a["id"]) && $a["id"] > 0) {
                $this->setAssignmentEndDate("employeeorganizationalunitassignments", (int)$a["id"]);
            }
        }
    }

    public function disableAllEmployeeOrganizationalUnitGroups(int $employeeId): void
    {
        $today = date("Y-m-d");
        $url = $this->client->getBaseUrl() . "/resources/external/employees/{$employeeId}/organizationalunitgroupassignments?referencedate={$today}";
        $response = $this->client->send($url);

        foreach ($response["employeeorganizationalunitgroupassignment"] ?? [] as $a) {
            if (!empty($a["id"]) && $a["id"] > 0) {
                $this->setAssignmentEndDate("employeeorganizationalunitgroupassignments", (int)$a["id"]);
            }
        }
    }

    public function disableAllUserRoles(int $userId): void
    {
        $today = date("Y-m-d");
        $url = $this->client->getBaseUrl() . "/resources/external/users/{$userId}/roleassignments?referencedate={$today}";
        $response = $this->client->send($url);

        foreach ($response["userroleassignment"] ?? [] as $a) {
            if (!empty($a["id"]) && $a["id"] > 0) {
                $this->setAssignmentEndDate("userroleassignments", (int)$a["id"]);
            }
        }
    }

    private function setAssignmentEndDate(string $resource, int $id): void
    {
        $url = $this->client->getBaseUrl() . "/resources/external/{$resource}/{$id}";
        $yesterday = date("Y-m-d", strtotime("-1 day"));

        $existing = $this->client->send($url);

        if (!is_array($existing) || empty($existing["id"])) {
            return;
        }

        $from = $existing["validityperiod"]["from"] ?? ["date" => "2000-01-01"];

        $payload = $existing;
        $payload["canceled"] = true;
        $payload["validityperiod"] = [
            "from" => $from,
            "to" => ["date" => $yesterday]
        ];

        $this->client->send(
            $this->client->getBaseUrl() . "/resources/external/{$resource}",
            "PUT",
            $payload
        );
    }
}
