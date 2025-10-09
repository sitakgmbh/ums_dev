<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class TestMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $toAddress;

    public function __construct(string $toAddress)
    {
        $this->toAddress = $toAddress;
    }

    public function build()
    {
        return $this->subject("Testmail")
            ->view("mails.test")
            ->with([
                "toAddress" => $this->toAddress,
            ]);
    }
}
