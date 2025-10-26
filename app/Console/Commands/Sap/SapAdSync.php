<?php

namespace App\Console\Commands\Sap;

use Illuminate\Console\Command;
use Throwable;
use App\Utils\Logging\Logger;
use App\Services\Sap\SapAdSyncService;

class SapAdSync extends Command
{
    protected $signature = "sap:ad-sync";
    protected $description = "Synchronisiert SAP-Daten ins Active Directory.";

    public function handle(SapAdSyncService $service): int
    {
        $this->info("Starte SAP-AD-Sync...");
        
        try 
        {
            $service->sync();
            $this->info("SAP-AD-Sync abgeschlossen");
            
            return Command::SUCCESS;
        } 
        catch (Throwable $e) 
        {
            $this->error("Fehler: " . $e->getMessage());
            
            Logger::db("sap", "error", "SAP-AD-Sync fehlgeschlagen", [
                "user" => auth()->user()->username ?? "cli",
                "exception" => $e->getMessage(),
                "trace" => $e->getTraceAsString(),
            ]);
            
            return Command::FAILURE;
        }
    }
}