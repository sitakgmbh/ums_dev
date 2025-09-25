<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Auth;
use App\Providers\LoginServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
	public function boot(): void
	{
		// Custom Auth Provider registrieren
		Auth::provider('ldap_or_local', function ($app, array $config) {
			return new LoginServiceProvider();
		});
	}
}
