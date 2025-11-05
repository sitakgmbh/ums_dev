<?php
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Schedule::command("ad:sync-users")->hourly();
Schedule::command("sap:sync")->dailyAt("01:05");
Schedule::command("mypdgr:sync")->dailyAt("01:10");
Schedule::command("eroeffnungen:assign-license")->dailyAt("01:15");
