<?php

namespace App\Utils;

use App\Models\Eroeffnung;
use LdapRecord\Models\ActiveDirectory\User as LdapUser;
use App\Utils\Logging\Logger;

class LdapHelper
{
	public static function getAdUser(string $username): ?LdapUser
	{
		if (! $username) 
		{
			return null;
		}

		return LdapUser::query()
			->whereEquals("samaccountname", $username)
			->first();
	}
	
	public static function getAdGroups(string $username): array
	{
		$ldapUser = LdapUser::query()
			->whereEquals("samaccountname", $username)
			->first();

		if (! $ldapUser) 
		{
			return [];
		}

		return collect($ldapUser->getAttribute("memberOf", []))
			->map(fn($dn) => preg_match("/CN=([^,]+)/i", $dn, $m) ? $m[1] : null)
			->filter()
			->values()
			->toArray();
	}

	public static function setAdAttribute(string $username, string $attribute, string|array|null $value): void
	{
		$user = self::getAdUser($username);

		if (! $user) 
		{
			throw new \RuntimeException("Fehler beim Aktualisieren von {$attribute}: AD-Benutzer nicht gefunden");
		}

		try 
		{
			$user->setAttribute($attribute, $value);
			$user->save();
		} 
		catch (\Exception $e) 
		{
			throw new \RuntimeException("Fehler beim Aktualisieren von {$attribute} bei Benutzer {$username}: " . $e->getMessage(), 0, $e);
		}
	}

	public static function updateGroupMembership(string $username, array $groups, bool $ignoreErrors = false): void
	{
		$user = self::getAdUser($username);

		if (! $user) 
		{
			throw new \RuntimeException("AD-Benutzer {$username} nicht gefunden.");
		}

		// SamAccountName absichern (kann Array sein)
		$sam = is_array($user->samaccountname) ? ($user->samaccountname[0] ?? $username) : $user->samaccountname;

		foreach ($groups as $groupName => $shouldBeMember) {
			$group = \LdapRecord\Models\ActiveDirectory\Group::query()
				->whereEquals("cn", $groupName)
				->first();

			if (! $group) 
			{
				$msg = "AD-Gruppe {$groupName} nicht gefunden. Benutzer {$sam} wird übersprungen.";
				Logger::warning($msg);
				
				if ($ignoreErrors) 
				{
					continue;
				}
				
				throw new \RuntimeException($msg);
			}

			try 
			{
				if ($shouldBeMember) 
				{
					$group->members()->attach($user);
					Logger::debug("{$sam} zu Gruppe {$groupName} hinzugefügt");
				} 
				else 
				{
					$group->members()->detach($user);
					Logger::debug("{$sam} aus Gruppe {$groupName} entfernt");
				}
			} 
			catch (\Exception $e) 
			{
				$msg = "Fehler bei Gruppe {$groupName} für Benutzer {$sam}: " . $e->getMessage();
				Logger::error($msg);

				if (! $ignoreErrors) 
				{
					throw new \RuntimeException($msg, 0, $e);
				}
			}
		}
	}

	public static function emailExists(string $email, ?string $ignoreUsername = null): bool
	{
		if (! $email) 
		{
			return false;
		}

		$email = strtolower($email);

		$query = \LdapRecord\Models\ActiveDirectory\User::query()
			->whereEquals('mail', $email);

		if ($ignoreUsername) 
		{
			$query->where('samaccountname', '!=', $ignoreUsername);
		}

		if ($query->exists()) 
		{
			return true;
		}

		$query = \LdapRecord\Models\ActiveDirectory\User::query()
			->whereContains('proxyAddresses', "smtp:$email")
			->orWhereContains('proxyAddresses', "SMTP:$email");

		if ($ignoreUsername) 
		{
			$query->where('samaccountname', '!=', $ignoreUsername);
		}

		return $query->exists();
	}
}
