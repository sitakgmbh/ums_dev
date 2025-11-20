<?php

namespace App\Console\Commands\StateChecker;

use Illuminate\Console\Command;
use App\Models\Eroeffnung;
use App\Models\Setting;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class SapAdExcludes extends Command
{
    protected $signature = 'check:sap-ad-excludes';
    protected $description = 'Prüft, ob es am heutigen Tag Entritte gibt. Falls ja, wird geprüft, ob der hinterlegte Benutzernamen in den Einstellungen exkludiert ist. Falls ja, wird der Eintrag entfernt.';

    public function handle()
    {
        $this->info('Starte Bereinigung der Excluded-Usernames...');

        $key = 'sap_ad_abgleich_excludes_benutzernamen';
        $raw = Setting::getValue($key, '');

        $excludedList = collect(explode(',', $raw))
            ->map(fn($v) => trim($v))
            ->filter()
            ->values();

        if ($excludedList->isEmpty()) 
		{
            $this->info('Keine Excludes vorhanden – nichts zu tun.');
            return 0;
        }

        $today = date('Y-m-d');

        $eroeffnungen = Eroeffnung::where('archiviert', 0)
            ->whereDate('vertragsbeginn', $today)
            ->whereNotNull('benutzername')
            ->get();

        if ($eroeffnungen->isEmpty()) 
		{
            $this->info("Keine Eroeffnungen fuer heute ({$today}).");
            return 0;
        }

        $this->info("Gefundene Eroeffnungen: " . $eroeffnungen->count());

        $removed = [];

        foreach ($eroeffnungen as $e) 
		{
            $username = trim($e->benutzername);

            if ($excludedList->contains($username)) 
			{
                $this->info("Bereinige: {$username}");
                Log::info("CleanupExcludedUsernames: Entferne {$username} aus Excludes wegen Eroeffnung-ID {$e->id}");

                $excludedList = $excludedList->reject(fn($u) => $u === $username)->values();

                $removed[] = $username;
            }
        }

        if (!empty($removed)) 
		{
            $newValue = $excludedList->implode(',');
            $setting = Setting::firstOrNew(['key' => $key]);
            $setting->value = $newValue;

            if (!$setting->exists) 
			{
                $setting->type = 'string';
                $setting->name = 'SAP/AD Excluded Usernames';
                $setting->description = 'Liste der ausgeschlossenen Benutzernamen fuer den SAP/AD-Abgleich';
            }

            $setting->save();
            Cache::forget("setting_{$key}");

            $this->info("Bereinigung abgeschlossen: " . count($removed) . " Benutzer entfernt.");
        } 
		else 
		{
            $this->info("Keine uebereinstimmenden Benutzer gefunden.");
        }

        return 0;
    }
}
