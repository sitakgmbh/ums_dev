<?php

namespace App\Utils;

use Carbon\Carbon;

class DateHelper
{
    /**
     * Prüft, ob ein Datum gültig ist für Vertragsbeginn:
     *  - mindestens 3 Werktage in der Zukunft
     *  - kein Wochenende
     *  - kein Feiertag (laut config/ums/feiertage.php)
     */
    public static function validateVertragsbeginn(string $dateString): ?string
    {
        if (empty($dateString)) 
		{
            return "Kein Datum angegeben.";
        }

        $date = Carbon::parse($dateString)->startOfDay();
        $today = now()->startOfDay();

        $feiertage = collect(config("ums.feiertage", []))
            ->map(fn($d) => Carbon::parse($d)->toDateString())
            ->toArray();

        $minDate = $today->copy();
        $tageInZukunft = 0;

        while ($tageInZukunft < 3) 
		{
            $minDate->addDay();
			
            if (!$minDate->isWeekend() && !in_array($minDate->toDateString(), $feiertage)) 
			{
                $tageInZukunft++;
            }
        }

        if ($date->lt($minDate)) 
		{
            return "Der Vertragsbeginn muss mindestens drei Werktage in der Zukunft liegen.";
        }

        if ($date->isWeekend()) 
		{
            return "Der Vertragsbeginn darf nicht auf ein Wochenende fallen.";
        }

        if (in_array($date->toDateString(), $feiertage)) 
		{
            return "Der Vertragsbeginn darf nicht auf einen Feiertag fallen.";
        }

        return null;
    }
}
