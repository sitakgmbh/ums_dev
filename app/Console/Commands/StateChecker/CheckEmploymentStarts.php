<?php

namespace App\Console\Commands\StateChecker;

use App\Models\Eroeffnung;
use App\Utils\Logging\Logger;
use Illuminate\Console\Command;
use LdapRecord\Models\ActiveDirectory\User as LdapUser;
use LdapRecord\Models\ActiveDirectory\Group as LdapGroup;
use Throwable;

class CheckEmploymentStarts extends Command
{
    protected $signature = 'check:employment-starts';
    protected $description = 'Weist Mitarbeitenden, die am heutigen Tag eintreten, eine M365-Lizenz zu.';

    public function handle(): int
    {
        $this->info('Starte Lizenz-Zuweisung für heutige Eintritte');

        try 
        {
            $eroeffnungen = Eroeffnung::where('archiviert', 0)
                ->whereDate('vertragsbeginn', today())
                ->get();

            if ($eroeffnungen->isEmpty()) 
            {
                $this->info('Keine Eröffnungen mit heutigem Vertragsbeginn gefunden');
                return self::SUCCESS;
            }

            $this->info("Gefunden: {$eroeffnungen->count()} Eröffnung(en)");

            // Lizenz-Konfiguration laden
            $config = config('ums.m365_licenses', []);
            $licenseMap = $config['licenses'] ?? [];
            $defaultGroupCn = $config['default'] ?? null;

            if (! $defaultGroupCn) 
            {
                throw new \RuntimeException('Keine Default-Lizenzgruppe in Config definiert.');
            }

            // Default-AD-Gruppe abrufen
            $defaultGroup = LdapGroup::query()
                ->whereEquals('cn', $defaultGroupCn)
                ->first();

            if (! $defaultGroup) 
            {
                throw new \RuntimeException("AD-Gruppe '{$defaultGroupCn}' (Default) nicht gefunden.");
            }

            $stats = [
                'processed' => $eroeffnungen->count(),
                'added' => 0,
                'removed' => 0,
                'failed' => 0,
            ];

            foreach ($eroeffnungen as $eroeffnung) 
            {
                $username = $eroeffnung->benutzername;
                $this->line("Verarbeite '{$username}'...");

                // Tracking für diesen Benutzer
                $userActions = [
                    'removed' => [],
                    'added' => [],
                    'skipped' => [],
                ];

                try 
                {
                    $user = LdapUser::query()
                        ->whereEquals('samaccountname', $username)
                        ->first();

                    if (! $user) 
                    {
                        throw new \RuntimeException('AD-Benutzer nicht gefunden');
                    }

                    // Alle Lizenzgruppen entfernen außer Default
                    foreach ($licenseMap as $license => $groupCn) 
                    {
                        // Default überspringen
                        if ($groupCn === $defaultGroupCn) 
                        {
                            continue;
                        }

                        $group = LdapGroup::query()
                            ->whereEquals('cn', $groupCn)
                            ->first();

                        if ($group && $group->members()->exists($user)) 
                        {
                            $group->members()->detach($user);
                            $this->warn("Entfernt: {$license} ({$groupCn})");
                            $stats['removed']++;
                            $userActions['removed'][] = "{$license} ({$groupCn})";
                        }
                    }

                    // Standardlizenz-Zuweisung sicherstellen
                    if (! $defaultGroup->members()->exists($user)) 
                    {
                        $defaultGroup->members()->attach($user);
                        $this->info("Default-Lizenz hinzugefügt ({$defaultGroupCn})");
                        $stats['added']++;
                        $userActions['added'][] = "Default-Lizenz ({$defaultGroupCn})";
                    } 
                    else 
                    {
                        $this->line("= Default-Lizenz bereits vorhanden ({$defaultGroupCn})");
                        $userActions['skipped'][] = "Default-Lizenz bereits vorhanden";
                    }

					// Nur loggen wenn tatsächlich etwas geändert wurde
					if (!empty($userActions['added']) || !empty($userActions['removed'])) {
						Logger::db('ad', 'info', "M365-Lizenzierung Benutzer '{$username}' angepasst", [
							'eroeffnung_id' => $eroeffnung->id,
							'username' => $username,
							'added' => $userActions['added'],
							'removed' => $userActions['removed'],
						]);
					}
                } 
                catch (Throwable $e) 
                {
                    $stats['failed']++;
                    $this->error("Fehler bei {$username}: {$e->getMessage()}");

                    Logger::db('ad', 'error', "M365-Lizenzierung Benutzer '{$username}' fehlgeschlagen", [
                        'username' => $username,
                        'eroeffnung_id' => $eroeffnung->id,
                        'exception' => $e->getMessage(),
                    ]);
                }
            }

            // Zusammenfassung
            $this->newLine();
            $this->info('Zusammenfassung:');
            $this->line("Verarbeitet:     {$stats['processed']}");
            $this->line("Hinzugefügt:     {$stats['added']}");
            $this->line("Entfernt:        {$stats['removed']}");
            $this->line("Fehlgeschlagen:  {$stats['failed']}");

            return $stats['failed'] > 0 ? self::FAILURE : self::SUCCESS;

        } 
        catch (Throwable $e) 
        {
            $this->error("Unerwarteter Fehler: {$e->getMessage()}");

            Logger::db('ad', 'error', 'Fehler bei der Lizenz-Zuweisung', [
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return self::FAILURE;
        }
    }
}