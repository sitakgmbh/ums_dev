<?php

namespace App\Observers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use App\Support\SafeMail;
use App\Models\Mutation;
use App\Mail\Mutationen\Bestaetigung;
use App\Services\OtoboService;
use App\Utils\Logging\Logger;

/**
 * Überwacht Mutationen
 */
class MutationObserver
{
    protected function filterData(array $data, Mutation $mutation): array
    {
        return collect($data)
            ->only($mutation->getFillable())
            ->toArray();
    }

    public function created(Mutation $mutation): void
    {
        $antragsteller = Auth::user();
        $username = $antragsteller?->username ?? "unbekannt";
        $fullname = $antragsteller?->name ?? ($antragsteller?->firstname . " " . $antragsteller?->lastname);

		// Logeintrag erstellen
        Logger::db("antraege", "info", "Mutation ID {$mutation->id} erstellt durch {$fullname} ({$username})", [
            "mutation_id" => $mutation->id,
            "form_data"   => $this->filterData($mutation->getAttributes(), $mutation),
        ]);

		// Bestätigunsmail versenden
        $to = "patrik@sitak.ch"; // später ersetzen durch $mutation->antragsteller?->email ?: $fallback;
        $cc = [];
		
        if ($mutation->bezugsperson?->email) 
		{
            // $cc[] = $mutation->bezugsperson->email;
        }

		SafeMail::send(new Bestaetigung($mutation), $to, $cc);

		// Ticket erstellen
        app(OtoboService::class)->createTicket($mutation);
    }

	public function updated(Mutation $mutation): void
	{
		$user = Auth::user();
		$username = $user?->username ?? "unbekannt";
		$fullname = $user?->name ?? ($user?->firstname . " " . $user?->lastname);

		$changes  = $this->filterData($mutation->getChanges(), $mutation);
		$original = $this->filterData($mutation->getOriginal(), $mutation);

		Logger::db("antraege", "info", "Mutation ID {$mutation->id} bearbeitet durch {$fullname} ({$username})", [
			"mutation_id" => $mutation->id,
			"changes"     => $changes,
			"original"    => $original,
		]);

		// Ticket schliessen wenn archiviert
		if ($mutation->wasChanged('archiviert') && $mutation->archiviert)
		{
			$msg = "Mutation wurde archiviert durch {$fullname} ({$username}).";
			app(OtoboService::class)->updateTicket($mutation, $msg, true);
			return;
		}
	}


    public function deleted(Mutation $mutation): void
    {
        $user = Auth::user();
        $username = $user?->username ?? "unbekannt";
        $fullname = $user?->name ?? ($user?->firstname . " " . $user?->lastname);

        $deletedData = $this->filterData($mutation->getOriginal(), $mutation);

		// Logeintrag erstellen
        Logger::db("antraege", "info", "Mutation ID {$mutation->id} gelöscht durch {$fullname} ({$username})", [
            "mutation_id" => $mutation->id,
            "deleted_data" => $deletedData,
        ]);

		// Ticket abschliessen
        $message = "Mutation wurde gelöscht durch {$fullname} ({$username}).";
        app(OtoboService::class)->updateTicket($mutation, $message, true);
    }
}
