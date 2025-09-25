<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\SapImportService;
use App\Utils\Logging\Logger;
use Throwable;

class ImportSap extends Command
{
    protected $signature = 'sap:import';
    protected $description = 'Importiert den SAP-Export und aktualisiert Stammdaten + Konstellationen.';

    public function handle(SapImportService $service): int
    {
        $this->info('Starte SAP-Import...');

        try {
            $service->import();

            $this->info('SAP-Import erfolgreich abgeschlossen.');

            Logger::info('SAP-Import abgeschlossen', [
                'user' => auth()->user()->username ?? 'cli',
                'time' => now()->toDateTimeString(),
            ]);

            return Command::SUCCESS;
        } catch (Throwable $e) {
            $this->error('Fehler beim Import: ' . $e->getMessage());

            Logger::error('SAP-Import fehlgeschlagen', [
                'user' => auth()->user()->username ?? 'cli',
                'time' => now()->toDateTimeString(),
                'exception' => $e,
            ]);

            return Command::FAILURE;
        }
    }
}
