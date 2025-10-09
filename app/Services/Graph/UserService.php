<?php

namespace App\Services\Graph;

class UserService
{
    public function __construct(protected Connection $base) {}

    /**
     * Alle Benutzer abfragen
     */
    public function listUsers(): array
    {
        return $this->base->call(function () 
		{
            $client = $this->base->getClient();
            $response = $client->users()->get()->wait();
            $users = [];

            if ($response && $response->getValue()) 
			{
                foreach ($response->getValue() as $user) 
				{
                    $users[] = [
                        "id"   => $user->getId(),
                        "name" => $user->getDisplayName(),
                        "upn"  => $user->getUserPrincipalName(),
                        "mail" => $user->getMail(),
                    ];
                }
            }

            return $users;
        }, "listUsers");
    }

    /**
     * Einzelnen Benutzer abfragen
     */
    public function getUser(string $userId): ?array
    {
        return $this->base->call(function () use ($userId) 
		{
            $client = $this->base->getClient();
            $user   = $client->users()->byUserId($userId)->get()->wait();

            if (!$user) 
			{
                return null;
            }

            return [
                "id"   => $user->getId(),
                "name" => $user->getDisplayName(),
                "upn"  => $user->getUserPrincipalName(),
                "mail" => $user->getMail(),
            ];
        }, "getUser({$userId})");
    }

    /**
     * Gruppen eines Benutzers abfragen
     */
    public function getUserGroups(string $userId): array
    {
        return $this->base->call(function () use ($userId) 
		{
            $client = $this->base->getClient();
            $groups = [];

            $response = $client->users()->byUserId($userId)->transitiveMemberOf()->get()->wait();

            if ($response && $response->getValue()) 
			{
                foreach ($response->getValue() as $membership) 
				{
                    $obj = $client->directoryObjects()->byDirectoryObjectId($membership->getId())->get()->wait();

                    if ($obj && $obj->getOdataType() === "#microsoft.graph.group") 
					{
                        $group = $client->groups()->byGroupId($obj->getId())->get()->wait();
						
                        if ($group) 
						{
                            $groups[] = [
                                "id"    => $group->getId(),
                                "name"  => $group->getDisplayName(),
                                "mail"  => $group->getMail(),
                                "types" => $group->getGroupTypes(),
                            ];
                        }
                    }
                }
            }

            return $groups;
        }, "getUserGroups({$userId})");
    }
}
