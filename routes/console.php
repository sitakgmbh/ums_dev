<?php
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Schedule::command("ad:sync-users")->hourly();
Schedule::command("sap:sync")->dailyAt("02:05");
Schedule::command("mypdgr:sync")->dailyAt("02:50");
Schedule::command("eroeffnungen:assign-license")->dailyAt("02:55");
