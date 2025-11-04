<?php
namespace App\Services\Orbis;

use Illuminate\Support\Facades\Log;
use InvalidArgumentException;
use Carbon\Carbon;

class OrbisUserService
{
    public function __construct(
        protected OrbisHelper $helper,
        protected OrbisApiClient $client
    ) {}

    public function getUserDetails(string $username): array
    {
        $username = strtoupper($username);
        $user = $this->helper->getUserByUsername($username);

        if (!$user) {
            throw new InvalidArgumentException("Benutzer nicht gefunden: {$username}");
        }

        $employee = $this->helper->getEmployeeByUserId($user['id']);
        $details = $this->helper->getEmployeeDetails($employee);

        return [
            'user' => $user,
            'employee' => $details,
        ];
    }

    public function createKisUser(int $eroeffnungId, array $input): array
    {
        $today = Carbon::now()->toDateString();
        $log = [];

        $baseUsername = strtoupper($input['base_username']);
        $username = $this->findAvailableUsername($baseUsername, $log);

        $employeePayload = [
            'shortname' => $username,
            'humanbeing' => [
                'firstname' => $input['firstname'] ?? '',
                'surname' => $input['surname'] ?? '',
                'salutation' => ['id' => $input['salutation_id'] ?? 29310],
                'sex' => ['id' => $input['sex_id'] ?? 29614],
            ],
            'language' => ['id' => 'de_CH'],
            'state' => ['id' => $input['employee_state_id'] ?? null],
            'validityperiod' => ['from' => ['date' => $today, 'handling' => 'inclusive']],
        ];

        $response = $this->client->send("resources/external/employees", "POST", $employeePayload, true);
        $employeeId = $this->extractLocationId($response['headers'], 'employees');
        $log[] = "Mitarbeiter erstellt (ID: {$employeeId})";

        $userPayload = [
            'name' => $username,
            'password' => base64_encode($input['password']),
            'mustchangepassword' => true,
            'canchangepassword' => true,
            'validityperiod' => ['from' => ['date' => $today, 'handling' => 'inclusive']],
        ];

        $response = $this->client->send("resources/external/employees/{$employeeId}/users", "POST", $userPayload, true);
        $userId = $this->extractLocationId($response['headers'], 'users');
        $log[] = "Benutzer erstellt (ID: {$userId})";

        return ['success' => true, 'log' => $log];
    }

    public function updateKisUser(int $mutationId, array $input): array
    {
        $today = Carbon::now()->toDateString();
        $log = [];

        $username = strtoupper($input['username']);
        $user = $this->helper->getUserByUsername($username);

        if (!$user) {
            throw new InvalidArgumentException("Benutzer {$username} nicht gefunden.");
        }

        $userId = $user['id'];
        $employee = $this->helper->getEmployeeByUserId($userId);
        $employeeId = $employee['id'];

        foreach ($input['roles'] ?? [] as $roleId) {
            $this->client->send("resources/external/userroleassignments", "POST", [
                'user' => ['id' => $userId],
                'role' => ['id' => $roleId],
                'validityperiod' => ['from' => ['date' => $today, 'handling' => 'inclusive']],
            ]);
        }

        $log[] = "Benutzerrollen aktualisiert.";
        return ['success' => true, 'log' => $log];
    }

    protected function extractLocationId(array $headers, string $resource): ?int
    {
        foreach ($headers as $h) {
            if (stripos($h, 'Location:') !== false && preg_match("#/{$resource}/(\\d+)#", $h, $m)) {
                return (int)$m[1];
            }
        }
        return null;
    }

    protected function findAvailableUsername(string $base, array &$log): string
    {
        $prefix = $base;
        $counter = 0;

        if (preg_match('/^(.*?)(\d+)$/', $base, $matches)) {
            $prefix = $matches[1];
            $counter = (int)$matches[2];
        }

        while (true) {
            $test = $counter === 0 ? $prefix : $prefix . $counter;
            $exists = $this->helper->getUserByUsername($test);
            if (!$exists) {
                $log[] = "Benutzername '{$test}' ist verfügbar.";
                return $test;
            }
            $log[] = "Benutzername '{$test}' ist bereits vergeben.";
            $counter++;
        }
    }
}