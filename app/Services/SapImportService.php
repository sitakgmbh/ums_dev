<?php

namespace App\Services;

use App\Models\Funktion;
use App\Models\Abteilung;
use App\Models\Unternehmenseinheit;
use App\Models\Arbeitsort;
use App\Models\Titel;
use App\Models\Anrede;
use App\Models\Konstellation;
use App\Models\AdUser;
use App\Utils\Logging\Logger;

class SapImportService
{
    protected string $filePath;
    protected string $actor;

    public function __construct()
    {
        $this->filePath = storage_path('app/private/export.csv');
        $this->actor = auth()->user()->username ?? 'cli';
    }

    public function import(): void
    {
        if (!file_exists($this->filePath)) {
            throw new \RuntimeException("SAP Export nicht gefunden: {$this->filePath}");
        }

        $raw = file($this->filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        // alle Zeilen nach UTF-8 konvertieren
        $lines = array_map(fn($line) => mb_convert_encoding($line, 'UTF-8', 'Windows-1252'), $raw);

        // Header
        $header = array_map('trim', explode(';', array_shift($lines)));

        $rows = [];
        foreach ($lines as $line) {
            $values = array_map('trim', explode(';', $line));
            if (count($values) !== count($header)) {
                continue;
            }
            $rows[] = array_combine($header, $values);
        }

        // Sets für spätere Deaktivierung
        $funktionenSeen = [];
        $abteilungenSeen = [];
        $unternehmenseinheitenSeen = [];
        $arbeitsorteSeen = [];
        $titelSeen = [];
        $anredenSeen = [];
        $konstellationenSeen = [];

        foreach ($rows as $row) {
            $funktionName = trim($row['d_0032_batchbez'] ?? '');
            $abteilungName = trim($row['d_abt_txt'] ?? '');
            $ueName = trim($row['d_pers_txt'] ?? '');
            $arbeitsortName = trim($row['d_arbortx'] ?? '');
            $titelName = trim($row['d_titel'] ?? '');
            $anredeName = trim($row['d_anrlt'] ?? '');

            // Stammdaten firstOrCreate
            $funktion = $funktionName !== '' ? Funktion::firstOrCreate(['name' => $funktionName]) : null;
            $abteilung = $abteilungName !== '' ? Abteilung::firstOrCreate(['name' => $abteilungName]) : null;
            $ue = $ueName !== '' ? Unternehmenseinheit::firstOrCreate(['name' => $ueName]) : null;
            $arbeitsort = $arbeitsortName !== '' ? Arbeitsort::firstOrCreate(['name' => $arbeitsortName]) : null;
            $titel = $titelName !== '' ? Titel::firstOrCreate(['name' => $titelName]) : null;
            $anrede = $anredeName !== '' ? Anrede::firstOrCreate(['name' => $anredeName]) : null;

            foreach ([
                'Funktion' => $funktion,
                'Abteilung' => $abteilung,
                'Unternehmenseinheit' => $ue,
                'Arbeitsort' => $arbeitsort,
                'Titel' => $titel,
                'Anrede' => $anrede,
            ] as $type => $model) {
                if ($model && $model->wasRecentlyCreated) {
                    Logger::db('sap', 'info', "$type neu angelegt", [
                        'id' => $model->id,
                        'name' => $model->name,
                        'actor' => $this->actor,
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
            if ($funktion && $abteilung && $ue && $arbeitsort) {
                $konstellation = Konstellation::firstOrCreate([
                    'funktion_id' => $funktion->id,
                    'abteilung_id' => $abteilung->id,
                    'unternehmenseinheit_id' => $ue->id,
                    'arbeitsort_id' => $arbeitsort->id,
                ]);

                if ($konstellation->wasRecentlyCreated) {
                    Logger::db('sap', 'info', "Konstellation neu angelegt", [
                        'id' => $konstellation->id,
                        'funktion_id' => $funktion->id,
                        'abteilung_id' => $abteilung->id,
                        'unternehmenseinheit_id' => $ue->id,
                        'arbeitsort_id' => $arbeitsort->id,
                        'actor' => $this->actor,
                    ]);
                }

                $konstellationenSeen[] = $konstellation->id;
            }
        }

        // Deaktivieren nicht mehr vorhandener Eintraege
        $this->disableMissing(Funktion::class, $funktionenSeen);
        $this->disableMissing(Abteilung::class, $abteilungenSeen);
        $this->disableMissing(Unternehmenseinheit::class, $unternehmenseinheitenSeen);
        $this->disableMissing(Arbeitsort::class, $arbeitsorteSeen);
        $this->disableMissing(Titel::class, $titelSeen);
        $this->disableMissing(Anrede::class, $anredenSeen);
        $this->disableMissing(Konstellation::class, $konstellationenSeen);

        // Alle gesehenen wieder aktiv setzen (falls vorher deaktiviert)
        Funktion::whereIn('id', $funktionenSeen)->where('enabled', false)->update(['enabled' => true]);
        Abteilung::whereIn('id', $abteilungenSeen)->where('enabled', false)->update(['enabled' => true]);
        Unternehmenseinheit::whereIn('id', $unternehmenseinheitenSeen)->where('enabled', false)->update(['enabled' => true]);
        Arbeitsort::whereIn('id', $arbeitsorteSeen)->where('enabled', false)->update(['enabled' => true]);
        Titel::whereIn('id', $titelSeen)->where('enabled', false)->update(['enabled' => true]);
        Anrede::whereIn('id', $anredenSeen)->where('enabled', false)->update(['enabled' => true]);
        Konstellation::whereIn('id', $konstellationenSeen)->where('enabled', false)->update(['enabled' => true]);

        // --- AD User Abgleich -------------------------------------------------
        foreach ($rows as $row) {
            $funktionName = trim($row['d_0032_batchbez'] ?? '');
            $abteilungName = trim($row['d_abt_txt'] ?? '');
            $ueName = trim($row['d_pers_txt'] ?? '');
            $arbeitsortName = trim($row['d_arbortx'] ?? '');
            $titelName = trim($row['d_titel'] ?? '');
            $anredeName = trim($row['d_anrlt'] ?? '');
            $personalnummer = ltrim(trim($row['d_pernr'] ?? ''), '0');
            $username = trim($row['d_name'] ?? '');

            $funktionId = $funktionName ? Funktion::where('name', $funktionName)->value('id') : null;
            $abteilungId = $abteilungName ? Abteilung::where('name', $abteilungName)->value('id') : null;
            $ueId = $ueName ? Unternehmenseinheit::where('name', $ueName)->value('id') : null;
            $arbeitsortId = $arbeitsortName ? Arbeitsort::where('name', $arbeitsortName)->value('id') : null;
            $titelId = $titelName ? Titel::where('name', $titelName)->value('id') : null;
            $anredeId = $anredeName ? Anrede::where('name', $anredeName)->value('id') : null;

            $adUser = null;
            if ($username !== '') {
                $adUser = AdUser::where('username', $username)->first();
            }
            if (!$adUser && $personalnummer !== '') {
                $adUser = AdUser::where('initials', $personalnummer)->first();
            }

			if ($adUser) {
				$fields = [
					'funktion_id' => $funktionId,
					'abteilung_id' => $abteilungId,
					'unternehmenseinheit_id' => $ueId,
					'arbeitsort_id' => $arbeitsortId,
					'titel_id' => $titelId,
					'anrede_id' => $anredeId,
					'is_existing' => true,
				];

				$changes = [];
				foreach ($fields as $key => $newValue) {
					$oldValue = $adUser->{$key};
					if ($oldValue != $newValue) {
						$changes[$key] = [
							'old' => $oldValue ? $this->resolveName($key, $oldValue) : null,
							'new' => $newValue ? $this->resolveName($key, $newValue) : null,
						];
					}
				}

				if (!empty($changes)) {
					$adUser->update($fields);

					Logger::db('sap', 'info', "ADUser aktualisiert: {$adUser->username}", [
						'username' => $adUser->username,
						'personalnummer' => $adUser->initials,
						'changes' => $changes,
					]);
				}
			}

        }
    }

    protected function disableMissing(string $modelClass, array $seenIds): void
    {
        $query = $modelClass::whereNotIn('id', $seenIds)->where('enabled', true);
        $query->get()->each(function ($item) use ($modelClass) {
            $item->update(['enabled' => false]);
            Logger::db('sap', 'info', class_basename($modelClass) . " deaktiviert", [
                'id' => $item->id,
                'name' => $item->name ?? null,
                'actor' => $this->actor,
            ]);
        });
    }

protected function resolveName(string $field, int $id): array
{
    return match ($field) {
        'funktion_id' => ['id' => $id, 'name' => Funktion::find($id)?->name],
        'abteilung_id' => ['id' => $id, 'name' => Abteilung::find($id)?->name],
        'unternehmenseinheit_id' => ['id' => $id, 'name' => Unternehmenseinheit::find($id)?->name],
        'arbeitsort_id' => ['id' => $id, 'name' => Arbeitsort::find($id)?->name],
        'titel_id' => ['id' => $id, 'name' => Titel::find($id)?->name],
        'anrede_id' => ['id' => $id, 'name' => Anrede::find($id)?->name],
        default => ['id' => $id, 'name' => null],
    };
}


}
