<?php
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Schedule::command("sap:sync")->hourly();
Schedule::command("mypdgr:sync")->dailyAt("02:00");
Schedule::command("eroeffnungen:assign-license")->dailyAt("02:10");
Schedule::command("ad:sync-users")->dailyAt("02:20");
