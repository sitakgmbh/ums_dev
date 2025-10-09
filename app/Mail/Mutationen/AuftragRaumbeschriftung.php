<?php

namespace App\Mail\Eroeffnungen;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\Eroeffnung;

class AuftragRaumbeschriftung extends Mailable
{
    use Queueable, SerializesModels;

    public Eroeffnung $eroeffnung;

    public function __construct(Eroeffnung $eroeffnung)
    {
        $this->eroeffnung = $eroeffnung;
    }

    public function build()
    {
        $subject = sprintf(
            "Auftrag Raumbeschriftung %s %s",
            $this->eroeffnung->vorname,
            $this->eroeffnung->nachname
        );

        return $this->subject($subject)
            ->view("mails.eroeffnungen.auftrag-raumbeschriftung")
            ->with([
                "eroeffnung" => $this->eroeffnung,
            ]);
    }
}
