<?php

namespace App\Support;

use Illuminate\Support\Facades\Mail;
use App\Utils\Logging\Logger;

class SafeMail
{
    /**
     * Robustes Versenden von E-Mails. Bietet Optionen wie Test-Mode.
     */
    public static function send($mailable, string|array $to, array $cc = []): bool
    {
        $testMode = env('TEST_MODE', false);

        if ($testMode) 
		{
            Logger::info('TEST_MODE aktiv – Mail nicht versendet', [
                'to'   => $to,
                'cc'   => $cc,
                'mail' => get_class($mailable),
            ]);
            return true;
        }

        try 
		{
            Mail::to($to)
                ->cc($cc)
                ->send($mailable);

            Logger::info('Mail erfolgreich versendet', [
                'to'   => $to,
                'cc'   => $cc,
                'mail' => get_class($mailable),
            ]);

            return true;
        } 
		catch (\Throwable $e) 
		{
            Logger::error('Fehler beim Mailversand', [
                'error' => $e->getMessage(),
                'to'    => $to,
                'cc'    => $cc,
                'mail'  => get_class($mailable),
            ]);

            return false;
        }
    }
}
