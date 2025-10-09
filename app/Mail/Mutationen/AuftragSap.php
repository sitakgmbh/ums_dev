<?php

namespace App\Mail\Mutationen;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\Mutation;

class AuftragSap extends Mailable
{
    use Queueable, SerializesModels;

    public Mutation $mutation;

    public function __construct(Mutation $mutation)
    {
        $this->mutation = $mutation;
    }

    public function build()
    {
        $subject = sprintf(
            "Auftrag SAP-Mutation %s %s",
            $this->mutation->vorname,
            $this->mutation->nachname
        );

        return $this->subject($subject)
            ->view("mails.mutationen.auftrag-sap")
            ->with([
                "mutation" => $this->mutation,
                "hasSapRole"   => !empty($this->mutation->sap_rolle_id),
                "hasSapDelete" => !empty($this->mutation->sap_delete),
                "hasSapLei"    => !empty($this->mutation->komm_lei),
            ]);
    }
}
