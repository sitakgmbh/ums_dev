<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class TestMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $recipient;
    public string $previewUrl;

    public function __construct(string $recipient, string $previewUrl)
    {
        $this->recipient = $recipient;
        $this->previewUrl = $previewUrl;
    }

    public function build()
    {
        return $this->to($this->recipient)
            ->subject('Testmail')
            ->view('mails.test-mail')
            ->with([
                'recipient' => $this->recipient,
                'previewUrl' => $this->previewUrl,
            ]);
    }
}
