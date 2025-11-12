<?php

namespace App\Mail\Mutationen;

use App\Models\Mutation;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Carbon\Carbon;

class Bestaetigung extends Mailable
{
    use Queueable, SerializesModels;

    public Mutation $mutation;
    public bool $isSoon;

    public function __construct(Mutation $mutation)
    {
        $this->mutation = $mutation;

        // Eintritt in den nächsten 3 Wochen?
        $this->isSoon = $mutation->vertragsbeginn && Carbon::parse($mutation->vertragsbeginn)->isBetween(now(), now()->addWeeks(3));
    }

	public function build()
	{
		$vertragsbeginn = $this->mutation->vertragsbeginn?->format('d.m.Y') ?? '';
		$subject = "Bestätitung Mutation {$this->mutation->adUser->display_name} per {$vertragsbeginn}";

		return $this->subject($subject)
			->view("mails.mutationen.bestaetigung")
			->with([
				"mutation" => $this->mutation,
				"isSoon"   => $this->mutation->vertragsbeginn &&
							  $this->mutation->vertragsbeginn->isBetween(now(), now()->addWeeks(3)),
			]);
	}
}
