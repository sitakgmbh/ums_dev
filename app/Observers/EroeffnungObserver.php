<?php

namespace App\Observers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use App\Support\SafeMail;
use App\Models\Eroeffnung;
use App\Mail\Eroeffnungen\Bestaetigung;
use App\Utils\Logging\Logger;
use App\Services\OtoboService;

/**
 * Überwacht Eröffnungen
 */
class EroeffnungObserver
{
    protected function filterData(array $data, Eroeffnung $eroeffnung): array
    {
        $sensitive = ["passwort"];
		
		return collect($data)
            ->only($eroeffnung->getFillable())
            ->except($sensitive)
            ->toArray();
    }

    public function created(Eroeffnung $eroeffnung): void
    {
        $antragsteller = Auth::user();
        $username = $antragsteller?->username ?? "unbekannt";
        $fullname = $antragsteller?->name ?? ($antragsteller?->firstname." ".$antragsteller?->lastname);

        $context = [
            "antragsteller_id" => $antragsteller?->id,
            "username"         => $username,
            "fullname"         => $fullname,
            "eroeffnung_id"    => $eroeffnung->id,
            "form_data"        => $this->filterData($eroeffnung->getAttributes(), $eroeffnung),
        ];
		
		// Log-Eintrag erstellen
        Logger::db("antraege", "info", "Eröffnung ID {$eroeffnung->id} erstellt durch {$fullname} ({$username})", $context);

		// Bestätigungsmail versenden
        $to = "patrik@sitak.ch"; // später ersetzen durch $eroeffnung->antragsteller?->email ?: $fallback;
        $cc = [];
		
        if ($eroeffnung->bezugsperson?->email) 
		{
            // $cc[] = $eroeffnung->bezugsperson->email;
        }

		SafeMail::send(new Bestaetigung($eroeffnung), $to, $cc);

		// Ticket erstellen
		app(\App\Services\OtoboService::class)->createTicket($eroeffnung);
    }

    public function updated(Eroeffnung $eroeffnung): void
    {
        $user = Auth::user();
        $username = $user?->username ?? "unbekannt";
        $fullname = $user?->name ?? ($user?->firstname." ".$user?->lastname);

        $changes  = $this->filterData($eroeffnung->getChanges(), $eroeffnung);
        $original = $this->filterData($eroeffnung->getOriginal(), $eroeffnung);

        Logger::db("antraege", "info", "Eröffnung ID {$eroeffnung->id} bearbeitet durch {$fullname} ({$username})", [
            "eroeffnung_id" => $eroeffnung->id,
            "changes"       => $changes,
            "original"      => $original,
        ]);

		// Ticket schliessen wenn archiviert
        if ($eroeffnung->wasChanged('archiviert') && $eroeffnung->archiviert) 
		{
            $msg = "Eröffnung wurde archiviert durch {$fullname} ({$username}).";
            app(OtoboService::class)->updateTicket($eroeffnung, $msg, true);
            return;
        }
    }

    public function deleted(Eroeffnung $eroeffnung): void
    {
        $user = Auth::user();
        $username = $user?->username ?? "unbekannt";
        $fullname = $user?->name ?? ($user?->firstname." ".$user?->lastname);

        $deletedData = $this->filterData($eroeffnung->getOriginal(), $eroeffnung);

		// Logeintrag erstellen
        Logger::db("antraege", "info", "Eröffnung ID {$eroeffnung->id} gelöscht durch {$fullname} ({$username})", [
            "eroeffnung_id" => $eroeffnung->id,
            "deleted_data"  => $deletedData,
        ]);

		// Ticket abschliessen
		$message = "Eröffnung wurde gelöscht durch {$fullname} ({$username})";
		app(\App\Services\OtoboService::class)->updateTicket($eroeffnung, $message, true);
    }
}
