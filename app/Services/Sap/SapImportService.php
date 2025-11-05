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
    protected string $actor;

    public function __construct()
    {
        $this->actor = auth()->user()->username ?? "cli";
    }

    public function import(string $filePath): void
    {
        if (!file_exists($filePath)) 
        {
            throw new \RuntimeException("SAP Export nicht gefunden: {$filePath}");
        }

		$content = file_get_contents($filePath);

		// Encoding erkennen und nach UTF-8 konvertieren
		$encoding = mb_detect_encoding($content, ["UTF-8", "ISO-8859-1", "Windows-1252", "ASCII"], true);

		if ($encoding && $encoding !== "UTF-8") 
		{
			$content = mb_convert_encoding($content, "UTF-8", $encoding);
		}

		$raw = explode("\n", $content);
		$raw = array_map("trim", $raw);
		$raw = array_filter($raw);

		$lines = $raw;
		$header = array_map("trim", explode(";", array_shift($lines)));
		$rows = [];

        foreach ($lines as $line) 
		{
            $values = array_map("trim", explode(";", $line));
			
            if (count($values) !== count($header)) 
			{
                continue;
            }
			
            $rows[] = array_combine($header, $values);
        }

		// SapExport Tabelle befüllen
		Logger::debug("SapImportService: Tabelle sap_export befüllen");
		SapExport::truncate();
		
		$insertData = array_map(function($row) {
			// Voranstehende Nullen bei d_pernr entfernen
			if (isset($row['d_pernr'])) {
				$row['d_pernr'] = ltrim(trim($row['d_pernr']), "0");
			}
			
			$row['created_at'] = now();
			$row['updated_at'] = now();
			return $row;
		}, $rows);
		
		SapExport::insert($insertData);
		
		// Verknüpfung zu ad_users erstellen
		Logger::debug("SapImportService: Verknüpfungen sap_export zu ad_users erstellen");
		
		// Alle AdUsers mit initials auf einmal laden für bessere Performance
		$adUsersMap = AdUser::whereNotNull('initials')
			->where('is_existing', true)
			->pluck('id', 'initials')
			->toArray();
		
		// Bulk Update für SapExport
		$sapUpdateData = [];
		foreach ($insertData as $row) 
		{
			$personalnummer = $row["d_pernr"] ?? "";
			if (empty($personalnummer)) continue;
			
			if (isset($adUsersMap[$personalnummer])) 
			{
				$sapUpdateData[$personalnummer] = $adUsersMap[$personalnummer];
			}
		}
		
		// Bulk Update ausführen
		foreach ($sapUpdateData as $pernr => $adUserId) {
			SapExport::where('d_pernr', $pernr)->update(['ad_user_id' => $adUserId]);
		}

        // Für spätere Deaktivierung
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

            // Stammdaten firstOrCreate
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
                        "actor" => $this->actor,
                    ]);
                }
            }

            if ($funktion) $funktionenSeen[] = $funktion->id;
            if ($abteilung) $abteilungenSeen[] = $abteilung->id;
            if ($ue) $unternehmenseinheitenSeen[] = $ue->id;
            if ($arbeitsort) $arbeitsorteSeen[] = $arbeitsort->id;
            if ($titel) $titelSeen[] = $titel->id;
            if ($anrede) $anredenSeen[] = $anrede->id;

            // Konstellation nur wenn alle 4 vorhanden
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
                        "actor" => $this->actor,
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

		// AD User Abgleich - OPTIMIERT: Alle Lookups VOR der Schleife
		Logger::debug("SapImportService: AD User Abgleich - Lade alle Maps");
		
		$funktionenMap = Funktion::pluck('id', 'name')->toArray();
		$abteilungenMap = Abteilung::pluck('id', 'name')->toArray();
		$ueMap = Unternehmenseinheit::pluck('id', 'name')->toArray();
		$arbeitsorteMap = Arbeitsort::pluck('id', 'name')->toArray();
		$titelMap = Titel::pluck('id', 'name')->toArray();
		$anredenMap = Anrede::pluck('id', 'name')->toArray();
		
		Logger::debug("SapImportService: AD User Abgleich - Verarbeite Rows");
		
		// Alle Updates sammeln
		$adUserUpdates = [];
		
		foreach ($rows as $row) 
		{
			$funktionName   = trim($row["d_0032_batchbez"] ?? "");
			$abteilungName  = trim($row["d_abt_txt"] ?? "");
			$ueName         = trim($row["d_pers_txt"] ?? "");
			$arbeitsortName = trim($row["d_arbortx"] ?? "");
			$titelName      = trim($row["d_titel"] ?? "");
			$anredeName     = trim($row["d_anrlt"] ?? "");
			$personalnummer = ltrim(trim($row["d_pernr"] ?? ""), "0");

			// Lookup ohne DB-Query!
			$funktionId   = $funktionName ? ($funktionenMap[$funktionName] ?? null) : null;
			$abteilungId  = $abteilungName ? ($abteilungenMap[$abteilungName] ?? null) : null;
			$ueId         = $ueName ? ($ueMap[$ueName] ?? null) : null;
			$arbeitsortId = $arbeitsortName ? ($arbeitsorteMap[$arbeitsortName] ?? null) : null;
			$titelId      = $titelName ? ($titelMap[$titelName] ?? null) : null;
			$anredeId     = $anredeName ? ($anredenMap[$anredeName] ?? null) : null;

			// Lookup ohne DB-Query!
			$adUserId = $personalnummer ? ($adUsersMap[$personalnummer] ?? null) : null;

			if ($adUserId) 
			{
				$adUserUpdates[$adUserId] = [
					"funktion_id"            => $funktionId,
					"abteilung_id"           => $abteilungId,
					"unternehmenseinheit_id" => $ueId,
					"arbeitsort_id"          => $arbeitsortId,
					"titel_id"               => $titelId,
					"anrede_id"              => $anredeId,
					"is_existing"            => true,
				];
			}
		}
		
		// Bulk Update ausführen
		Logger::debug("SapImportService: AD User Abgleich - Update " . count($adUserUpdates) . " Benutzer");
		
		foreach ($adUserUpdates as $userId => $fields) {
			AdUser::where('id', $userId)->update($fields);
		}
		
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
				"actor" => $this->actor,
			]);
		});
	}

	protected function resolveName(string $field, int $id): array
	{
		return match ($field) {
			"funktion_id" => ["id" => $id, "name" => Funktion::find($id)?->name],
			"abteilung_id" => ["id" => $id, "name" => Abteilung::find($id)?->name],
			"unternehmenseinheit_id" => ["id" => $id, "name" => Unternehmenseinheit::find($id)?->name],
			"arbeitsort_id" => ["id" => $id, "name" => Arbeitsort::find($id)?->name],
			"titel_id" => ["id" => $id, "name" => Titel::find($id)?->name],
			"anrede_id" => ["id" => $id, "name" => Anrede::find($id)?->name],
			default => ["id" => $id, "name" => null],
		};
	}
}