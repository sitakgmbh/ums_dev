<?php
namespace App\Console\Commands\Sap;

use Illuminate\Console\Command;
use Throwable;
use App\Utils\Logging\Logger;
use App\Utils\SmbHelper;
use App\Services\Sap\SapImportService;
use App\Services\Sap\SapAdSyncService;

class SapSync extends Command
{
    protected $signature = "sap:sync";
    protected $description = "Importiert den SAP-Export, aktualisiert Stammdaten inkl. Konstellationen und synchronisiert die Änderungen ins AD. Führt abschliessend einen Personalnummerabgleich durch.";
    
    public function handle(SapImportService $importService, SapAdSyncService $adSyncService): int
    {
        $localCsvPath = null;
        
        try 
        {
            $localCsvPath = $this->downloadLatestCsvFromSmb();
            
            $this->info("Starte SAP-Import...");
            $importService->import($localCsvPath);
            $this->info("SAP-Import abgeschlossen");
            
            Logger::info("SAP-Import abgeschlossen", [
                "user" => auth()->user()->username ?? "cli",
                "file" => basename($localCsvPath),
            ]);
            
        } 
        catch (Throwable $e) 
        {
            $this->error("Fehler beim Import: " . $e->getMessage());
            
            Logger::db("sap", "error", "SAP-Import fehlgeschlagen", [
                "user" => auth()->user()->username ?? "cli",
                "exception" => $e,
                "file" => $localCsvPath ?? null,
            ]);
            
            if ($localCsvPath) 
            {
                $this->warn("CSV-Datei wurde zur Fehleranalyse behalten: {$localCsvPath}");
            }
            
            return Command::FAILURE;
        }
        
        $this->info("Starte SAP-AD-Sync...");
        
        try 
        {
            $adSyncService->sync($localCsvPath);
            $this->info("SAP-AD-Sync abgeschlossen");
            
            if ($localCsvPath && file_exists($localCsvPath)) 
            {
                @unlink($localCsvPath);
                $this->info("Temporäre CSV-Datei gelöscht");
            }
            
            return Command::SUCCESS;
        } 
        catch (Throwable $e) 
        {
            $this->error("Fehler beim AD-Sync: " . $e->getMessage());
            
            Logger::db("sap", "error", "SAP-AD-Sync fehlgeschlagen", [
                "user" => auth()->user()->username ?? "cli",
                "exception" => $e->getMessage(),
                "trace" => $e->getTraceAsString(),
            ]);
            
            if ($localCsvPath) 
            {
                $this->warn("CSV-Datei wurde zur Fehleranalyse behalten: {$localCsvPath}");
            }
            
            return Command::FAILURE;
        }
    }
    
    protected function downloadLatestCsvFromSmb(): string
    {
        $smbSharePath = config("ums.sap.export_path");
        
        $this->info("Suche neuste CSV-Datei auf SMB-Share {$smbSharePath}");
        
        $items = SmbHelper::listDirectory($smbSharePath, false);
        
        if ($items === false || empty($items)) 
        {
            throw new \RuntimeException("Keine Dateien im SMB-Share gefunden.");
        }
        
        $csvFiles = array_filter($items, function ($item) {
            return $item["type"] === "file" 
                && strtolower(pathinfo($item["name"], PATHINFO_EXTENSION)) === "csv";
        });
        
        if (empty($csvFiles)) 
        {
            throw new \RuntimeException("Keine CSV-Dateien im SMB-Share gefunden.");
        }
        
        // Nach Änderungsdatum sortieren (neuste zuerst)
        usort($csvFiles, function ($a, $b) {
            $timeA = @filemtime($a["path"]);
            $timeB = @filemtime($b["path"]);
            return $timeB <=> $timeA;
        });
        
        $latestCsv = $csvFiles[0];
        $timestamp = date("Y-m-d_H-i-s", @filemtime($latestCsv["path"]));
        
        $this->info("Datei: {$latestCsv['name']} ({$timestamp})");
        
        $localPath = storage_path("app/private/sap_import_{$timestamp}.csv");
        
        $this->info("Kopiere Datei...");
		
        if (!@copy($latestCsv["path"], $localPath)) 
        {
            throw new \RuntimeException("Fehler beim Kopieren der Datei von {$latestCsv['path']} nach {$localPath}");
        }
        
        $this->info("Datei erfolgreich kopiert nach: {$localPath}");
        
        Logger::Debug("CSV vom SMB-Share heruntergeladen", [
            "source" => $latestCsv["path"],
            "destination" => $localPath,
            "size" => $latestCsv["size"],
        ]);
        
        return $localPath;
    }
}