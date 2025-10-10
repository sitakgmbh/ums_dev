<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use LdapRecord\Models\ActiveDirectory\User as LdapUser;
use App\Models\User;
use App\Utils\Logging\Logger;
use App\Services\LdapProvisioningService;

class SsoAutoLogin
{
    public function handle($request, Closure $next)
    {
        // Nur aktiv, wenn SSO aktiviert ist
        if (env('AUTH_MODE') !== 'sso') 
		{
            return $next($request);
        }

        // Prüfen, ob Apache bereits authentifiziert hat
        if (!Auth::check() && isset($_SERVER['REMOTE_USER'])) 
		{
            $username = Str::after($_SERVER['REMOTE_USER'], '\\');
            Logger::debug("SSO-AutoLogin Middleware: {$username}");

            // Benutzer aus DB laden
            $user = User::where('username', $username)->first();

			$service = app(LdapProvisioningService::class);

            // Wenn Benutzer nicht existiert, neu anlegen (Provisionierung)
            if (! $user) 
			{
                $ldapUser = LdapUser::query()->where('samaccountname', '=', $username)->first();

                if ($ldapUser) 
				{
					$user = $service->provisionOrUpdateUserFromLdap($ldapUser, $username, true);

                    Logger::debug("SSO-User {$username} automatisch provisioniert", ['user_id' => $user->id]);
                } 
				else 
				{
                    Logger::warning("SSO-User {$username} nicht im AD gefunden – keine Provisionierung möglich");
                }
            } 
			else 
			{
                // Benutzer existiert → Gruppen und Felder aktualisieren
                $ldapUser = LdapUser::query()->where('samaccountname', '=', $username)->first();

                if ($ldapUser) 
				{
                    $service->provisionOrUpdateUserFromLdap($ldapUser, $username, false, $user);
                    Logger::debug("SSO-User {$username} Gruppen/Rollen synchronisiert", ['user_id' => $user->id]);
                }
            }

            // Benutzer anmelden
            if ($user) 
			{
                Auth::login($user);
                Logger::debug("SSO-Login erfolgreich für {$username}");
            }
        }

        return $next($request);
    }
}
