<?php
namespace App\Console\Commands\Sap;

use Illuminate\Console\Command;
use Throwable;
use App\Utils\Logging\Logger;
use App\Utils\SmbHelper;

use App\Services\Sap\SapImportService;
use App\Services\Sap\SapAdPersNrAbgleichService;
use App\Services\Sap\SapAdMappingService;
use App\Services\Sap\SapAdSyncService;
use App\Services\ActiveDirectory\UserSyncService;

class SapSync extends Command
{
    protected $signature = "sap:sync";
    protected $description = "Importiert den SAP-Export, führt einen Personalnummerabgleich durch, aktualisiert Stammdaten und synchronisiert die Änderungen ins AD.";

    public function handle(
        SapImportService $importService,
		SapAdPersNrAbgleichService $persNrService,
        SapAdMappingService $mappingService,
        SapAdSyncService $adSyncService,
        UserSyncService $userSync
    ): int
    {
        try
        {
            // CSV herunterladen
            $localCsvPath = $this->downloadLatestCsvFromSmb();

            // SAP-Export in DB speichern
            $this->info("Starte SAP-Import...");
            $rows = $importService->import($localCsvPath);
            $this->info("SAP-Import abgeschlossen.");

            // Personalnummer-Abgleich
            $this->info("Starte Personalnummer-Abgleich...");
            $persNrService->syncMissingInitials();
            $this->info("Personalnummer-Abgleich abgeschlossen.");
			
			// SAP-Stammdaten aktualisieren
            $this->info("Starte Aktualisierung SAP-Stammdaten...");
            $rows = $importService->update();
            $this->info("Aktualisierung SAP-Stammdaten abgeschlossen");

            // SAP-AD-Mapping
            $this->info("Starte SAP-AD-Mapping...");
            $mappingService->map();
            $this->info("SAP→AD Mapping abgeschlossen.");

            // Sync SAP → AD
            $this->info("Starte SAP → AD Sync...");
            $adSyncService->sync();
            $this->info("SAP→AD Sync abgeschlossen.");

            // Sync AD → DB
            $this->info("Starte AD → DB Sync...");
            $userSync->sync();
            $this->info("AD→DB Sync abgeschlossen.");

            $this->info("SAP Sync komplett abgeschlossen.");

            return Command::SUCCESS;
        } 
		catch (Throwable $e) 
		{
            $this->error("Fehler in SAP-Sync: " . $e->getMessage());

            Logger::db("sap", "error", "SAP-Sync fehlgeschlagen", [
                "actor" => auth()->user()->username ?? "cli",
                "error" => $e->getMessage(),
                "trace" => $e->getTraceAsString(),
            ]);

            return Command::FAILURE;
        }

        if ($localCsvPath && file_exists($localCsvPath)) 
		{
            @unlink($localCsvPath);
            $this->info("Temporäre CSV-Datei gelöscht.");
        }

        $this->info("SAP-Sync erfolgreich abgeschlossen.");
        return Command::SUCCESS;
    }

    protected function downloadLatestCsvFromSmb(): string
    {
        $smbSharePath = config("ums.sap.export_path");

        $this->info("Suche neueste CSV auf SMB-Share {$smbSharePath}");

        $items = SmbHelper::listDirectory($smbSharePath, false);

        if (!$items) 
		{
            throw new \RuntimeException("Keine Dateien im SMB-Share gefunden.");
        }

        $csvFiles = array_filter($items, fn($item) =>
            $item["type"] === "file" &&
            strtolower(pathinfo($item["name"], PATHINFO_EXTENSION)) === "csv"
        );

        if (empty($csvFiles)) 
		{
            throw new \RuntimeException("Keine CSV-Dateien im SMB-Share gefunden.");
        }

        usort($csvFiles, fn($a, $b) =>
            (@filemtime($b["path"])) <=> (@filemtime($a["path"]))
        );

        $latestCsv = $csvFiles[0];
        $timestamp = date("Y-m-d_H-i-s", @filemtime($latestCsv["path"]));

        $this->info("Gefunden: {$latestCsv['name']} ({$timestamp})");

        $localPath = storage_path("app/private/sap_import_{$timestamp}.csv");

        $this->info("Kopiere Datei...");

        if (!@copy($latestCsv["path"], $localPath)) 
		{
            throw new \RuntimeException("Fehler beim Kopieren von {$latestCsv['path']} nach {$localPath}");
        }

        $this->info("CSV erfolgreich kopiert.");

        Logger::debug("CSV vom SMB heruntergeladen", [
            "source"      => $latestCsv["path"],
            "destination" => $localPath,
            "size"        => $latestCsv["size"],
        ]);

        return $localPath;
    }
}
