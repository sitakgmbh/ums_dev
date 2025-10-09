<?php

use Illuminate\Support\Facades\Route;
use App\Livewire\Pages\Auth\Login;
use App\Livewire\Pages\Auth\ForgotPassword;
use App\Livewire\Pages\Auth\ResetPassword;
use App\Livewire\Pages\Dashboard;
use App\Livewire\Pages\Profile\Edit as ProfileEdit;
use App\Livewire\Pages\Admin\Settings;
use App\Livewire\Pages\Admin\Logfiles;
use App\Livewire\Pages\Admin\Logs;
use App\Livewire\Pages\Admin\Users\Index as UsersIndex;
use App\Livewire\Pages\Admin\Users\Create as UsersCreate;
use App\Livewire\Pages\Admin\Users\Edit as UsersEdit;
use App\Livewire\Pages\Admin\AdUsers\Index as AdUsersIndex;
use App\Livewire\Pages\Admin\AdUsers\Show as AdUsersShow;
use App\Livewire\Pages\Admin\MailTest;
use App\Livewire\Pages\Admin\Tools\Index as ToolsIndex;
use App\Livewire\Pages\Admin\Tools\TaskScheduler;
use App\Livewire\Pages\Admin\Tools\MailTest as ToolsMailTest;
use App\Livewire\Pages\Admin\AdminDashboard;

use Illuminate\Support\Facades\Auth;

Route::get('/sso-test', function () {
    $_SERVER['REMOTE_USER'] = 'BSM\luspet';

    $credentials = ['username' => $_SERVER['REMOTE_USER']];
    if (Auth::attempt($credentials)) {
        return redirect()->route('dashboard');
    }

    return redirect()->route('login')->withErrors(['msg' => 'SSO-Test fehlgeschlagen']);
});

Route::redirect('/', '/dashboard');
Route::get('/login', Login::class)->name('login');
Route::get('/forgot-password', ForgotPassword::class)->name('password.request');
Route::get('/reset-password/{token}', ResetPassword::class)->name('password.reset');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', Dashboard::class)->name('dashboard');
    Route::get('/profile/edit', ProfileEdit::class)->name('profile.edit');
	Route::get('/profile/settings', \App\Livewire\Pages\Profile\UserSettings::class)->middleware('auth')->name('profile.settings');

	Route::prefix('eroeffnungen')->name('eroeffnungen.')->group(function () {
		Route::get('/', \App\Livewire\Pages\Eroeffnungen\Index::class)->name('index');
		Route::get('/create', \App\Livewire\Pages\Eroeffnungen\Create::class)->name('create');
		Route::get('/{eroeffnung}', \App\Livewire\Pages\Eroeffnungen\Show::class)->name('show');
		Route::get('/{eroeffnung}/edit', \App\Livewire\Pages\Eroeffnungen\Edit::class)->name('edit');
	});

	Route::prefix('mutationen')->name('mutationen.')->group(function () {
		Route::get('/', \App\Livewire\Pages\Mutationen\Index::class)->name('index');
		Route::get('/create', \App\Livewire\Pages\Mutationen\Create::class)->name('create');
		Route::get('/{mutation}', \App\Livewire\Pages\Mutationen\Show::class)->name('show');
		Route::get('/{mutation}/edit', \App\Livewire\Pages\Mutationen\Edit::class)->name('edit');
	});

    // >>> Admin Bereich <<<
    Route::prefix('admin')->middleware('role:admin')->group(function () {
        // Admin Dashboard
        Route::get('/', AdminDashboard::class)->name('admin.dashboard');

        Route::get('/settings', Settings::class)->name('admin.settings');

        // Users
        Route::prefix('users')->name('admin.users.')->group(function () {
            Route::get('/', UsersIndex::class)->name('index');
            Route::get('/create', UsersCreate::class)->name('create');
            Route::get('/{user}/edit', UsersEdit::class)->name('edit');
        });

        // AD Users
        Route::prefix('ad-users')->name('admin.ad-users.')->group(function () {
            Route::get('/', AdUsersIndex::class)->name('index');
            Route::get('/{adUser}', AdUsersShow::class)->name('show');
        });

        // Logs
        Route::prefix('logs')->name('admin.logs.')->group(function () {
            Route::get('/', Logs::class)->name('index');
        });

        // Logfiles
        Route::prefix('logfiles')->name('admin.logfiles.')->group(function () {
            Route::get('/', Logfiles::class)->name('index');
        });

        // Admin Tools
        Route::prefix('tools')->name('admin.tools.')->group(function () {
            Route::get('/', ToolsIndex::class)->name('index');
            Route::get('/task-scheduler', TaskScheduler::class)->name('task-scheduler');
            Route::get('/mail-test', ToolsMailTest::class)->name('mail-test');
        });

		Route::prefix('eroeffnungen')->name('admin.eroeffnungen.')->group(function () {
			Route::get('/', \App\Livewire\Pages\Admin\Eroeffnungen\Index::class)->name('index');
			Route::get('/{eroeffnung}/verarbeitung', \App\Livewire\Pages\Admin\Eroeffnungen\Verarbeitung::class)->name('verarbeitung');
		});

		Route::prefix('mutationen')->name('admin.mutationen.')->group(function () {
			Route::get('/', \App\Livewire\Pages\Admin\Mutationen\Index::class)->name('index');
			Route::get('/{mutation}/verarbeitung', \App\Livewire\Pages\Admin\Mutationen\Verarbeitung::class)->name('verarbeitung');
		});

		Route::prefix('austritte')->name('admin.austritte.')->group(function () {
			Route::get('/', \App\Livewire\Pages\Admin\Austritte\Index::class)->name('index');
			Route::get('/{austritt}/verarbeitung', \App\Livewire\Pages\Admin\Austritte\Verarbeitung::class)->name('verarbeitung');
		});
		
		Route::get('/changelog', \App\Livewire\Pages\Admin\Changelog::class)->name('admin.changelog');
		Route::get('/server-info', \App\Livewire\Pages\Admin\ServerInfo::class)->name('admin.server-info');
    });
});
