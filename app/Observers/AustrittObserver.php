<?php

namespace App\Observers;

use Illuminate\Support\Facades\Auth;
use App\Models\Austritt;
use App\Services\OtoboService;
use App\Utils\Logging\Logger;

/**
 * Überwacht Austritte
 */
class AustrittObserver
{
    protected function filterData(array $data, Austritt $austritt): array
    {
        return collect($data)
            ->only($austritt->getFillable())
            ->toArray();
    }

    public function created(Austritt $austritt): void
    {
        $antragsteller = Auth::user();
        $username = $antragsteller?->username ?? "unbekannt";
        $fullname = $antragsteller?->name ?? ($antragsteller?->firstname . " " . $antragsteller?->lastname);

        Logger::db("antraege", "info", "Austritt ID {$austritt->id} erstellt durch {$fullname} ({$username})", [
            "austritt_id" => $austritt->id,
            "form_data"   => $this->filterData($austritt->getAttributes(), $austritt),
        ]);

        // Ticket erstellen
        app(OtoboService::class)->createTicket($austritt);
    }

    public function updated(Austritt $austritt): void
    {
        $user = Auth::user();
        $username = $user?->username ?? "unbekannt";
        $fullname = $user?->name ?? ($user?->firstname . " " . $user?->lastname);

        $changes  = $this->filterData($austritt->getChanges(), $austritt);
        $original = $this->filterData($austritt->getOriginal(), $austritt);

        Logger::db("antraege", "info", "Austritt ID {$austritt->id} bearbeitet durch {$fullname} ({$username})", [
            "austritt_id" => $austritt->id,
            "changes"     => $changes,
            "original"    => $original,
        ]);

        // Ticket schliessen wenn archiviert
        if ($austritt->wasChanged('archiviert') && $austritt->archiviert) 
		{
            $msg = "Austritt wurde archiviert durch {$fullname} ({$username}).";
            app(OtoboService::class)->updateTicket($austritt, $msg, true);
            return;
        }
    }

    public function deleted(Austritt $austritt): void
    {
        $user = Auth::user();
        $username = $user?->username ?? "unbekannt";
        $fullname = $user?->name ?? ($user?->firstname . " " . $user?->lastname);

        $deletedData = $this->filterData($austritt->getOriginal(), $austritt);
		
		// Logeintrag erstellen
        Logger::db("antraege", "info", "Austritt ID {$austritt->id} gelöscht durch {$fullname} ({$username})", [
            "austritt_id" => $austritt->id,
            "deleted_data" => $deletedData,
        ]);

		// Ticket abschliessen
        $message = "Austritt wurde gelöscht durch {$fullname} ({$username}).";
        app(OtoboService::class)->updateTicket($austritt, $message, true);
    }
}
