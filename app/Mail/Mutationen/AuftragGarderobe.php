<?php

namespace App\Mail\Mutationen;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\Mutation;

class AuftragGarderobe extends Mailable
{
    use Queueable, SerializesModels;
    public Mutation $mutation;

    public function __construct(Mutation $mutation) { $this->mutation = $mutation; }

    public function build()
    {
        return $this->subject("Auftrag Garderobe {$this->mutation->vorname} {$this->mutation->nachname}")
            ->view("mails.mutationen.auftrag-garderobe")
            ->with(["mutation" => $this->mutation]);
    }
}
