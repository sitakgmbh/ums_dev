<?php
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Schedule::command("ad:sync-users")->hourly();
Schedule::command("sap:sync")->dailyAt("23:05");
Schedule::command("check:sap-ad")->dailyAt("23:10");
Schedule::command("mypdgr:sync")->dailyAt("23:15");
Schedule::command("eroeffnungen:assign-license")->dailyAt("23:20");
