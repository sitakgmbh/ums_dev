<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use LdapRecord\Models\ActiveDirectory\User as LdapUser;
use App\Services\LdapProvisioningService;

class ApiAuthSwitcher
{
    public function handle($request, Closure $next)
    {
        if (isset($_SERVER['REMOTE_USER'])) {
            return $this->handleSso($request, $next);
        }

        return $this->handleBasicAuth($request, $next);
    }

    private function handleSso($request, Closure $next)
    {
        $rawUser = $_SERVER['REMOTE_USER'];
        $username = explode('\\', $rawUser)[1] ?? $rawUser;

        $user = User::where('username', $username)->first();

        if (! $user) {
            $ldapUser = LdapUser::query()->where('samaccountname', '=', $username)->first();
            if (! $ldapUser) {
                return response()->json(['message' => 'User not found in AD'], 403);
            }

            $provisioner = app(LdapProvisioningService::class);
            $user = $provisioner->provisionOrUpdateUserFromLdap($ldapUser, $username, true);
        }

        Auth::setUser($user);

        return $next($request);
    }

    private function handleBasicAuth($request, Closure $next)
    {
        $username = $request->getUser();
        $password = $request->getPassword();

        if (! $username || ! $password) {
            return response('Unauthorized', 401, ['WWW-Authenticate' => 'Basic']);
        }

        $user = User::where('username', $username)->first();

        if (! $user || ! \Hash::check($password, $user->password)) {
            return response('Unauthorized', 401, ['WWW-Authenticate' => 'Basic']);
        }

        Auth::setUser($user);

        return $next($request);
    }
}
