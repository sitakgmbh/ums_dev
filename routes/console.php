<?php
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Schedule::command("ad:sync-users")->hourly();

Schedule::command("eroeffnungen:assign-license")->dailyAt("02:00");
Schedule::command("sap:import")->dailyAt("03:00");
Schedule::command("sap:sync-ad")->dailyAt("03:05");
