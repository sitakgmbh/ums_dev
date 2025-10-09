<?php

namespace App\Providers;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Auth;
use App\Providers\CustomUserProvider;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [];

    public function boot(): void
    {
        $this->registerPolicies();

        Auth::provider('ldap_or_local', function ($app, array $config) 
		{
            return new CustomUserProvider();
        });

        ResetPassword::createUrlUsing(function ($user, string $token) 
		{
            return url(route('password.reset', [
                'token' => $token,
                'email' => $user->email,
            ], false));
        });
    }
}
