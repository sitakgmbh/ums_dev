<?php

namespace App\Providers;

use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use LdapRecord\Container;
use LdapRecord\Models\ActiveDirectory\User as LdapUser;
use App\Utils\Logging\Logger;
use App\Models\User;

class CustomUserProvider implements UserProvider
{
    public function retrieveById($identifier)
    {
        return User::find($identifier);
    }

    public function retrieveByToken($identifier, $token)
    {
        return User::where("id", $identifier)
            ->where("remember_token", $token)
            ->first();
    }

    public function updateRememberToken(Authenticatable $user, $token)
    {
        $user->setRememberToken($token);
        $user->save();
    }

	public function retrieveByCredentials(array $credentials)
	{
		// SSO-Login
		if (env("LDAP_SSO_ENABLED", false) && isset($_SERVER["REMOTE_USER"])) 
		{
			$username = Str::after($_SERVER["REMOTE_USER"], "\\");
			Logger::debug("Verarbeite SSO-Login von {$username}");
			$ldapUser = LdapUser::query()->where("samaccountname", "=", $username)->first();
			
			if (! $ldapUser) 
			{
				Logger::debug("SSO-User nicht im AD gefunden: {$username}");
				return null;
			}

			return $this->handleLdapUser($ldapUser, $username);
		}

		// Form-Login
		$usernameRaw = $credentials["username"] ?? "";
		$username = str_contains($usernameRaw, "\\") ? explode("\\", $usernameRaw)[1] : $usernameRaw;

		Logger::debug("Authentifiziere Benutzer {$username}");

		$user = User::where("username", $username)->first();

		if ($user) 
		{
			if ($user->auth_type === "local") 
			{
				Logger::debug("Lokalen Benutzer {$username} in Datenbank gefunden");
				return $user;
			}

			if ($user->auth_type === "ldap") 
			{
				Logger::debug("LDAP-Benutzer {$username} in Datenbank gefunden");
				return $user;
			}
		}

		Logger::debug("Benutzer {$username} nicht in Datenbank gefunden, suche LDAP-Benutzer im AD");
		$ldapUser = LdapUser::query()->where("samaccountname", "=", $username)->first();
		
		if ($ldapUser) 
		{
			Logger::debug("LDAP-Benutzer {$username} gefunden, authentifiziere");
			return $this->handleLdapUser($ldapUser, $username);
		}

		Logger::debug("LDAP-Benutzer nicht gefunden: {$username}");
		
		$this->logDb("auth", "warning", "Loginversuch mit unbekanntem Benutzer: {$username}");
		return null;
	}

	public function validateCredentials(Authenticatable $user, array $credentials)
	{
		$usernameRaw = $credentials["username"] ?? "";
		$username = str_contains($usernameRaw, "\\") ? explode("\\", $usernameRaw)[1] : $usernameRaw;
		$password = $credentials["password"] ?? "";

		if ($user->auth_type === "local") 
		{
			if (! $user->is_enabled) 
			{
				$this->logDb("auth", "warning", "Login Benutzer {$username} fehlgeschlagen: Deaktiviert");
				return false;
			}

			$ok = Hash::check($password, $user->getAuthPassword());
			
			if ($ok) 
			{
				$this->logDb("auth", "info", "Login Benutzer {$username} erfolgreich", ["user_id" => $user->id]);
			} 
			else 
			{
				$this->logDb("auth", "warning", "Login Benutzer {$username} fehlgeschlagen: Passwort falsch");
			}
			
			return $ok;
		}

		if ($user->auth_type === "ldap") 
		{
			if (env("LDAP_SSO_ENABLED", false) && isset($_SERVER["REMOTE_USER"])) 
			{
				$this->logDb("auth", "info", "SSO-Login erfolgreich: {$username}", ["user_id" => $user->id]);
				return true;
			}

			$domain   = env("LDAP_DOMAIN_NAME");
			$fullUser = $domain . "\\" . $username;

			try 
			{
				$ldap   = Container::getDefaultConnection();
				$result = $ldap->auth()->attempt($fullUser, $password, true);

				if ($result) 
				{
					$this->logDb("auth", "info", "LDAP-Login Benutzer {$username} erfolgreich", ["user_id" => $user->id]);
				} 
				else 
				{
					$this->logDb("auth", "warning", "LDAP-Login Benutzer {$username} fehlgeschlagen: Passwort falsch oder Benutzer deaktiviert");
				}

				return $result;
			} 
			catch (\Throwable $e) 
			{
				$this->logDb("auth", "error", "LDAP-Login Benutzer {$username} fehlgeschlagen", ["errorMessage" => $e->getMessage()]);
				return false;
			}
		}

		return false;
	}

	private function ldapAuthenticate(string $username, string $password): bool
	{
		$domain   = env("LDAP_DOMAIN_NAME");
		$fullUser = $domain . "\\" . $username;

		try 
		{
			$ldap = Container::getDefaultConnection();
			return $ldap->auth()->attempt($fullUser, $password, true);
		} 
		catch (\Throwable $e) 
		{
			$this->logDb("auth", "error", "LDAP-Login Benutzer {$username} fehlgeschlagen", ["errorMessage" => $e->getMessage()]);
			return false;
		}
	}

	private function handleLdapUser(LdapUser $ldapUser, string $username): ?User
	{
		$sid = $ldapUser->getConvertedSid();

		$groups = collect($ldapUser->getAttribute("memberOf", []))
			->map(fn($dn) => preg_match("/CN=([^,]+)/i", $dn, $m) ? $m[1] : null)
			->filter()
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

		if (! $newRole) 
		{
			$this->logDb("auth", "warning", "LDAP-Login Benutzer {$username} fehlgeschlagen: Keine Berechtigung");
			return null;
		}

		$user = User::firstOrNew(["ad_sid" => $sid]);

		$user->fill([
			"username"   => $username,
			"firstname"  => $ldapUser->getFirstAttribute("givenname") ?? "",
			"lastname"   => $ldapUser->getFirstAttribute("sn") ?? "",
			"email"      => $ldapUser->getFirstAttribute("mail") ?? "",
			"auth_type"  => "ldap",
			"ad_sid"     => $sid,
			"is_enabled" => true,
		]);

		if (! $user->exists) 
		{
			$user->password = "";
			$user->save();
			Logger::debug("LDAP-Benutzer {$username} erstellt", ["user_id" => $user->id]);
		} 
		else 
		{
			$user->save();
		}

		$user->syncRoles([$newRole]);

		return $user;
	}

    public function rehashPasswordIfRequired(Authenticatable $user, array $credentials, bool $force = false): void
    {
        if ($user->auth_type === "local" && isset($credentials["password"]) && Hash::needsRehash($user->getAuthPassword()))
		{
            $user->password = Hash::make($credentials["password"]);
            $user->save();
        }
    }
	
	private function logDb(string $channel, string $level, string $message, array $extra = []): void
	{
		Logger::db($channel, $level, $message, array_merge([
			"ip" => request()->ip(),
			"userAgent" => request()->userAgent(),
		], $extra));
	}	
}
