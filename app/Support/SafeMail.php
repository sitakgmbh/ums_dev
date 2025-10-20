<?php

namespace App\Support;

use Illuminate\Support\Facades\Mail;
use App\Utils\Logging\Logger;

class SafeMail
{
    /**
     * Versenden von E-Mails
     */
    public static function send($mailable, string|array $to, string|array $cc = [], string|array $bcc = []): bool
    {
        $testMode = env("TEST_MODE", false);
        $mailClass = is_object($mailable) ? get_class($mailable) : (string) $mailable;

        $toList  = (array) $to;
        $ccList  = (array) $cc;
        $bccList = (array) $bcc;

        $user     = auth()->user();
        $username = $user?->username ?? "system";
        $fullname = trim(($user?->firstname ?? "") . " " . ($user?->lastname ?? ""));

        $subject = property_exists($mailable, "subject") ? $mailable->subject : (method_exists($mailable, "subject") ? $mailable->subject() : "(kein Betreff)");

        $context = [
            "to"       => $toList,
            "cc"       => $ccList,
            "bcc"      => $bccList,
            "mail"     => $mailClass,
            "subject"  => $subject,
            "username" => $username,
            "fullname" => $fullname,
        ];

        if ($testMode)
        {
            Logger::info(
                "Simulation Versand {$mailClass} an " . implode(", ", $toList) . " durch {$username}",
                $context
            );

            return true;
        }

        try
        {
            $mailer = Mail::to($toList);

            if (! empty($ccList))
            {
                $mailer->cc($ccList);
            }

            if (! empty($bccList))
            {
                $mailer->bcc($bccList);
            }

            $mailer->send($mailable);

            Logger::db(
                "mail",
                "info",
                "Versand {$mailClass} an " . implode(", ", $toList) . " durch {$username}",
                $context + [
                    "ip"        => request()->ip(),
                    "userAgent" => request()->userAgent(),
                ]
            );

            return true;
        }
        catch (\Throwable $e)
        {
            $context["error"] = $e->getMessage();

            Logger::db(
                "mail",
                "error",
                "Fehler beim Mailversand ({$mailClass}) durch {$username}: {$e->getMessage()}",
                $context + [
                    "ip"        => request()->ip(),
                    "userAgent" => request()->userAgent(),
                ]
            );

            return false;
        }
    }
}
