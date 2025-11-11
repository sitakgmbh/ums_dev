<?php

namespace App\Services;

use LdapRecord\Models\ActiveDirectory\User as LdapUser;
use App\Models\User;
use App\Utils\Logging\Logger;

class LdapProvisioningService
{
    public function provisionOrUpdateUserFromLdap(LdapUser $ldapUser, string $username, bool $create = false, ?User $existingUser = null): User 
	{
        $sid = $ldapUser->getConvertedSid();
		
		$groups = collect($ldapUser->getAttribute("memberOf") ?? [])
			->map(fn($dn) => preg_match("/CN=([^,]+)/i", $dn, $m) ? $m[1] : null)
			->filter()
			->values()
			->toArray();

        $newRole = null;
		
        if (env("LDAP_ADMIN_GROUP") && in_array(env("LDAP_ADMIN_GROUP"), $groups, true)) 
		{
            $newRole = "admin";
        } 
		elseif (env("LDAP_USER_GROUP") && in_array(env("LDAP_USER_GROUP"), $groups, true)) 
		{
            $newRole = "user";
        }

        $user = $existingUser ?? new User();

        $user->fill([
            "username"   => $username,
            "firstname"  => $ldapUser->getFirstAttribute("givenname") ?? "",
            "lastname"   => $ldapUser->getFirstAttribute("sn") ?? "",
            "email"      => $ldapUser->getFirstAttribute("mail") ?? "",
            "auth_type"  => "ldap",
            "ad_sid"     => $sid,
            "is_enabled" => null,
        ]);

        if ($create) 
		{
            $user->password = ""; 
        }

        $user->save();

        if ($newRole) 
		{
            $user->syncRoles([$newRole]);
        } 
		else 
		{
            $user->syncRoles([]);
            Logger::warning("Benutzer {$username} hat keine gültige AD-Gruppe mehr, Rollen entfernt");
        }

        return $user;
    }

	public function userHasAccess(LdapUser $ldapUser): bool
	{
		$groups = collect($ldapUser->getAttribute("memberOf") ?? [])
			->map(fn($dn) => preg_match("/CN=([^,]+)/i", $dn, $m) ? $m[1] : null)
			->filter()
			->values()
			->toArray();

		$admin = env("LDAP_ADMIN_GROUP");
		$user  = env("LDAP_USER_GROUP");

		return in_array($admin, $groups, true) || in_array($user, $groups, true);
	}

}
