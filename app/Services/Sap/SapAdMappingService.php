<?php

namespace App\Services\Sap;

use App\Models\AdUser;
use App\Models\SapExport;
use App\Models\Funktion;
use App\Models\Abteilung;
use App\Models\Unternehmenseinheit;
use App\Models\Arbeitsort;
use App\Models\Titel;
use App\Models\Anrede;
use App\Utils\Logging\Logger;

class SapAdMappingService
{
    public function map(): void
    {
        Logger::debug("SapAdMappingService: Starte Mapping SAP-Eintrag und AD Benutzer");

        // Mapping personalnummer mit ad_user_id
        $adUsersMap = AdUser::whereNotNull('initials')
            ->where('is_existing', true)
            ->pluck('id', 'initials')
            ->toArray();

        if (empty($adUsersMap)) 
		{
            Logger::debug("SapAdMappingService: Keine AD Benutzer mit Personalnummern gefunden");
            return;
        }

        $rows = SapExport::all();
        $updateCount = 0;

        foreach ($rows as $row) 
		{
            $pn = $row->d_pernr;
			
            if (empty($pn)) 
			{
                continue;
            }

            $adUserId = $adUsersMap[$pn] ?? null;
			
            if (!$adUserId) 
			{
                continue;
            }

            // SAP → SAPExport.ad_user_id setzen
            if ($row->ad_user_id != $adUserId) 
			{
                $row->update(['ad_user_id' => $adUserId]);
                $updateCount++;
            }

            // Benutzerbezogene Attribute aus SAP übernehmen
            $adUserUpdates = [
                "funktion_id"            => $this->resolveId(Funktion::class, $row->d_0032_batchbez),
                "abteilung_id"           => $this->resolveId(Abteilung::class, $row->d_abt_txt),
                "unternehmenseinheit_id" => $this->resolveId(Unternehmenseinheit::class, $row->d_pers_txt),
                "arbeitsort_id"          => $this->resolveId(Arbeitsort::class, $row->d_arbortx),
                "titel_id"               => $this->resolveId(Titel::class, $row->d_titel),
                "anrede_id"              => $this->resolveId(Anrede::class, $row->d_anrlt),
                "is_existing"            => true,
            ];

            AdUser::where('id', $adUserId)->update($adUserUpdates);
        }

        Logger::debug("SapAdMappingService: Mapping abgeschlossen ({$updateCount} Zuordnungen aktualisiert)");
    }

    private function resolveId(string $model, ?string $name): ?int
    {
        if (!$name) 
		{
            return null;
        }

        $name = trim($name);

        return $model::where('name', $name)->value('id');
    }
}
