<?php

namespace App\Console\Commands\Sap;

use Illuminate\Console\Command;
use Throwable;
use App\Utils\Logging\Logger;
use App\Services\SapImportService;

class ImportSap extends Command
{
    protected $signature = "sap:import";
    protected $description = "Importiert den SAP-Export und aktualisiert Stammdaten sowie Konstellationen.";

    public function handle(SapImportService $service): int
    {
        $this->info("Starte SAP-Import...");

        try 
		{
            $service->import();

            $this->info("SAP-Import erfolgreich abgeschlossen.");

            Logger::info("SAP-Import abgeschlossen", [
                "user" => auth()->user()->username ?? "cli",
            ]);

            return Command::SUCCESS;
			
        } 
		catch (Throwable $e) 
		{
            $this->error("Fehler: " . $e->getMessage());

			Logger::db('sap', 'error', "SAP-Import fehlgeschlagen", [
                "user" => auth()->user()->username ?? "cli",
                "exception" => $e,
			]);

            return Command::FAILURE;
        }
    }
}
