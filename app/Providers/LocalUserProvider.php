<?php

namespace App\Providers;

use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Hash;
use App\Utils\Logging\Logger;
use App\Models\User;

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
        $user->setRememberToken($token);
        $user->save();
    }

    public function retrieveByCredentials(array $credentials)
    {
        // Wenn im SSO-Modus, keine Formular-Authentifizierung zulassen
        if (env('AUTH_MODE') === 'sso') {
            Logger::debug('Form-Login verweigert: System im SSO-Modus');
            return null;
        }

        $username = $credentials['username'] ?? '';

        Logger::debug("Suche lokalen Benutzer {$username}");

        // Nur lokale Benutzer zulassen
        return User::where('username', $username)
            ->where('auth_type', 'local')
            ->first();
    }

    public function validateCredentials(Authenticatable $user, array $credentials)
    {
        if (env('AUTH_MODE') !== 'local') {
            Logger::debug('validateCredentials ignoriert: System nicht im local-Modus');
            return false;
        }

        $username = $credentials['username'] ?? '';
        $password = $credentials['password'] ?? '';

        if (! $user->is_enabled) {
            $this->logDb('auth', 'warning', "Login Benutzer {$username} fehlgeschlagen: deaktiviert");
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

        return $ok;
    }

    public function rehashPasswordIfRequired(Authenticatable $user, array $credentials, bool $force = false): void
    {
        if (
            env('AUTH_MODE') === 'local'
            && isset($credentials['password'])
            && Hash::needsRehash($user->getAuthPassword())
        ) {
            $user->password = Hash::make($credentials['password']);
            $user->save();
        }
    }

    private function logDb(string $channel, string $level, string $message, array $extra = []): void
    {
        Logger::db($channel, $level, $message, array_merge([
            'ip' => request()->ip(),
            'userAgent' => request()->userAgent(),
        ], $extra));
    }
}
