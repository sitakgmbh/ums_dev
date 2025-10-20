<?php

namespace App\Providers;

use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use App\Utils\Logging\Logger;
use App\Models\User;
use Illuminate\Database\QueryException;

class LocalUserProvider implements UserProvider
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
        try {
            $user->setRememberToken($token);
            $user->saveQuietly();
        } catch (QueryException $e) {
            if ($e->getCode() === '23000') {
                Logger::debug("Duplicate remember_token für Benutzer-ID {$user->id} ignoriert");
            } else {
                throw $e;
            }
        }
    }

    public function retrieveByCredentials(array $credentials)
    {
        $mode = config('auth.mode', 'local');

        // Im SSO-Modus keine Formularauthentifizierung
        if ($mode === 'sso') {
            Logger::debug('Form-Login verweigert: System im SSO-Modus');
            return null;
        }

        $username = trim($credentials['username'] ?? '');
        Logger::debug("Suche lokalen Benutzer '{$username}'");

        return User::where('username', $username)
            ->where('auth_type', 'local')
            ->first();
    }

    public function validateCredentials(Authenticatable $user, array $credentials)
    {
        $mode = config('auth.mode', 'local');

        if ($mode !== 'local') {
            Logger::debug('validateCredentials ignoriert: System nicht im local-Modus');
            return false;
        }

        $username = $credentials['username'] ?? '';
        $password = $credentials['password'] ?? '';

        if (! $user->is_enabled) {
            $this->logDb('auth', 'warning', "Login Benutzer {$username} fehlgeschlagen: Benutzer deaktiviert", [
                'user_id' => $user->id,
            ]);
            return false;
        }

        $ok = Hash::check($password, $user->getAuthPassword());

        $this->logDb(
            'auth',
            $ok ? 'info' : 'warning',
            $ok
                ? "Login Benutzer {$username} erfolgreich"
                : "Login Benutzer {$username} fehlgeschlagen: Passwort falsch",
            ['user_id' => $user->id]
        );

        // Zusätzlich in Debug-Log für Nachvollziehbarkeit
        Logger::debug($ok
            ? "LocalUserProvider: Authentifizierung für '{$username}' erfolgreich"
            : "LocalUserProvider: Authentifizierung für '{$username}' fehlgeschlagen"
        );

        return $ok;
    }

    public function rehashPasswordIfRequired(Authenticatable $user, array $credentials, bool $force = false): void
    {
        $mode = config('auth.mode', 'local');

        if (
            $mode === 'local' &&
            isset($credentials['password']) &&
            Hash::needsRehash($user->getAuthPassword())
        ) {
            $user->password = Hash::make($credentials['password']);
            $user->save();
            Logger::debug("Passworthash für Benutzer-ID {$user->id} wurde erneuert");
        }
    }

    private function logDb(string $channel, string $level, string $message, array $extra = []): void
    {
        try {
            Logger::db($channel, $level, $message, array_merge([
                'ip' => request()->ip(),
                'userAgent' => request()->userAgent(),
                'guard' => Auth::getDefaultDriver(),
            ], $extra));
        } catch (\Throwable $e) {
            Logger::debug("Fehler beim Schreiben des Login-Logs: {$e->getMessage()}");
        }
    }
}
