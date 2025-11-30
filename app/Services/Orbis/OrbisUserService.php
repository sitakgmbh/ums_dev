<?php
namespace App\Services\Orbis;

use InvalidArgumentException;
use Carbon\Carbon;
use App\Utils\Logging\Logger;

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

        if (!$user) 
		{
            throw new InvalidArgumentException("Benutzer nicht gefunden: {$username}");
        }

        $employee = $this->helper->getEmployeeByUserId($user['id']);
        
        if (!$employee) 
		{
            throw new InvalidArgumentException("Kein Mitarbeiter für Benutzer {$username} gefunden.");
        }
        
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

        $this->validateInput($input);

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

        $this->client->send("resources/external/employeefacilityassignments", "POST", [
            'employee' => ['id' => $employeeId],
            'facility' => ['id' => 1],
            'type' => ['id' => 41280],
            'validityperiod' => ['from' => ['date' => $today, 'handling' => 'inclusive']]
        ]);
		
        $log[] = "Zuweisung Facility an Mitarbeiter abgeschlossen";

        $employeeFunctionId = $input['employee_function'] ?? null;
		
        if ($employeeFunctionId) 
		{
            $this->client->send("resources/external/employeeemployeefunctionassignments", "POST", [
                'employee' => ['id' => $employeeId],
                'employeefunction' => ['id' => (int)$employeeFunctionId],
                'validityperiod' => ['from' => ['date' => $today, 'handling' => 'inclusive']]
            ]);
			
            $log[] = "Zuweisung Mitarbeiterfunktion abgeschlossen";
        } 
		else 
		{
            $log[] = "Keine Mitarbeiterfunktion angegeben";
        }

        $userPayload = [
            'name' => $username,
            'password' => base64_encode($input['password']),
            'canchangepassword' => true,
            'mustchangepassword' => true,
            'passwordrefreshinterval' => 120,
            'locked' => false,
            'validityperiod' => ['from' => ['date' => $today, 'handling' => 'inclusive']],
            'facility' => ['id' => 1],
            'languages' => [
                'language' => [['id' => 'de_CH'], ['id' => 'de']]
            ]
        ];

        $response = $this->client->send("resources/external/employees/{$employeeId}/users", "POST", $userPayload, true);
        $userId = $this->extractLocationId($response['headers'], 'users');
        $log[] = "Benutzer erstellt (ID: {$userId})";

        $this->client->send("resources/external/userfacilityassignments", "POST", [
            'user' => ['id' => $userId],
            'facility' => ['id' => 1],
            'type' => ['id' => 41280],
            'validityperiod' => ['from' => ['date' => $today, 'handling' => 'inclusive']]
        ]);
		
        $log[] = "Zuweisung Facility an Benutzer abgeschlossen";

        foreach ($input['orgunits'] ?? [] as $unit) 
		{
            $assignment = [
                'employee' => ['id' => $employeeId],
                'organizationalunit' => ['id' => $unit['id']],
                'validityperiod' => ['from' => ['date' => $today, 'handling' => 'inclusive']]
            ];
            
            if (!empty($unit['rank'])) 
			{
                $assignment['rank'] = ['id' => (int)$unit['rank']];
            }
            
            $this->client->send("resources/external/employeeorganizationalunitassignments", "POST", $assignment);
        }
		
        $log[] = "Zuweisung Organisationseinheiten abgeschlossen";

        foreach ($input['orggroups'] ?? [] as $groupId) 
		{
            $this->client->send("resources/external/employeeorganizationalunitgroupassignments", "POST", [
                'employee' => ['id' => $employeeId],
                'organizationalunitgroup' => ['id' => $groupId],
                'validityperiod' => ['from' => ['date' => $today, 'handling' => 'inclusive']]
            ]);
        }
		
        $log[] = "OE-Gruppen zugewiesen";

        foreach ($input['roles'] ?? [] as $roleId) 
		{
            $this->client->send("resources/external/userroleassignments", "POST", [
                'user' => ['id' => $userId],
                'role' => ['id' => $roleId],
                'validityperiod' => ['from' => ['date' => $today, 'handling' => 'inclusive']]
            ]);
        }
		
        $log[] = "Zuweisung Benutzerrollen abgeschlossen";

        return ['success' => true, 'log' => $log];
    }

    public function updateKisUser(int $mutationId, array $input): array
    {
        $today = Carbon::now()->toDateString();
        $log = [];

        $this->validateInput($input);

        $username = strtoupper($input['username']);
        $user = $this->helper->getUserByUsername($username);

        if (!$user) 
		{
            throw new InvalidArgumentException("Benutzer {$username} nicht gefunden.");
        }

        $userId = $user['id'];
        $employee = $this->helper->getEmployeeByUserId($userId);
        
        if (!$employee) 
		{
            throw new InvalidArgumentException("Kein zugeordneter Mitarbeiter gefunden.");
        }
        
        $employeeId = $employee['id'];

        $orgUnits = $input['orgunits'] ?? [];
        $orgGroups = $input['orggroups'] ?? [];
        $roles = $input['roles'] ?? [];
        $employeeFunctionId = $input['employeeFunction'] ?? null;
        $permissionMode = $input['permissionMode'] ?? 'replace';

        if ($permissionMode === 'replace') 
		{
            $log[] = "Existierende Zuweisungen werden vollständig ersetzt";
            
            $this->disableAllEmployeeOrganizationalUnits($employeeId);
            $this->disableAllEmployeeOrganizationalUnitGroups($employeeId);
            $this->disableAllUserRoles($userId);
        } 
		else 
		{
            $log[] = "Ergänzungsmodus – bestehende Zuweisungen bleiben erhalten";
        }

        foreach ($orgUnits as $unit) 
		{
            $assignment = [
                'employee' => ['id' => $employeeId],
                'organizationalunit' => ['id' => $unit['id']],
                'validityperiod' => ['from' => ['date' => $today, 'handling' => 'inclusive']]
            ];
            
            if (!empty($unit['rank'])) 
			{
                $assignment['rank'] = ['id' => (int)$unit['rank']];
            }
            
            $this->client->send("resources/external/employeeorganizationalunitassignments", "POST", $assignment);
        }
		
        $log[] = "Organisationseinheiten verarbeitet";

        foreach ($orgGroups as $groupId) 
		{
            $this->client->send("resources/external/employeeorganizationalunitgroupassignments", "POST", [
                'employee' => ['id' => $employeeId],
                'organizationalunitgroup' => ['id' => $groupId],
                'validityperiod' => ['from' => ['date' => $today, 'handling' => 'inclusive']]
            ]);
        }
		
        $log[] = "Organisationseinheitengruppen verarbeitet";

        foreach ($roles as $roleId) 
		{
            $this->client->send("resources/external/userroleassignments", "POST", [
                'user' => ['id' => $userId],
                'role' => ['id' => $roleId],
                'validityperiod' => ['from' => ['date' => $today, 'handling' => 'inclusive']]
            ]);
        }
		
        $log[] = "Benutzerrollen verarbeitet";

        if ($employeeFunctionId) 
		{
            $this->client->send("resources/external/employeeemployeefunctionassignments", "POST", [
                'employee' => ['id' => $employeeId],
                'employeefunction' => ['id' => (int)$employeeFunctionId],
                'validityperiod' => ['from' => ['date' => $today, 'handling' => 'inclusive']]
            ]);
			
            $log[] = "Mitarbeiterfunktion aktualisiert";
        }

        return ['success' => true, 'log' => $log];
    }

    protected function extractLocationId(array $headers, string $resource): ?int
    {
        foreach ($headers as $h) 
		{
            if (stripos($h, 'Location:') !== false && preg_match("#/{$resource}/(\\d+)#", $h, $m)) 
			{
                return (int)$m[1];
            }
        }
		
        return null;
    }

    protected function findAvailableUsername(string $base, array &$log): string
    {
        $prefix = $base;
        $counter = 0;

        if (preg_match('/^(.*?)(\d+)$/', $base, $matches)) 
		{
            $prefix = $matches[1];
            $counter = (int)$matches[2];
        }

        while (true) 
		{
            $test = $counter === 0 ? $prefix : $prefix . $counter;
            $exists = $this->helper->getUserByUsername($test);
			
            if (!$exists) 
			{
                $log[] = "Benutzername '{$test}' ist verfügbar.";
                return $test;
            }
			
            $log[] = "Benutzername '{$test}' ist bereits vergeben.";
            $counter++;
        }
    }

    protected function validateInput(array $input): void
    {
        if (!isset($input['roles']) || !is_array($input['roles']) || count($input['roles']) === 0) 
		{
            throw new InvalidArgumentException("Es muss mindestens eine Rolle ausgewählt werden.");
        }

        $hasOrgUnits = isset($input['orgunits']) && is_array($input['orgunits']) && count($input['orgunits']) > 0;
        $hasOrgGroups = isset($input['orggroups']) && is_array($input['orggroups']) && count($input['orggroups']) > 0;

        if (!$hasOrgUnits && !$hasOrgGroups) 
		{
            throw new InvalidArgumentException("Bitte wähle mindestens eine Organisationseinheit oder eine Organisationseinheitgruppe aus.");
        }
    }

    protected function disableAllEmployeeOrganizationalUnits(int $employeeId): void
    {
        $today = Carbon::now()->toDateString();
        $endpoint = "resources/external/employees/{$employeeId}/organizationalunitassignments?referencedate={$today}";
        
        try 
		{
            $response = $this->client->send($endpoint);
            
            foreach ($response['employeeorganizationalunitassignment'] ?? [] as $assignment) 
			{
                if (!empty($assignment['id']) && $assignment['id'] > 0) 
				{
                    $this->setAssignmentEndDate('employeeorganizationalunitassignments', (int)$assignment['id']);
                }
            }
        } 
		catch (\Exception $e) 
		{
            Logger::warning('Fehler beim Deaktivieren von Organisationseinheiten', ['error' => $e->getMessage()]);
        }
    }

    protected function disableAllEmployeeOrganizationalUnitGroups(int $employeeId): void
    {
        $today = Carbon::now()->toDateString();
        $endpoint = "resources/external/employees/{$employeeId}/organizationalunitgroupassignments?referencedate={$today}";
        
        try 
		{
            $response = $this->client->send($endpoint);
            
            foreach ($response['employeeorganizationalunitgroupassignment'] ?? [] as $assignment) 
			{
                if (!empty($assignment['id']) && $assignment['id'] > 0) 
				{
                    $this->setAssignmentEndDate('employeeorganizationalunitgroupassignments', (int)$assignment['id']);
                }
            }
        } 
		catch (\Exception $e) 
		{
            Logger::warning('Fehler beim Deaktivieren von Organisationseinheitengruppen', ['error' => $e->getMessage()]);
        }
    }

    protected function disableAllUserRoles(int $userId): void
    {
        $today = Carbon::now()->toDateString();
        $endpoint = "resources/external/users/{$userId}/roleassignments?referencedate={$today}";
        
        try 
		{
            $response = $this->client->send($endpoint);
            
            foreach ($response['userroleassignment'] ?? [] as $assignment) 
			{
                if (!empty($assignment['id']) && $assignment['id'] > 0) 
				{
                    $this->setAssignmentEndDate('userroleassignments', (int)$assignment['id']);
                }
            }
        } 
		catch (\Exception $e) 
		{
            Logger::warning('Fehler beim Deaktivieren von Benutzerrollen', ['error' => $e->getMessage()]);
        }
    }

    protected function setAssignmentEndDate(string $resource, int $id): void
    {
        $endpoint = "resources/external/{$resource}/{$id}";
        $yesterday = Carbon::now()->subDay()->toDateString();

        try 
		{
            $existing = $this->client->send($endpoint);

            if (!is_array($existing) || empty($existing['id'])) 
			{
                return;
            }

            $from = $existing['validityperiod']['from'] ?? ['date' => '2000-01-01'];

			$payload = [
				'id' => $existing['id'],
				'user' => ['id' => $existing['user']['id']],
				'role' => ['id' => $existing['role']['id']],
				'canceled' => true,
				'validityperiod' => [
					'from' => $existing['validityperiod']['from'],
					'to' => ['date' => $yesterday, 'handling' => 'inclusive']
				]
			];

            $payload['canceled'] = true;
            $payload['validityperiod'] = [
                'from' => $from,
                'to' => ['date' => $yesterday]
            ];

            $this->client->send("resources/external/{$resource}", "PUT", $payload);
        } 
		catch (\Exception $e) 
		{
            Logger::warning("Fehler beim Setzen des Enddatums für {$resource}/{$id}", ['error' => $e->getMessage()]);
        }
    }
}