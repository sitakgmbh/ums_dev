<?php

namespace App\Console\Commands\Sap;

use App\Models\AdUser;
use App\Models\Incident;
use App\Services\Sap\SapAdMappingService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

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
                        if ($f === 'kein_ad_benutzer') {
                            $incidentMetadata[$f][] = [
                                'name' => $b->d_name . ($b->d_vname || $b->d_rufnm ? ' ' . ($b->d_rufnm ?: $b->d_vname) : ''),
                                'benutzername' => '-',
                                'personalnummer' => $b->d_pernr,
                                'funktion' => $b->d_0032_batchbez ?? '-',
                            ];
                        } else {
                            $incidentMetadata[$f][] = [
                                'name' => $b->display_name,
                                'benutzername' => $b->username,
                                'personalnummer' => $b->initials,
                                'funktion' => $b->description ?? '-',
                            ];
                        }
                    }
                }
            }
        }

        if (!empty($incidentMetadata)) {
            Incident::create([
                'title' => 'Abgleich SAP ↔ AD',
                'description' => 'Folgende Filter haben problematische Einträge erkannt: ' . implode(', ', array_keys($incidentMetadata)),
                'priority' => 'medium',
                'metadata' => $incidentMetadata,
                'created_by' => auth()->check() ? auth()->user()->id : null,
            ]);

            $this->info('Incident erfolgreich erstellt.');
        } else {
            $this->info('Keine problematischen Einträge gefunden.');
        }

        // Zusätzlicher Check für AdUser mit denselben Initialen
		$duplicateInitials = AdUser::select('initials', DB::raw('COUNT(*) as count'))
			->whereNotNull('initials')
			->whereNotIn('initials', ['99999', '11111', '00000'])
			->groupBy('initials')
			->having('count', '>', 1)
			->pluck('initials')
			->toArray();

        if (!empty($duplicateInitials)) {
            $duplicateIncidentMetadata = [];

            foreach ($duplicateInitials as $initials) {
                $users = AdUser::where('initials', $initials)->get();

                foreach ($users as $user) {
                    $duplicateIncidentMetadata[] = [
                        'name' => $user->display_name,
                        'benutzername' => $user->username,
                        'personalnummer' => $user->initials,
                        'funktion' => $user->description ?? '-',
                    ];
                }
            }

            Incident::create([
                'title' => 'Doppelte Personalnummern',
                'description' => 'Es wurden AD-Benutzer mit derselben Personalnummer gefunden.',
                'priority' => 'medium',
                'metadata' => $duplicateIncidentMetadata,
                'created_by' => auth()->check() ? auth()->user()->id : null,
            ]);

            $this->info('Incident für doppelte Personalnummern erstellt.');
        }
		 else {
            $this->info('Keine doppelten Personalnummern im AD gefunden.');
        }
    }
}