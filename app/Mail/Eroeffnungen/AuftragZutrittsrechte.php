<?php

namespace App\Mail\Eroeffnungen;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\Eroeffnung;

class AuftragZutrittsrechte extends Mailable
{
    use Queueable, SerializesModels;
    public Eroeffnung $eroeffnung;

    public function __construct(Eroeffnung $eroeffnung) { $this->eroeffnung = $eroeffnung; }

    public function build()
    {
        return $this->subject("Auftrag Zutrittsrechte {$this->eroeffnung->vorname} {$this->eroeffnung->nachname}")
            ->view("mails.eroeffnungen.auftrag-zutrittsrechte")
            ->with(["eroeffnung" => $this->eroeffnung]);
    }
}
