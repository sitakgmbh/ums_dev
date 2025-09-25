<?php

namespace App\Providers;

use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use LdapRecord\Container;
use LdapRecord\Models\ActiveDirectory\User as LdapUser;
use App\Models\User;
use App\Utils\Logging\Logger;

class LoginServiceProvider implements UserProvider
{
    public function retrieveById($identifier)
    {
        return User::find($identifier);
    }

    public function retrieveByToken($identifier, $token)
    {
        return User::where('id', $identifier)
            ->where('remember_token', $token)
            ->first();
    }

    public function updateRememberToken(Authenticatable $user, $token)
    {
        $user->setRememberToken($token);
        $user->save();
    }

	public function retrieveByCredentials(array $credentials)
	{
		if (env('LDAP_SSO_ENABLED', false) && isset($_SERVER['REMOTE_USER'])) 
		{
			$username = Str::after($_SERVER['REMOTE_USER'], '\\');
			Logger::debug("Verarbeite SSO-Login von {$username}");
			return $this->provisionLdapUser($username, true);
		}

		$usernameRaw = $credentials['username'] ?? '';
		$username = str_contains($usernameRaw, '\\') ? explode('\\', $usernameRaw)[1] : $usernameRaw;

		Logger::info("Authentifiziere Benutzer {$username}");
		$user = User::where('username', $username)->first();
		
		if ($user && $user->auth_type === 'local') 
		{
			Logger::info("Benutzer für {$username} in DB gefunden");
			return $user;
		}

		Logger::info("Kein Benutzer für {$username} in DB gefunden, versuche Benutzer anzulegen");
		return $this->provisionLdapUser($username, false);
	}

	public function validateCredentials(Authenticatable $user, array $credentials)
	{
		$username = $credentials['username'] ?? '';
		$password = $credentials['password'] ?? '';

		if ($user->auth_type === 'local') 
		{
			if (! $user->is_enabled) 
			{
				$this->logDb('auth', 'warning', "Login fehlgeschlagen: lokaler Benutzer {$username} ist deaktiviert");
				return false;
			}

			$ok = Hash::check($password, $user->getAuthPassword());
			
			if ($ok) 
			{
				$this->logDb('auth', 'info', "Login erfolgreich: lokaler Benutzer {$username}", ['user_id' => $user->id]);
			} 
			else 
			{
				$this->logDb('auth', 'warning', "Login fehlgeschlagen: falsches Passwort für lokalen Benutzer {$username}");
			}
			
			return $ok;
		}

		if ($user->auth_type === 'ldap') 
		{
			if (env('LDAP_SSO_ENABLED', false) && isset($_SERVER['REMOTE_USER'])) 
			{
				$this->logDb('auth', 'info', "SSO-Login erfolgreich: {$username}", ['user_id' => $user->id]);
				return true;
			}

			$domain   = env('LDAP_DOMAIN_NAME');
			$fullUser = $domain . "\\" . $user->username;

			try 
			{
				$ldap   = Container::getDefaultConnection();
				$result = $ldap->auth()->attempt($fullUser, $password, true);

				if ($result) 
				{
					$this->logDb('auth', 'info', "LDAP-Login erfolgreich: {$username}", ['user_id' => $user->id]);
				} 
				else 
				{
					$this->logDb('auth', 'warning', "Login fehlgeschlagen: LDAP-Passwort ist {$username} ungültig");
				}

				return $result;
			} 
			catch (\Throwable $e) 
			{
				$this->logDb('auth', 'error', "LDAP-Login Exception fuer {$username}: {$e->getMessage()}");
				return false;
			}
		}

		return false;
	}

	private function provisionLdapUser(string $username, bool $isSso): ?User
	{
		$ldapUser = LdapUser::query()->where('samaccountname', '=', $username)->first();
		
		if (! $ldapUser) 
		{
			$this->logDb('auth', 'warning', "LDAP-Benutzer nicht gefunden: {$username}");
			return null;
		}

		$groups = collect($ldapUser->getAttribute('memberOf', []))
			->map(fn($dn) => preg_match('/CN=([^,]+)/i', $dn, $m) ? $m[1] : null)
			->filter()
			->toArray();

		$newRole = null;
		
		if (env('LDAP_ADMIN_GROUP') && in_array(env('LDAP_ADMIN_GROUP'), $groups, true)) 
		{
			$newRole = 'admin';
		} 
		elseif (env('LDAP_USER_GROUP') && in_array(env('LDAP_USER_GROUP'), $groups, true)) 
		{
			$newRole = 'user';
		}

		if (! $newRole) 
		{
			$this->logDb('auth', 'warning', "LDAP-User {$username} ohne gültige Gruppe → kein Zugriff");
			return null;
		}

		$user = User::firstOrNew(['username' => $username]);
		$user->fill([
			'firstname'  => $ldapUser->getFirstAttribute('givenname') ?? '',
			'lastname'   => $ldapUser->getFirstAttribute('sn') ?? '',
			'email'      => $ldapUser->getFirstAttribute('mail') ?? "{$username}@example.local",
			'auth_type'  => 'ldap',
			'is_enabled' => null,
		]);

		if (! $user->exists) 
		{
			$user->password = '';
			$user->save();
			$this->logDb('auth', 'info', "LDAP-Benutzer {$username} neu provisioniert", ['user_id' => $user->id]);
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
        if ($user->auth_type === 'local' && isset($credentials['password']) && Hash::needsRehash($user->getAuthPassword()))
		{
            $user->password = Hash::make($credentials['password']);
            $user->save();
        }
    }
	
	private function logDb(string $channel, string $level, string $message, array $extra = []): void
	{
		Logger::db($channel, $level, $message, array_merge([
			'ip'        => request()->ip(),
			'userAgent' => request()->userAgent(),
		], $extra));
	}	
}
