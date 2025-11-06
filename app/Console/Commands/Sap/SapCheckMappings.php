<?php

namespace App\Console\Commands\Sap;

use App\Models\Incident;
use App\Services\Sap\SapAdMappingService;
use Illuminate\Console\Command;

class SapCheckMappings extends Command
{
    protected $signature = 'sap:check-mappings';
    protected $description = 'Sucht Benutzer ohne Personalnummer oder fehlerhafter SAP ↔ AD-Zuordnung und erstellt einen Incident, wenn Ergebnisse gefunden wurden.';

    public function handle(SapAdMappingService $sapAdMappingService)
    {
        $filter = ['keine_personalnummer', 'kein_ad_benutzer', 'kein_sap_eintrag'];
        $excludedInitials = $sapAdMappingService->getExcludedInitials();
        $incidentMetadata = [];

        foreach ($filter as $f) {
            $benutzer = $sapAdMappingService->getFilteredData($f);

            if ($benutzer->isNotEmpty()) {
                $incidentMetadata[$f] = [];

                foreach ($benutzer as $b) {
                    $initials = $f === 'keine_personalnummer' || $f === 'kein_sap_eintrag' ? $b->initials : $b->d_pernr;

                    if (!in_array($initials, $excludedInitials)) {
                        $incidentMetadata[$f][] = [
                            'display_name' => $b->display_name,
                            'username' => $b->username,
                        ];
                    }
                }
            }
        }

        Incident::create([
            'title' => 'Abgleich SAP ↔ AD',
            'description' => 'Folgende Filter haben problematische Einträge erkannt: ' . implode(', ', array_keys($incidentMetadata)),
            'priority' => 'medium',
            'metadata' => $incidentMetadata,
            'created_by' => auth()->check() ? auth()->user()->id : null,
        ]);
    }
}