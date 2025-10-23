<?php
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Schedule::command("ad:sync-users")->hourly();
Schedule::command('eroeffnungen:assign-license')->dailyAt('01:00');
