<?php

namespace App\Console\Commands\ActiveDirectory;

use Illuminate\Console\Command;
use Throwable;
use App\Utils\Logging\Logger;
use App\Services\ActiveDirectory\UserSyncService;

class SyncUsers extends Command
{
    protected $signature = "ad:sync-users";
    protected $description = "Synchronisiert alle AD-Benutzer in die Datenbank.";

    protected UserSyncService $syncService;

    public function __construct(UserSyncService $syncService)
    {
        parent::__construct();
        $this->syncService = $syncService;
    }

    public function handle(): int
    {
        $this->info("Starte AD-Sync...");

        try 
		{
            $this->syncService->sync();

            $this->info("AD-Sync erfolgreich abgeschlossen.");
            
            Logger::info("AD-Sync abgeschlossen", [
                "user" => auth()->user()->username ?? "cli"
            ]);

            return Command::SUCCESS;

        } 
		catch (Throwable $e) 
		{
            $this->error("Fehler: " . $e->getMessage());

            Logger::db('ad', 'error', "AD-Sync fehlgeschlagen", [
                "user" => auth()->user()->username ?? "cli",
                "exception" => $e,
            ]);

            return Command::FAILURE;
        }
    }
}
