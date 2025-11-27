<?php

namespace App\Services\Sap;

use App\Models\Funktion;
use App\Models\Abteilung;
use App\Models\Unternehmenseinheit;
use App\Models\Arbeitsort;
use App\Models\Titel;
use App\Models\Anrede;
use App\Models\Konstellation;
use App\Models\AdUser;
use App\Models\SapExport;
use App\Utils\Logging\Logger;

class SapImportService
{
    // CSV einlesen und die Tabelle sap_export befüllen
    public function import(string $filePath): void
    {
        if (!file_exists($filePath)) 
		{
            throw new \RuntimeException("SAP Export nicht gefunden: {$filePath}");
        }

        $content = file_get_contents($filePath);

        $encoding = mb_detect_encoding($content, [
            "UTF-8", "ISO-8859-1", "Windows-1252", "ASCII"
        ], true);

        if ($encoding && $encoding !== "UTF-8") 
		{
            $content = mb_convert_encoding($content, "UTF-8", $encoding);
        }

        $raw = array_filter(array_map("trim", explode("\n", $content)));

        if (empty($raw)) 
		{
            throw new \RuntimeException("SAP CSV ist leer oder ungültig.");
        }

        $header = array_map("trim", explode(";", array_shift($raw)));
        $rows = [];
		
        foreach ($raw as $line) 
		{
            $values = array_map("trim", explode(";", $line));
			
            if (count($values) !== count($header)) 
			{
                continue;
            }
			
            $rows[] = array_combine($header, $values);
        }

        Logger::debug("SapImportService: Befülle Tabelle sap_export...");

        SapExport::truncate();

        $insertData = array_map(function ($row) {
            $row["d_pernr"] = ltrim(trim($row["d_pernr"] ?? ""), "0");
            $row["created_at"] = now();
            $row["updated_at"] = now();
            return $row;
        }, $rows);

        SapExport::insert($insertData);
    }

	// SAP-Stammdaten aktualisieren (z. B. Arbeitsort)
    public function update(): void
    {
        Logger::debug("SapImportService: Aktualisiere Stammdaten...");

        $rows = SapExport::all();

        $funktionenSeen = [];
        $abteilungenSeen = [];
        $unternehmenseinheitenSeen = [];
        $arbeitsorteSeen = [];
        $titelSeen = [];
        $anredenSeen = [];
        $konstellationenSeen = [];

        foreach ($rows as $row) 
		{
            $funktionName = trim($row["d_0032_batchbez"] ?? "");
            $abteilungName = trim($row["d_abt_txt"] ?? "");
            $ueName = trim($row["d_pers_txt"] ?? "");
            $arbeitsortName = trim($row["d_arbortx"] ?? "");
            $titelName = trim($row["d_titel"] ?? "");
            $anredeName = trim($row["d_anrlt"] ?? "");

            $funktion = $funktionName !== "" ? Funktion::firstOrCreate(["name" => $funktionName]) : null;
            $abteilung = $abteilungName !== "" ? Abteilung::firstOrCreate(["name" => $abteilungName]) : null;
            $ue = $ueName !== "" ? Unternehmenseinheit::firstOrCreate(["name" => $ueName]) : null;
            $arbeitsort = $arbeitsortName !== "" ? Arbeitsort::firstOrCreate(["name" => $arbeitsortName]) : null;
            $titel = $titelName !== "" ? Titel::firstOrCreate(["name" => $titelName]) : null;
            $anrede = $anredeName !== "" ? Anrede::firstOrCreate(["name" => $anredeName]) : null;

            foreach (["Funktion" => $funktion, "Abteilung" => $abteilung, "Unternehmenseinheit" => $ue, "Arbeitsort" => $arbeitsort, "Titel" => $titel, "Anrede" => $anrede,] as $type => $model) 
			{
                if ($model && $model->wasRecentlyCreated) 
				{
                    Logger::db("sap", "info", "{$type} {$model->name} angelegt", [
                        "id" => $model->id,
                        "name" => $model->name,
                    ]);
                }
            }

            if ($funktion) $funktionenSeen[] = $funktion->id;
            if ($abteilung) $abteilungenSeen[] = $abteilung->id;
            if ($ue) $unternehmenseinheitenSeen[] = $ue->id;
            if ($arbeitsort) $arbeitsorteSeen[] = $arbeitsort->id;
            if ($titel) $titelSeen[] = $titel->id;
            if ($anrede) $anredenSeen[] = $anrede->id;

            if ($funktion && $abteilung && $ue && $arbeitsort) 
			{
                $konstellation = Konstellation::firstOrCreate([
                    "funktion_id" => $funktion->id,
                    "abteilung_id" => $abteilung->id,
                    "unternehmenseinheit_id" => $ue->id,
                    "arbeitsort_id" => $arbeitsort->id,
                ]);

                if ($konstellation->wasRecentlyCreated) 
				{
                    Logger::db("sap", "info", "Konstellation angelegt", [
                        "id" => $konstellation->id,
                        "funktion_id" => $funktion->id,
                        "abteilung_id" => $abteilung->id,
                        "unternehmenseinheit_id" => $ue->id,
                        "arbeitsort_id" => $arbeitsort->id,
                    ]);
                }

                $konstellationenSeen[] = $konstellation->id;
            }
        }

        // Deaktivieren nicht mehr vorhandener Einträge
        $this->disableMissing(Funktion::class, $funktionenSeen);
        $this->disableMissing(Abteilung::class, $abteilungenSeen);
        $this->disableMissing(Unternehmenseinheit::class, $unternehmenseinheitenSeen);
        $this->disableMissing(Arbeitsort::class, $arbeitsorteSeen);
        $this->disableMissing(Titel::class, $titelSeen);
        $this->disableMissing(Anrede::class, $anredenSeen);
        $this->disableMissing(Konstellation::class, $konstellationenSeen);

        // Alle gesehenen Einträge wieder aktivieren (falls vorher deaktiviert)
        Funktion::whereIn("id", $funktionenSeen)->where("enabled", false)->update(["enabled" => true]);
        Abteilung::whereIn("id", $abteilungenSeen)->where("enabled", false)->update(["enabled" => true]);
        Unternehmenseinheit::whereIn("id", $unternehmenseinheitenSeen)->where("enabled", false)->update(["enabled" => true]);
        Arbeitsort::whereIn("id", $arbeitsorteSeen)->where("enabled", false)->update(["enabled" => true]);
        Titel::whereIn("id", $titelSeen)->where("enabled", false)->update(["enabled" => true]);
        Anrede::whereIn("id", $anredenSeen)->where("enabled", false)->update(["enabled" => true]);
        Konstellation::whereIn("id", $konstellationenSeen)->where("enabled", false)->update(["enabled" => true]);
		
		Logger::debug("SapImportService: Import abgeschlossen");
    }

	protected function disableMissing(string $modelClass, array $seenIds): void
	{
		$query = $modelClass::whereNotIn("id", $seenIds)->where("enabled", true);

		$query->get()->each(function ($item) use ($modelClass) {
			$item->update(["enabled" => false]);

			$modelName = class_basename($modelClass);

			Logger::db("sap", "info", "{$modelName} {$item->name} deaktiviert", [
				"id"    => $item->id,
				"name"  => $item->name ?? null,
			]);
		});
	}
}