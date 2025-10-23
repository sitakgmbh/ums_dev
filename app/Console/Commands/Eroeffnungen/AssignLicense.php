<?php

namespace App\Console\Commands\Eroeffnungen;

use App\Models\Eroeffnung;
use App\Utils\Logging\Logger;
use Illuminate\Console\Command;
use LdapRecord\Models\ActiveDirectory\User as LdapUser;
use LdapRecord\Models\ActiveDirectory\Group as LdapGroup;
use Throwable;

class AssignLicense extends Command
{
    protected $signature = "eroeffnungen:assign-license";
    protected $description = "Weist Mitarbeitenden, die am heutigen Tag eintreten, eine M365-Lizenz zu.";

    public function handle(): int
    {
        $this->info("Starte Lizenz-Zuweisung für heutige Eintritte");

        try 
		{
            $eroeffnungen = Eroeffnung::where("archiviert", 0)
                ->whereDate("vertragsbeginn", today())
                ->get();

            if ($eroeffnungen->isEmpty()) 
			{
                $this->info("Keine Eröffnungen mit heutigem Vertragsbeginn gefunden");
                return self::SUCCESS;
            }

            $this->info("Gefunden: {$eroeffnungen->count()} Eröffnung(en) mit heutigem Vertragsbeginn");

            // Lizenz-Konfiguration laden
            $licenseMap = config("ums.m365_licenses.licenses", []);
            $targetLicense = "E3";
            $targetGroupCn = $licenseMap[$targetLicense] ?? null;

            if (! $targetGroupCn) {
                throw new \RuntimeException("Keine AD-Gruppe für Lizenz '{$targetLicense}' definiert");
            }

            $stats = [
                "required" => $eroeffnungen->count(),
                "added" => 0,
                "removed" => 0,
                "failed" => 0,
            ];

            foreach ($eroeffnungen as $eroeffnung) 
			{
                $username = $eroeffnung->benutzername;
                $this->info("Verarbeite '{$username}'");

                try 
				{
                    // AD-Benutzer abrufen
                    $user = LdapUser::query()
                        ->whereEquals("samaccountname", $username)
                        ->first();

                    if (! $user) throw new \RuntimeException("AD-Benutzer nicht gefunden");

                    // Zielgruppe auflösen
                    $targetGroup = LdapGroup::query()
                        ->whereEquals("cn", $targetGroupCn)
                        ->first();

                    if (! $targetGroup) throw new \RuntimeException("AD-Gruppe '{$targetGroupCn}' nicht gefunden");

                    // AD-Gruppe zuweisen, falls noch nicht vorhanden
                    if (! $targetGroup->members()->exists($user)) 
					{
                        $targetGroup->members()->attach($user);
                        $this->line("Lizenz '{$targetLicense}' hinzugefügt ({$targetGroupCn})");
                        $stats["added"]++;

                        Logger::db("antraege", "info", "Benutzer '{$username}' M365-Lizenz zugewiesen", [
                            "benutzername" => $username,
                            "eroeffnung_id" => $eroeffnung->id,
                            "vertragsbeginn" => $eroeffnung->vertragsbeginn,
                            "lizenz" => $targetLicense,
                            "gruppe" => $targetGroupCn,
                        ]);
                    } 
					else 
					{
                        $this->line("= Lizenz '{$targetLicense}' bereits vorhanden ({$targetGroupCn})");
                    }

                    // Andere Lizenzgruppen entfernen
                    foreach ($licenseMap as $license => $groupCn) 
					{
                        if ($license === $targetLicense) continue;

                        $group = LdapGroup::query()
                            ->whereEquals("cn", $groupCn)
                            ->first();

                        if ($group && $group->members()->exists($user)) 
						{
                            $group->members()->detach($user);
                            $this->warn("Lizenz '{$license}' entfernt ({$groupCn})");
                            $stats["removed"]++;

                            Logger::db("antraege", "info", "Benutzer '{$username}' M365-Lizenz entfernt", [
                                "benutzername" => $username,
                                "eroeffnung_id" => $eroeffnung->id,
                                "vertragsbeginn" => $eroeffnung->vertragsbeginn,
                                "lizenz" => $license,
                                "gruppe" => $groupCn,
                            ]);
                        }
                    }
                }
                catch (Throwable $e) 
				{
                    $stats["failed"]++;
                    $this->error("Fehler: - {$e->getMessage()}");

                    Logger::db("antraege", "error", "Zuweisung M365-Lizenz '{$username}' fehlgeschlagen", [
                        "benutzername" => $username,
                        "eroeffnung_id" => $eroeffnung->id,
                        "vertragsbeginn" => $eroeffnung->vertragsbeginn,
                        "exception" => $e->getMessage(),
                    ]);
                }
            }

            // Zusammenfassung
            $this->newLine();
			$this->info("Zusammenfassung:");
            $this->newLine();
            $this->info("Benötigt:       {$stats['required']}");
            $this->info("Hinzugefügt:    {$stats['added']}");
            $this->info("Entfernt:       {$stats['removed']}");
            $this->info("Fehlgeschlagen: {$stats['failed']}");

            return $stats["failed"] > 0 ? self::FAILURE : self::SUCCESS;
        }
        catch (Throwable $e) 
		{
            $this->error("Unerwarteter Fehler: " . $e->getMessage());

            Logger::db("antraege", "error", "Unerwarteter Fehler bei der Lizenz-Zuweisung", [
                "exception" => $e->getMessage(),
                "trace" => $e->getTraceAsString(),
            ]);

            return self::FAILURE;
        }
    }
}
