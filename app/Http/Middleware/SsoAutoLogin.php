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
        $mode = config("auth.mode", "local");

        if ($mode !== "sso") 
		{
            return $next($request);
        }

        $remoteUser = $_SERVER["REMOTE_USER"] ?? null;

        if (! $remoteUser) 
		{
            Logger::debug("SSO-AutoLogin: kein REMOTE_USER vorhanden");
            return $next($request);
        }

        $username = Str::of($remoteUser)
            ->after("\\")
            ->before("@")
            ->lower()
            ->toString();

        Logger::debug("SSO-AutoLogin gestartet für Benutzer '{$username}'");

        if (Auth::check() && Auth::user()->username === $username) 
		{
            Logger::debug("SSO-AutoLogin: Benutzer '{$username}' bereits eingeloggt, übersprungen");
            return $next($request);
        }

        $service = app(LdapProvisioningService::class);
		
        $ldapUser = LdapUser::query()
            ->where("samaccountname", "=", $username)
            ->first();

        $user = User::where("username", $username)->first();

        if (! $user && $ldapUser) 
		{
            $user = $service->provisionOrUpdateUserFromLdap($ldapUser, $username, true);
            Logger::debug("SSO-AutoLogin: Benutzer '{$username}' provisioniert (ID {$user->id})");
        } 
		elseif ($user && $ldapUser) 
		{
            $service->provisionOrUpdateUserFromLdap($ldapUser, $username, false, $user);
            Logger::debug("SSO-AutoLogin: Benutzer '{$username}' aktualisiert (ID {$user->id})");
        }

        if ($user) 
		{
            Auth::guard("sso")->login($user, true);
            session()->regenerate();

            $this->logDb("auth", "info", "Login Benutzer '{$username}' erfolgreich", [
                "user_id" => $user->id,
            ]);

            Logger::debug("SSO-Login erfolgreich für '{$username}'");
			
        } 
		else 
		{
            $this->logDb("auth", "warning", "Login Benutzer '{$username}' fehlgeschlagen: Benutzer existiert nicht");
            Logger::debug("SSO-Login fehlgeschlagen für '{$username}'");
        }

        return $next($request);
    }

    private function logDb(string $channel, string $level, string $message, array $extra = []): void
    {
        try 
		{
            Logger::db($channel, $level, $message, array_merge([
                "ip" => request()->ip(),
                "userAgent" => request()->userAgent(),
                "guard" => "sso",
            ], $extra));
        } 
		catch (\Throwable $e) 
		{
            Logger::debug("Fehler beim Schreiben des Logs: {$e->getMessage()}");
        }
    }
}
