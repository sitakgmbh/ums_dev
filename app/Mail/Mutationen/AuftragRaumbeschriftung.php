<?php

namespace App\Mail\Mutationen;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\Mutation;

class AuftragRaumbeschriftung extends Mailable
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
            "Auftrag Raumbeschriftung %s %s",
            $this->mutation->vorname,
            $this->mutation->nachname
        );

        return $this->subject($subject)
            ->view("mails.mutationen.auftrag-raumbeschriftung")
            ->with([
                "mutation" => $this->mutation,
            ]);
    }
}
