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
        
        foreach ($users as $user) 
		{
            $from = $user['validityperiod']['from']['date'] ?? null;
            $thru = $user['validityperiod']['thru']['date'] ?? null;
			
            if ((!$from || $from <= $today) && (!$thru || $thru >= $today)) 
			{
                return $user;
            }
        }
        return null;
    }
    
    public function getEmployeeByUserId(int $userId): ?array
    {
        $today = Carbon::now()->toDateString();
        $endpoint = "resources/external/users/{$userId}/employees?referencedate={$today}&includecatalogtranslations=true";
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
            'organizationalunits' => $this->getEmployeeOrganizationalUnits($id, $today),
            'organizationalunitgroups' => $this->getEmployeeOrganizationalUnitGroups($id, $today),
            'users' => $this->getEmployeeUsers($id, $today),
        ];
    }
    
    public function getEmployeeFacilities(int $employeeId, string $today): array
    {
        $endpoint = "resources/external/employees/{$employeeId}/facilityassignments?referencedate={$today}";
        
        try 
		{
            $response = $this->client->send($endpoint);
            $result = [];
            
            foreach ($response['employeefacilityassignment'] ?? [] as $entry) 
			{
                $fid = $entry['facility']['id'] ?? null;
				
                if ($fid) 
				{
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
		catch (\Exception $e) 
		{
            return [];
        }
    }
    
    public function getEmployeeOrganizationalUnits(int $employeeId, string $today): array
    {
        $endpoint = "resources/external/employees/{$employeeId}/organizationalunitassignments?referencedate={$today}";
        
        try 
		{
            $response = $this->client->send($endpoint);
            $result = [];
            
            foreach ($response['employeeorganizationalunitassignment'] ?? [] as $assignment) 
			{
                $unitId = $assignment['organizationalunit']['id'] ?? null;
                
                if ($unitId) 
				{
                    $detail = $this->client->send("resources/external/organizationalunits/{$unitId}");
                    
                    $result[] = [
                        'id' => $unitId,
                        'name' => $detail['name'] ?? null,
                        'shortname' => $detail['shortname'] ?? null,
                        'type' => $detail['type']['catalogcoding']['code'] ?? null,
                        'rank' => $this->getRank($assignment['rank'] ?? null),
                    ];
                }
            }
            
            return $result;
        } 
		catch (\Exception $e) 
		{
            return [];
        }
    }
    
    public function getEmployeeOrganizationalUnitGroups(int $employeeId, string $today): array
    {
        $endpoint = "resources/external/employees/{$employeeId}/organizationalunitgroupassignments?referencedate={$today}";
        
        try 
		{
            $response = $this->client->send($endpoint);
            $result = [];
            
            foreach ($response['employeeorganizationalunitgroupassignment'] ?? [] as $assignment) 
			{
                $groupId = $assignment['organizationalunitgroup']['id'] ?? null;
                
                if (!$groupId) continue;
                
                $detail = $this->client->send("resources/external/organizationalunitgroups/{$groupId}");
                
                $name = $detail['name'] ?? null;
                $shortname = $detail['shortname'] ?? null;
                $type = $detail['organizationalunitgrouptypeassignments']['organizationalunitgrouptypeassignment'][0]['type']['catalogcoding']['code'] ?? null;
                
                if ($name && $shortname && $type) 
				{
                    $result[] = [
                        'id' => $groupId,
                        'name' => $name,
                        'shortname' => $shortname,
                        'type' => $type,
                    ];
                }
            }
            
            return $result;
        } 
		catch (\Exception $e) 
		{
            return [];
        }
    }
    
    public function getEmployeeUsers(int $employeeId, string $today): array
    {
        $endpoint = "resources/external/employees/{$employeeId}/users?referencedate={$today}";
        
        try 
		{
            $response = $this->client->send($endpoint);
            $result = [];
            
            foreach ($response['user'] ?? [] as $user) 
			{
                $userId = $user['id'];
                $result[] = [
                    'id' => $userId,
                    'username' => $user['name'] ?? null,
                    'description' => $user['description'] ?? null,
                    'validfrom' => $user['validityperiod']['from']['date'] ?? null,
                    'validthru' => $user['validityperiod']['thru']['date'] ?? null,
                    'locked' => $user['locked'] ?? null,
                    'mustchangepassword' => $user['mustchangepassword'] ?? null,
                    'passwordrefreshinterval' => $user['passwordrefreshinterval'] ?? null,
                    'roles' => $this->getUserRoles($userId, $today),
                ];
            }
            
            return $result;
        } 
		catch (\Exception $e) 
		{
            return [];
        }
    }
    
    public function getUserRoles(int $userId, string $today): array
    {
        $endpoint = "resources/external/users/{$userId}/roleassignments?referencedate={$today}";
        
        try 
		{
            $response = $this->client->send($endpoint);
            $result = [];
            
            foreach ($response['userroleassignment'] ?? [] as $assignment) 
			{
                $roleId = $assignment['role']['id'] ?? null;
                
                if (!$roleId) continue;
                
                $details = $this->client->send("resources/external/roles/{$roleId}");
                
                $result[] = [
                    'id' => $roleId,
                    'name' => $details['name'] ?? 'Unbekannt',
                ];
            }
            
            return $result;
        } 
		catch (\Exception $e) 
		{
            return [];
        }
    }
    
    public function getRank(?array $rank): ?array
    {
        if (!isset($rank['id'])) 
		{
            return null;
        }
        
        try 
		{
            $details = $this->client->send("resources/external/catalogs/{$rank['id']}");
            
            return [
                'id' => $rank['id'],
                'code' => $details['catalogcoding']['code'] ?? null,
            ];
        } 
		catch (\Exception $e) 
		{
            return null;
        }
    }
    
    public function getCatalogTranslation(string $codesystem, string $code): array
    {
        if (!$code) return [];
        
        $endpoint = "resources/external/catalogs?codesystem={$codesystem}&code=" . urlencode($code) . "&includecatalogtranslations=true";
        
        try 
		{
            $data = $this->client->send($endpoint);
            
            $result = [
                'id' => $data['id'] ?? null,
                'code' => $code,
                'shortname' => null,
                'longname' => null,
            ];
            
            foreach ($data['catalogtranslations']['catalogtranslation'] ?? [] as $trans) 
			{
                if (in_array($trans['languageoftranslation']['id'] ?? '', ['de', 'de_CH'])) 
				{
                    $result['shortname'] = $trans['shortname'] ?? null;
                    $result['longname'] = $trans['longname'] ?? null;
                    break;
                }
            }
            
            return $result;
        } 
		catch (\Exception $e) 
		{
            return [];
        }
    }
}