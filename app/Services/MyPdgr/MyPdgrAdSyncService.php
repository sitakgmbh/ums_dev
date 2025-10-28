<?php

namespace App\Services\MyPdgr;

use App\Utils\Logging\Logger;
use Illuminate\Support\Facades\DB;
use LdapRecord\Models\ActiveDirectory\User as LdapUser;

class MyPdgrAdSyncService
{
    protected string $actor;
    protected array $stats;
    protected array $changes;
    protected int $minExpectedRows = 1000;

    protected array $attributeMap = [
        "Office" => "per_adresszusatz",
        "StreetAddress" => "per_adresse",
        "PostalCode" => "per_plz",
        "City" => "per_ort",
    ];

    public function __construct()
    {
        $this->actor = auth()->user()->username ?? "cli";
        $this->stats = [
            "found" => 0,
            "not_found" => 0,
            "disabled" => 0,
            "updated" => 0,
            "no_changes" => 0,
        ];
    }

    public function sync(): void
    {
        /*
		Logger::debug("MyPdgr zu AD Sync gestartet", [
            "actor" => $this->actor,
        ]);
		*/

        $MyPdgrUsers = $this->getMyPdgrEntries();
        if (!$MyPdgrUsers || count($MyPdgrUsers) === 0) throw new \RuntimeException("Unerwarteter Fehler beim Abfragen der Daten aus MyPdgr");
		// Logger::debug("MyPdgr-Einträge geladen: " . count($MyPdgrUsers));

        $adUsers = LdapUser::get();

		$adUsers = $adUsers->filter(function ($user) {
			$initials = $user->getFirstAttribute("initials");
			return $initials !== "00000" && $initials !== "11111" && $initials !== "99999";
		});

		// Logger::debug("AD-Benutzer geladen: " . $adUsers->count());

        foreach ($adUsers as $adUser) 
		{
            $this->changes = [];

            $username = $adUser->getFirstAttribute("samaccountname");
            $displayName = $adUser->getFirstAttribute("displayname");
            $initials = $adUser->getFirstAttribute("initials");
            $userAccountControl = $adUser->getFirstAttribute("useraccountcontrol");

			/*
			Logger::debug("═══════════════════════════════════════════════════════════════");
			Logger::debug("Verarbeite AD-Benutzer: {$displayName} ({$username})");
			Logger::debug("  Initials/Personalnummer: " . ($initials ?? "(nicht gesetzt)"));
			*/

            if (empty($initials)) {
				// Logger::debug("  ○ Keine Personalnummer (Initials) gesetzt - Übersprungen");
                $this->stats["not_found"]++;
                continue;
            }

            $MyPdgrEntry = collect($MyPdgrUsers)->firstWhere("per_pdgrnummer", $initials);

            if (!$MyPdgrEntry) 
			{
                Logger::warning("Kein MyPdgr-Eintrag für {$displayName} ({$username}) mit Personalnummer {$initials} gefunden");
                $this->stats["not_found"]++;
                continue;
            }

			/*
			Logger::debug("  ✓ MyPdgr-Eintrag gefunden", [
				"per_pdgrnummer" => $MyPdgrEntry["per_pdgrnummer"],
				"per_adresszusatz" => $MyPdgrEntry["per_adresszusatz"] ?? "",
				"per_adresse" => $MyPdgrEntry["per_adresse"] ?? "",
				"per_plz" => $MyPdgrEntry["per_plz"] ?? "",
				"per_ort" => $MyPdgrEntry["per_ort"] ?? "",
			]);
			*/

            // Prüfen ob Benutzer deaktiviert ist (userAccountControl & 2 = deaktiviert)
            if ($userAccountControl && ($userAccountControl & 2)) 
			{
				// Logger::debug("  ○ Benutzer ist deaktiviert - Übersprungen");
                $this->stats["disabled"]++;
                continue;
            }

            $this->stats["found"]++;

            // Adressattribute synchronisieren
            $this->syncAddressAttributes($adUser, $MyPdgrEntry, $username, $initials);

            if (!empty($this->changes)) 
			{
				/*
				Logger::debug("  ✓ Änderungen für '{$username}' erkannt:", [
					"anzahl_änderungen" => count($this->changes),
					"änderungen" => $this->changes,
				]);
				*/

                Logger::db("mypdgr", "info", "Benutzer '{$username}' aktualisiert (MyPdgr-Sync)", [
                    "personalnummer" => $initials,
                    "username" => $username,
                    "displayname" => $displayName,
                    "changes" => $this->changes,
                    "actor" => $this->actor,
                ]);

                $this->stats["updated"]++;
            } 
			else 
			{
				// Logger::debug("  ○ Keine Änderungen für '{$username}' erforderlich");
                $this->stats["no_changes"]++;
            }
        }

        // Logger::debug("═══════════════════════════════════════════════════════════════");
        // Logger::debug("MyPdgr zu AD Sync abgeschlossen", $this->stats);
    }

    protected function getMyPdgrEntries(): array
    {
        try 
		{
            $host = env("MyPdgr_DB_HOST");
            $port = env("MyPdgr_DB_PORT", 3306);
            $database = env("MyPdgr_DB_DATABASE");
            $username = env("MyPdgr_DB_USERNAME");
            $password = env("MyPdgr_DB_PASSWORD");
            $table = env("MyPdgr_DB_TABLE", "avs_personen");

            if (!$host || !$database || !$username || !$password) throw new \RuntimeException("MyPdgr Datenbank-Konfiguration in .env nicht vollständig");

			/*
            Logger::debug("Verbinde mit MyPdgr-Datenbank", [
                "host" => $host,
                "database" => $database,
                "table" => $table,
            ]);
			*/

            // Temporäre Verbindung zur MyPdgr-Datenbank erstellen
            config([
                "database.connections.MyPdgr_temp" => [
                    "driver" => "mysql",
                    "host" => $host,
                    "port" => $port,
                    "database" => $database,
                    "username" => $username,
                    "password" => $password,
                    "charset" => "utf8mb4",
                    "collation" => "utf8mb4_unicode_ci",
                    "prefix" => "",
                    "strict" => false,
                    "engine" => null,
                ]
            ]);

            // Prüfen ob Tabelle existiert
            $tableExists = DB::connection("MyPdgr_temp")
                ->select("SELECT COUNT(*) as count FROM information_schema.tables WHERE table_schema = ? AND table_name = ?", [$database, $table]);

            if ($tableExists[0]->count == 0) 
			{
                $msg = "Die Tabelle '{$table}' existiert nicht in der MyPdgr-Datenbank '{$database}'.";
                Logger::db("MyPdgr", "error", $msg, ["actor" => $this->actor]);
                throw new \RuntimeException($msg);
            }

            // Anzahl Einträge prüfen
            $rowCount = DB::connection("MyPdgr_temp")
                ->table($table)
                ->count();

            // Logger::debug("MyPdgr Tabelle '{$table}' enthält {$rowCount} Einträge");

            if ($rowCount < $this->minExpectedRows) 
			{
                $msg = "Die Tabelle '{$table}' enthält nur {$rowCount} Einträge (erwartet: mindestens {$this->minExpectedRows}).";
                Logger::db("MyPdgr", "error", $msg, ["actor" => $this->actor]);
                throw new \RuntimeException($msg);
            }

            $results = DB::connection("MyPdgr_temp")
                ->table($table)
                ->get()
                ->map(function ($row) {
                    return (array) $row;
                })
                ->toArray();

            // Logger::debug("MyPdgr-Daten erfolgreich abgerufen: " . count($results) . " Einträge");

            return $results;
        } 
		catch (\Exception $e) 
		{
            Logger::db("MyPdgr", "error", "Fehler beim Abrufen der MyPdgr-Daten", [
                "error" => $e->getMessage(),
                "actor" => $this->actor,
            ]);
			
            throw $e;
        }
    }

    protected function syncAddressAttributes($adUser, array $MyPdgrEntry, string $username, string $personalnummer): void
    {
        // Logger::debug("  → Synchronisiere Adressattribute");

        foreach ($this->attributeMap as $adAttr => $MyPdgrField) 
		{
            $MyPdgrValue = trim($MyPdgrEntry[$MyPdgrField] ?? "");
            $adValue = $adUser->getFirstAttribute(strtolower($adAttr));
			
			/*
            Logger::debug("    Attribut-Prüfung: {$adAttr}", [
                "MyPdgr_feld" => $MyPdgrField,
                "ad_wert" => $adValue ?? "(null)",
                "MyPdgr_wert" => $MyPdgrValue ?: "(leer)",
            ]);
			*/

            // Nur aktualisieren wenn MyPdgr-Wert nicht leer ist UND unterschiedlich zum AD-Wert
            if (!empty($MyPdgrValue) && $adValue !== $MyPdgrValue) 
			{
                try 
				{
					Logger::debug("    ✓ Attribut wird geändert:", [
                        "von" => $adValue ?? "(null)",
                        "nach" => $MyPdgrValue,
                    ]);

                    // $adUser->setFirstAttribute(strtolower($adAttr), $MyPdgrValue);
                    // $adUser->save();

                    $this->changes[] = [
                        "attribute" => $adAttr,
                        "old" => $adValue,
                        "new" => $MyPdgrValue,
                    ];
                } 
				catch (\Exception $e) 
				{
                    Logger::db("mypdgr", "error", "Fehler beim Aktualisieren des Attributs '{$adAttr}' des Benutzers '{$username}'", [
                        "username" => $username,
                        "personalnummer" => $personalnummer,
                        "error" => $e->getMessage(),
                        "actor" => $this->actor,
                    ]);
                }
            } 
			else 
			{
                // Logger::debug("    ○ Attribut bleibt unverändert");
            }
        }
    }
}