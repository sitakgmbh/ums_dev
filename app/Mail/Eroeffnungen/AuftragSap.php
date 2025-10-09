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
        $subject = sprintf(
            "Auftrag SAP-Eröffnung %s %s",
            $this->eroeffnung->vorname,
            $this->eroeffnung->nachname
        );

        return $this->subject($subject)
            ->view("mails.eroeffnungen.auftrag-sap")
            ->with([
                "eroeffnung"  => $this->eroeffnung,
                "hasSapUser"  => !empty($this->eroeffnung->sap_rolle_id),
                "hasSapLei"   => !empty($this->eroeffnung->is_lei),
            ]);
    }
}
