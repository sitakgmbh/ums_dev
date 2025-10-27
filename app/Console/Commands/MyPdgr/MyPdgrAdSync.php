<?php
namespace App\Console\Commands\MyPdgr;

use Illuminate\Console\Command;
use Throwable;
use App\Utils\Logging\Logger;
use App\Utils\SmbHelper;
use App\Services\MyPdgr\MyPdgrAdSyncService;

class MyPdgrAdSync extends Command
{
    protected $signature = "mypdgr:sync";
    protected $description = "Synchronisiert Addressdaten aus MyPDGR ins AD.";
    
    public function handle(MyPdgrAdSyncService $syncService): int
    {       
        try 
        {            
            $this->info("Starte MyPDGR-Sync...");
            $syncService->sync();
            $this->info("MyPDGR-Sync abgeschlossen");
        } 
        catch (Throwable $e) 
        {
            $this->error("Fehler: " . $e->getMessage());
            
            Logger::db("mypdgr", "error", "MyPDGR-Sync fehlgeschlagen", [
                "user" => auth()->user()->username ?? "cli",
                "exception" => $e,
                "file" => $localCsvPath ?? null,
            ]);
            
            return Command::FAILURE;
        }
    }
}