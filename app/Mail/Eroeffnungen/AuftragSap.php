<?php

namespace App\Mail\Eroeffnungen;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\Eroeffnung;

class AuftragSap extends Mailable
{
    use Queueable, SerializesModels;

    public Eroeffnung $eroeffnung;

    public function __construct(Eroeffnung $eroeffnung)
    {
        $this->eroeffnung = $eroeffnung;
    }

    public function build()
    {
        $vertragsbeginn = $this->eroeffnung->vertragsbeginn?->format('d.m.Y') ?? '';
		$subject = "Auftrag SAP-Eröffnung {$this->eroeffnung->nachname} {$this->eroeffnung->vorname} per {$vertragsbeginn}";
		
		return $this->subject($subject)
            ->view("mails.eroeffnungen.auftrag-sap")
            ->with(["eroeffnung" => $this->eroeffnung]);
    }
}
