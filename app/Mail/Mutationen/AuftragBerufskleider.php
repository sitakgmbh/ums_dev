<?php

namespace App\Mail\Mutationen;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\Mutation;

class AuftragBerufskleider extends Mailable
{
    use Queueable, SerializesModels;
    public Mutation $mutation;

    public function __construct(Mutation $mutation) { $this->mutation = $mutation; }

    public function build()
    {
        return $this->subject("Auftrag Berufskleider {$this->mutation->adUser->display_name}")
            ->view("mails.mutationen.auftrag-berufskleider")
            ->with(["mutation" => $this->mutation]);
    }
}
