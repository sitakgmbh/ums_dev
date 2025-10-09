<?php

namespace App\Observers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use App\Models\Eroeffnung;
use App\Mail\EroeffnungBestaetigungMail;
use App\Utils\Logging\Logger;
use App\Services\OtoboService;


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

        Logger::db("antraege", "info", "Eröffnung ID {$eroeffnung->id} erstellt durch {$fullname} ({$username})", $context);

        $to = "patrik@sitak.ch"; // später ersetzen durch $eroeffnung->antragsteller?->email ?: $fallback;
        $cc = [];
		
        if ($eroeffnung->bezugsperson?->email) 
		{
            // $cc[] = $eroeffnung->bezugsperson->email;
        }

        Mail::to($to)->cc($cc)->send(new EroeffnungBestaetigungMail($eroeffnung));

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

        if ($eroeffnung->wasChanged('archiviert') && $eroeffnung->archiviert) {
            $msg = "Eröffnung wurde archiviert durch {$fullname} ({$username}).";
            app(OtoboService::class)->updateTicket($eroeffnung, $msg, true);
            return;
        }

        if (!empty($changes)) {
            $message = "Eröffnung aktualisiert durch {$fullname} ({$username}):\n\n";

            foreach ($changes as $field => $newValue) {
                $oldValue = $original[$field] ?? '(leer)';
                $newValue = $newValue === '' ? '(leer)' : $newValue;
                $message .= "- {$field}: {$oldValue} → {$newValue}\n";
            }

            app(OtoboService::class)->updateTicket($eroeffnung, $message);
        }
    }

    public function deleted(Eroeffnung $eroeffnung): void
    {
        $user = Auth::user();
        $username = $user?->username ?? "unbekannt";
        $fullname = $user?->name ?? ($user?->firstname." ".$user?->lastname);

        $deletedData = $this->filterData($eroeffnung->getOriginal(), $eroeffnung);

        Logger::db("antraege", "info", "Eröffnung ID {$eroeffnung->id} gelöscht durch {$fullname} ({$username})", [
            "eroeffnung_id" => $eroeffnung->id,
            "deleted_data"  => $deletedData,
        ]);

		$message = "Eröffnung wurde gelöscht durch {$fullname} ({$username})";
		app(\App\Services\OtoboService::class)->updateTicket($eroeffnung, $message, true);
    }
}
