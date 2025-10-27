<?php
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Schedule::command("ad:sync-users")->hourly();
Schedule::command("sap:sync")->dailyAt("02:00");
Schedule::command("eroeffnungen:assign-license")->dailyAt("03:00");
