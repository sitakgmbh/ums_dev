<?php
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Schedule::command("ad:sync-users")->hourly();
Schedule::command("sap:sync")->dailyAt("00:02");
Schedule::command("mypdgr:sync")->dailyAt("00:04");
Schedule::command("check:sap-ad-mappings")->dailyAt("00:06");
Schedule::command("check:sap-ad-excludes")->dailyAt("00:08");
Schedule::command("check:employment-starts")->dailyAt("00:10");
