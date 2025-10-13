<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Utils\Logging\Logger;
use LdapRecord\Models\ActiveDirectory\User as LdapUser;
use App\Services\LdapProvisioningService;

class ApiAuthSwitcher
{
    public function handle($request, Closure $next)
    {
        $mode = env('AUTH_MODE');

        if ($mode === 'sso') {
            return $this->handleSsoAuth($request, $next);
        }

        return $this->handleLocalAuth($request, $next);
    }

    // ============================================================
    // SSO-Authentifizierung (Apache mod_auth_sspi)
    // ============================================================
    private function handleSsoAuth($request, Closure $next)
    {
        if (! isset($_SERVER['REMOTE_USER'])) {
            Logger::db('auth', 'warning', 'SSO fehlgeschlagen: kein REMOTE_USER', $this->logMeta($request));
            return response()->json(['message' => 'Unauthenticated (no REMOTE_USER)'], 401);
        }

        // Wenn Laravel bereits eingeloggt ist → weiter
        if (Auth::check()) {
            return $next($request);
        }

        $rawUser  = $_SERVER['REMOTE_USER'];
        $username = explode('\\', $rawUser)[1] ?? $rawUser;

        Logger::debug("ApiAuthSwitcher: REMOTE_USER = {$rawUser}");

        $user = User::where('username', $username)->first();
        $service = app(LdapProvisioningService::class);

        if (! $user) {
            $ldapUser = LdapUser::query()->where('samaccountname', '=', $username)->first();

            if (! $ldapUser) {
                Logger::db('auth', 'warning', "SSO-Benutzer {$username} nicht im AD gefunden", $this->logMeta($request));
                return response()->json(['message' => 'User not found in AD'], 403);
            }

            $user = $service->provisionOrUpdateUserFromLdap($ldapUser, $username, true);

            Logger::db('auth', 'info', "SSO-Benutzer {$username} automatisch provisioniert", [
                'user_id' => $user->id,
                ...$this->logMeta($request),
            ]);
        } 
        else {
            $ldapUser = LdapUser::query()->where('samaccountname', '=', $username)->first();

            if ($ldapUser) {
                $service->provisionOrUpdateUserFromLdap($ldapUser, $username, false, $user);
                Logger::debug("ApiAuthSwitcher: Benutzer {$username} im AD synchronisiert");
            }
        }

        if (! $user) {
            Logger::db('auth', 'warning', "SSO-Login fehlgeschlagen: Benutzer {$username} nicht gefunden", $this->logMeta($request));
            return response()->json(['message' => 'Unauthenticated (SSO expected)'], 401);
        }

        Auth::login($user);

        Logger::db('auth', 'info', "SSO-Login Benutzer {$username} erfolgreich (API)", [
            'user_id' => $user->id,
            ...$this->logMeta($request),
        ]);

        return $next($request);
    }

    // ============================================================
    // Token-Authentifizierung (Sanctum)
    // ============================================================
    private function handleLocalAuth($request, Closure $next)
    {
        if ($request->user('sanctum')) {
            return $next($request);
        }

        Logger::db('auth', 'warning', 'Token-Authentifizierung fehlgeschlagen', $this->logMeta($request));
        return response()->json(['message' => 'Unauthenticated (Token required)'], 401);
    }

    // ============================================================
    // Hilfsfunktion: Meta-Infos fürs Logging
    // ============================================================
    private function logMeta($request): array
    {
        return [
            'ip'         => $request->ip(),
            'userAgent'  => $request->userAgent(),
        ];
    }
}
