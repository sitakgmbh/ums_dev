<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Auth;
use App\Providers\CustomUserProvider;
use App\Observers\UserObserver;
use App\Observers\AdUserObserver;
use App\Observers\EroeffnungObserver;
use App\Observers\MutationObserver;
use App\Observers\AustrittObserver;
use App\Models\User;
use App\Models\AdUser;
use App\Models\Eroeffnung;
use App\Models\Mutation;
use App\Models\Austritt;

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
		Auth::provider('ldap_or_local', function ($app, array $config) 
		{
			return new CustomUserProvider();
		});

		User::observe(UserObserver::class);
		AdUser::observe(AdUserObserver::class);
		Eroeffnung::observe(EroeffnungObserver::class);
		Mutation::observe(MutationObserver::class);
		Austritt::observe(AustrittObserver::class);
	}
}
