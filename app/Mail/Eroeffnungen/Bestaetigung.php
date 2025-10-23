<?php

namespace App\Mail\Eroeffnungen;

use App\Models\Eroeffnung;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Carbon\Carbon;

class Bestaetigung extends Mailable
{
    use Queueable, SerializesModels;

    public Eroeffnung $eroeffnung;
    public bool $isSoon;

    public function __construct(Eroeffnung $eroeffnung)
    {
        $this->eroeffnung = $eroeffnung;

        // Eintritt in den nächsten 3 Wochen?
        $this->isSoon = $eroeffnung->vertragsbeginn && Carbon::parse($eroeffnung->vertragsbeginn)->isBetween(now(), now()->addWeeks(3));
    }

	public function build()
	{
		$vertragsbeginn = $this->eroeffnung->vertragsbeginn ? $this->eroeffnung->vertragsbeginn->format("d.m.Y") : "Kein Datum";

		return $this->subject("Bestätigung Antrag Eröffnung {$this->eroeffnung->vorname} {$this->eroeffnung->nachname} per {$vertragsbeginn}")
			->view("mails.eroeffnungen.bestaetigung")
			->with([
				"eroeffnung" => $this->eroeffnung,
				"isSoon"     => $this->eroeffnung->vertragsbeginn &&
								$this->eroeffnung->vertragsbeginn->isBetween(now(), now()->addWeeks(3)),
			]);
	}

}
