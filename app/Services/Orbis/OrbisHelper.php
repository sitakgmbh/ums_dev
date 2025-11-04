<?php
namespace App\Services\Orbis;

use Carbon\Carbon;

class OrbisHelper
{
    public function __construct(protected OrbisApiClient $client)
    {
    }

    public function getUserByUsername(string $username): ?array
    {
        $endpoint = "resources/external/users?name=" . urlencode($username);
        $users = $this->client->send($endpoint)['user'] ?? [];

        $today = Carbon::now()->toDateString();

        foreach ($users as $user) {
            $from = $user['validityperiod']['from']['date'] ?? null;
            $thru = $user['validityperiod']['thru']['date'] ?? null;

            if ((!$from || $from <= $today) && (!$thru || $thru >= $today)) {
                return $user;
            }
        }

        return null;
    }

    public function getEmployeeByUserId(int $userId): ?array
    {
        $today = Carbon::now()->toDateString();
        $endpoint = "resources/external/users/{$userId}/employees?referencedate={$today}";
        return $this->client->send($endpoint)['employee'][0] ?? null;
    }

    public function getEmployeeDetails(array $employee): array
    {
        $today = Carbon::now()->toDateString();
        $id = $employee['id'];
        $human = $employee['humanbeing'] ?? [];

        $salutation = $this->getCatalogTranslation("SALUTATIONS", $human['salutation']['catalogcoding']['code'] ?? "");
        $sex = $this->getCatalogTranslation("SEX", $human['sex']['catalogcoding']['code'] ?? "");
        $title = $this->getCatalogTranslation("TITLES", $human['title']['catalogcoding']['code'] ?? "");
        $state = $this->getCatalogTranslation("STATEOFEMPLOYEE", $employee['state']['catalogcoding']['code'] ?? "");

        return [
            'id' => $id,
            'firstname' => $human['firstname'] ?? null,
            'surname' => $human['surname'] ?? null,
            'sex' => $sex,
            'salutation' => $salutation,
            'title' => $title,
            'state' => $state,
            'signinglevel' => $employee['signinglevel'] ?? null,
            'validfrom' => $employee['validityperiod']['from']['date'] ?? null,
            'validthru' => $employee['validityperiod']['thru']['date'] ?? null,
            'facilities' => $this->getEmployeeFacilities($id, $today),
        ];
    }

    public function getEmployeeFacilities(int $employeeId, string $today): array
    {
        $endpoint = "resources/external/employees/{$employeeId}/facilityassignments?referencedate={$today}";
        $response = $this->client->send($endpoint);
        $result = [];

        foreach ($response['employeefacilityassignment'] ?? [] as $entry) {
            $fid = $entry['facility']['id'] ?? null;
            if ($fid) {
                $detail = $this->client->send("resources/external/facilities/{$fid}");
                $result[] = [
                    'id' => $fid,
                    'name' => $detail['name'] ?? null,
                    'shortname' => $detail['shortname'] ?? null,
                ];
            }
        }

        return $result;
    }

    public function getCatalogTranslation(string $codesystem, string $code): array
    {
        if (!$code) return [];

        $endpoint = "resources/external/catalogs?codesystem={$codesystem}&code=" . urlencode($code) . "&includecatalogtranslations=true";
        $data = $this->client->send($endpoint);

        $result = [
            'id' => $data['id'] ?? null,
            'code' => $code,
            'shortname' => null,
            'longname' => null,
        ];

        foreach ($data['catalogtranslations']['catalogtranslation'] ?? [] as $trans) {
            if (in_array($trans['languageoftranslation']['id'] ?? '', ['de', 'de_CH'])) {
                $result['shortname'] = $trans['shortname'] ?? null;
                $result['longname'] = $trans['longname'] ?? null;
                break;
            }
        }

        return $result;
    }
}
