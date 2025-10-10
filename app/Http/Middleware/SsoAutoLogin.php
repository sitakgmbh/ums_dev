<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use LdapRecord\Models\ActiveDirectory\User as LdapUser;
use App\Models\User;
use App\Utils\Logging\Logger;

class SsoAutoLogin
{
    public function handle($request, Closure $next)
    {
        // Nur aktiv, wenn SSO aktiviert ist
        if (env('AUTH_MODE') !== 'sso') {
            return $next($request);
        }

        // Prüfen, ob Apache bereits authentifiziert hat
        if (!Auth::check() && isset($_SERVER['REMOTE_USER'])) {
            $username = Str::after($_SERVER['REMOTE_USER'], '\\');
            Logger::debug("SSO-AutoLogin Middleware: {$username}");

            // Benutzer aus DB laden
            $user = User::where('username', $username)->first();

            // Wenn Benutzer nicht existiert, neu anlegen (Provisionierung)
            if (! $user) {
                $ldapUser = LdapUser::query()->where('samaccountname', '=', $username)->first();

                if ($ldapUser) {
                    $user = $this->provisionOrUpdateUserFromLdap($ldapUser, $username, true);
                    Logger::debug("SSO-User {$username} automatisch provisioniert", ['user_id' => $user->id]);
                } else {
                    Logger::warning("SSO-User {$username} nicht im AD gefunden – keine Provisionierung möglich");
                }
            } else {
                // Benutzer existiert → Gruppen und Felder aktualisieren
                $ldapUser = LdapUser::query()->where('samaccountname', '=', $username)->first();

                if ($ldapUser) {
                    $this->provisionOrUpdateUserFromLdap($ldapUser, $username, false, $user);
                    Logger::debug("SSO-User {$username} Gruppen/Rollen synchronisiert", ['user_id' => $user->id]);
                }
            }

            // Benutzer anmelden
            if ($user) {
                Auth::login($user);
                Logger::debug("SSO-Login erfolgreich für {$username}");
            }
        }

        return $next($request);
    }

    /**
     * Erstellt oder aktualisiert einen Benutzer anhand der LDAP-Informationen.
     */
    private function provisionOrUpdateUserFromLdap(LdapUser $ldapUser, string $username, bool $create = false, ?User $existingUser = null): User
    {
        $sid = $ldapUser->getConvertedSid();
        $groups = collect($ldapUser->getAttribute('memberOf', []))
            ->map(fn($dn) => preg_match('/CN=([^,]+)/i', $dn, $m) ? $m[1] : null)
            ->filter()
            ->toArray();

        // Rolle anhand AD-Gruppen bestimmen
        $newRole = null;
        if (env('LDAP_ADMIN_GROUP') && in_array(env('LDAP_ADMIN_GROUP'), $groups, true)) {
            $newRole = 'admin';
        } elseif (env('LDAP_USER_GROUP') && in_array(env('LDAP_USER_GROUP'), $groups, true)) {
            $newRole = 'user';
        }

        // Benutzer erstellen oder aktualisieren
        $user = $existingUser ?? new User();

        $user->fill([
            'username'   => $username,
            'firstname'  => $ldapUser->getFirstAttribute('givenname') ?? '',
            'lastname'   => $ldapUser->getFirstAttribute('sn') ?? '',
            'email'      => $ldapUser->getFirstAttribute('mail') ?? '',
            'auth_type'  => 'ldap',
            'ad_sid'     => $sid,
            'is_enabled' => null,
        ]);

        if ($create) {
            $user->password = ''; // kein Passwort nötig
        }

        $user->save();

        // Rollen synchronisieren (immer!)
        if ($newRole) {
            $user->syncRoles([$newRole]);
        } else {
            $user->syncRoles([]); // falls User keine gültige Gruppe mehr hat
            Logger::warning("Benutzer {$username} hat keine gültige AD-Gruppe mehr, Rollen entfernt");
        }

        return $user;
    }
}
