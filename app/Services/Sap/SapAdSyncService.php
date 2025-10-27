<?php

namespace App\Services\Sap;

use App\Utils\Logging\Logger;
use App\Utils\UserHelper;
use App\Models\Mutation;
use App\Models\AdUser;
use LdapRecord\Models\ActiveDirectory\User as LdapUser;

class SapAdSyncService
{
    protected string $actor;
    protected array $stats;
    protected array $changes;

    protected array $attributeMap = [
        "title"					=> ["sap" => "d_0032_batchbez"],
        "description"			=> ["sap" => "d_0032_batchbez"],
        "department"			=> ["sap" => "d_abt_txt"],
        "extensionAttribute2"	=> ["sap" => "d_gbdat"],
        "extensionAttribute5"	=> ["sap" => "d_0032_batchbez"],
        "extensionAttribute6"	=> ["sap" => "d_pers_txt"],
        "extensionAttribute7"	=> ["sap" => "d_zzkader", "if_sap_not_empty" => true],
        "extensionAttribute8"	=> ["sap" => "d_zzbereit"],
        "extensionAttribute9"	=> ["sap" => "d_einri"],
        "extensionAttribute11"	=> ["sap" => "d_einda"],
        "extensionAttribute13"	=> ["sap" => "d_titel", "if_sap_and_ad_not_empty" => true],
        "extensionAttribute15"	=> ["sap" => "d_abt_nr"],
    ];

    public function __construct()
    {
        $this->actor = auth()->user()->username ?? "cli";
        $this->stats = [
            "found" => 0,
            "not_found" => 0,
            "updated" => 0,
            "no_changes" => 0,
            "mutations_created" => 0,
        ];
    }

	public function sync(string $filePath): void
	{
		if (!file_exists($filePath)) 
		{
			throw new \RuntimeException("SAP Export nicht gefunden: {$filePath}");
		}

		$this->changes = [];

		$content = file_get_contents($filePath);

		// Encoding erkennen und nach UTF-8 konvertieren
		$encoding = mb_detect_encoding($content, ['UTF-8', 'ISO-8859-1', 'Windows-1252', 'ASCII'], true);
		Logger::debug("Datei-Encoding erkannt: " . ($encoding ?: "unbekannt"));

		if ($encoding && $encoding !== 'UTF-8') {
			$content = mb_convert_encoding($content, 'UTF-8', $encoding);
			Logger::debug("Datei konvertiert von {$encoding} nach UTF-8");
		}

		$raw = explode("\n", $content);
		$raw = array_map('trim', $raw);
		$raw = array_filter($raw);

		$lines = $raw;
		$header = array_map("trim", explode(";", array_shift($lines)));
		$rows = [];

		foreach ($lines as $line) 
		{
			$values = array_map("trim", explode(";", $line));
			if (count($values) !== count($header)) continue;
			$rows[] = array_combine($header, $values);
		}

		$adUsers = LdapUser::get();
		
		Logger::debug("SAP zu AD Sync gestartet", [
			"csv_rows" => count($rows),
			"ad_users" => $adUsers->count(),
			"actor" => $this->actor,
		]);

		$rows = array_slice($rows, 0, 1);

		foreach ($rows as $row) 
		{
			$personalnummer = ltrim(trim($row["d_pernr"] ?? ""), "0");
			if (empty($personalnummer)) continue;

			Logger::debug("═══════════════════════════════════════════════════════════════");
			Logger::debug("Verarbeite Personalnummer: {$personalnummer}");
			Logger::debug("SAP-Rohdaten:", [
				"d_pernr" => $row["d_pernr"] ?? "",
				"d_vname" => $row["d_vname"] ?? "",
				"d_rufnm" => $row["d_rufnm"] ?? "",
				"d_name" => $row["d_name"] ?? "",
				"d_leader" => $row["d_leader"] ?? "",
			]);

			$adUser = $adUsers->first(function ($user) use ($personalnummer) {
				return $user->getFirstAttribute("initials") === $personalnummer;
			});

			if (!$adUser) 
			{
				Logger::warning("Kein AD-Benutzer zu Personalnummer {$personalnummer} gefunden");
				$this->stats["not_found"]++;
				continue;
			}		

			$username = $adUser->getFirstAttribute("samaccountname");
			Logger::debug("AD-User gefunden: {$username}");
			Logger::debug("AD-Aktuelle Werte:", [
				"givenname" => $adUser->getFirstAttribute("givenname"),
				"sn" => $adUser->getFirstAttribute("sn"),
				"displayname" => $adUser->getFirstAttribute("displayname"),
				"cn" => $adUser->getFirstAttribute("cn"),
				"userprincipalname" => $adUser->getFirstAttribute("userprincipalname"),
			]);

			$this->stats["found"]++;
			
			$this->syncNames($adUser, $row, $username, $personalnummer);
			$this->syncDisplayNameAndUpn($adUser, $row, $username, $personalnummer);
			$this->syncSimpleAttributes($adUser, $row, $username, $personalnummer);
			$this->syncManager($adUser, $row, $username, $personalnummer);

			if (!empty($this->changes)) 
			{
				Logger::debug("✓ Änderungen für '{$username}' erkannt:", [
					"anzahl_änderungen" => count($this->changes),
					"änderungen" => $this->changes,
				]);
				
				Logger::db("ad", "info", "Benutzer '{$username}' aktualisiert", [
					"personalnummer" => $personalnummer,
					"username" => $username,
					"changes" => $this->changes,
					"actor" => $this->actor,
				]);
				
				$this->stats["updated"]++;
				
				// Nach dem Loggen für nächsten User leeren
				$this->changes = [];
			} 
			else 
			{
				Logger::debug("○ Keine Änderungen für '{$username}' erforderlich");
				$this->stats["no_changes"]++;
			}
		}
		
		Logger::debug("═══════════════════════════════════════════════════════════════");
		Logger::debug("SAP zu AD Sync abgeschlossen", $this->stats);
	}

    protected function syncNames($adUser, $row, $username, $personalnummer): void
    {
		Logger::debug("→ syncNames() Start");
		
        $vornameSAP = !empty($row["d_rufnm"]) ? trim($row["d_rufnm"]) : trim($row["d_vname"] ?? "");
        $nachnameSAP = trim($row["d_name"] ?? "");
		
		Logger::debug("  SAP-Namen ermittelt:", [
			"vorname_sap" => $vornameSAP,
			"nachname_sap" => $nachnameSAP,
			"rufname_vorhanden" => !empty($row["d_rufnm"]),
		]);
		
        $vornameChanged = false;
        $nachnameChanged = false;
        
        // Vorname prüfen und anpassen
        $vornameAD = $adUser->getFirstAttribute("givenname");
        
		Logger::debug("  Vorname-Vergleich:", [
			"ad_wert" => $vornameAD ?? "(null)",
			"sap_wert" => $vornameSAP,
			"sind_gleich" => $vornameAD === $vornameSAP,
			"sap_ist_leer" => empty($vornameSAP),
		]);
		
        if ($vornameAD !== $vornameSAP && !empty($vornameSAP)) 
        {
            try 
            {
				Logger::debug("  ✓ Vorname wird geändert:", [
					"von" => $vornameAD ?? "(null)",
					"nach" => $vornameSAP,
				]);
				
				// $adUser->setFirstAttribute("givenname", $vornameSAP);
                // $adUser->save();
                
                $this->changes[] = [
                    "attribute" => "givenname",
                    "old" => $vornameAD,
                    "new" => $vornameSAP,
                ];
                
                $vornameChanged = true;
            } 
            catch (\Exception $e) 
            {
                Logger::db("ad", "error", "Fehler beim Aktualisieren des Vornamens des Benutzers '{$username}'", [
                    "username" => $username,
                    "error" => $e->getMessage(),
                    "actor" => $this->actor,
                ]);
            }
        }
		else 
		{
			Logger::debug("  ○ Vorname bleibt unverändert");
		}

        // Nachname prüfen und anpassen
        $nachnameAD = $adUser->getFirstAttribute("sn");

		Logger::debug("  Nachname-Vergleich:", [
			"ad_wert" => $nachnameAD ?? "(null)",
			"sap_wert" => $nachnameSAP,
			"sind_gleich" => $nachnameAD === $nachnameSAP,
			"sap_ist_leer" => empty($nachnameSAP),
		]);

        if ($nachnameAD !== $nachnameSAP && !empty($nachnameSAP)) 
        {
            try 
			{
                Logger::debug("  ✓ Nachname wird geändert:", [
					"von" => $nachnameAD ?? "(null)",
					"nach" => $nachnameSAP,
				]);
				
				// $adUser->setFirstAttribute("sn", $nachnameSAP);
                // $adUser->save();
                
                $this->changes[] = [
                    "attribute" => "sn",
                    "old" => $nachnameAD,
                    "new" => $nachnameSAP,
                ];
                
                $nachnameChanged = true;
            } 
			catch (\Exception $e) 
			{
                Logger::db("ad", "error", "Fehler beim Aktualisieren des Nachnamens des Benutzers '{$username}'", [
                    "username" => $username,
                    "error" => $e->getMessage(),
                    "actor" => $this->actor,
                ]);
            }
        }
		else 
		{
			Logger::debug("  ○ Nachname bleibt unverändert");
		}
        
		Logger::debug("→ syncNames() Ende - Änderungen:", [
			"vorname_geändert" => $vornameChanged,
			"nachname_geändert" => $nachnameChanged,
		]);
		
		/*
        // Mutation erstellen wenn mindestens ein Name geändert wurde
		if ($vornameChanged || $nachnameChanged) 
		{
			$this->createMutation(
				$username, 
				$vornameChanged ? $vornameSAP : null, 
				$nachnameChanged ? $nachnameSAP : null
			);
		}
		*/
    }

    protected function syncDisplayNameAndUpn($adUser, $row, $username, $personalnummer): void
    {
		Logger::debug("→ syncDisplayNameAndUpn() Start");
		
        $vornameSAP = !empty($row["d_rufnm"]) ? trim($row["d_rufnm"]) : trim($row["d_vname"] ?? "");
        $nachnameSAP = trim($row["d_name"] ?? "");
        $displayName = trim($vornameSAP . " " . $nachnameSAP);
        $displayNameAD = $adUser->getFirstAttribute("displayname");
		
		Logger::debug("  DisplayName-Vergleich:", [
			"ad_wert" => $displayNameAD ?? "(null)",
			"sap_wert_berechnet" => $displayName,
			"sind_gleich" => $displayNameAD === $displayName,
			"sap_ist_leer" => empty($displayName),
		]);
		
        if ($displayNameAD !== $displayName && !empty($displayName)) 
        {
            try 
			{
                Logger::debug("  ✓ DisplayName wird geändert:", [
					"von" => $displayNameAD ?? "(null)",
					"nach" => $displayName,
				]);
				
				// $adUser->setFirstAttribute("displayname", $displayName);
                // $adUser->save();
                
                $this->changes[] = [
                    "attribute" => "displayname",
                    "old" => $displayNameAD,
                    "new" => $displayName,
                ];
            } 
			catch (\Exception $e) 
			{
                Logger::db("ad", "error", "Fehler beim Aktualisieren des Anzeigenamens des Benutzers '{$username}'", [
                    "username" => $username,
                    "error" => $e->getMessage(),
                    "actor" => $this->actor,
                ]);
            }
        }
		else 
		{
			Logger::debug("  ○ DisplayName bleibt unverändert");
		}

        $nameAD = $adUser->getFirstAttribute("cn");
		
		Logger::debug("  CN-Vergleich:", [
			"ad_wert" => $nameAD ?? "(null)",
			"sap_wert_berechnet" => $displayName,
			"sind_gleich" => $nameAD === $displayName,
			"sap_ist_leer" => empty($displayName),
		]);
		
        if ($nameAD !== $displayName && !empty($displayName)) 
        {
            try 
			{
				$currentUpn = $adUser->getFirstAttribute("userprincipalname");
                $upnDomain = explode("@", $currentUpn)[1];
                
                $cleanFirstName = UserHelper::normalize($vornameSAP);
                $cleanLastName = UserHelper::normalize($nachnameSAP);
                $newUpn = strtolower("{$cleanFirstName}.{$cleanLastName}@{$upnDomain}");
                
				Logger::debug("  ✓ CN und UPN werden geändert:", [
					"cn_von" => $nameAD ?? "(null)",
					"cn_nach" => $displayName,
					"upn_von" => $currentUpn,
					"upn_nach" => $newUpn,
					"clean_firstname" => $cleanFirstName,
					"clean_lastname" => $cleanLastName,
				]);
				
                // $adUser->rename($displayName);
				// $adUser->setFirstAttribute("userprincipalname", $newUpn);
                // $adUser->save();

                $this->changes[] = [
                    "attribute" => "cn",
                    "old" => $nameAD,
                    "new" => $displayName,
                ];
				
                $this->changes[] = [
                    "attribute" => "userprincipalname",
                    "old" => $currentUpn,
                    "new" => $newUpn,
                ];
            } 
			catch (\Exception $e) 
			{
                Logger::db("ad", "error", "Fehler beim Umbenennen oder Aktualisieren des UPNs des Benutzers '{$username}'", [
                    "username" => $username,
                    "error" => $e->getMessage(),
                    "actor" => $this->actor,
                ]);
            }
        }
		else 
		{
			Logger::debug("  ○ CN bleibt unverändert (UPN-Update übersprungen)");
		}
		
		Logger::debug("→ syncDisplayNameAndUpn() Ende");
    }

    protected function syncSimpleAttributes($adUser, $row, $username, $personalnummer): void
    {
		Logger::debug("→ syncSimpleAttributes() Start");
		
		$attributeChanges = 0;
		
		foreach ($this->attributeMap as $adAttr => $config) 
		{
			$sapValue = trim($row[$config["sap"]] ?? "");
			$adValue = $adUser->getFirstAttribute($adAttr);
			
			Logger::debug("  Attribut-Prüfung: {$adAttr}", [
				"sap_feld" => $config["sap"],
				"ad_wert" => $adValue ?? "(null)",
				"sap_wert" => $sapValue ?: "(leer)",
				"regel_if_sap_and_ad_not_empty" => isset($config["if_sap_and_ad_not_empty"]),
				"regel_if_sap_not_empty" => isset($config["if_sap_not_empty"]),
			]);

            // Nur ändern wenn SAP und AD nicht leer sind
            if (isset($config["if_sap_and_ad_not_empty"]) && $config["if_sap_and_ad_not_empty"]) 
            {
                if (empty($adValue) && empty($sapValue)) 
				{
					Logger::debug("    → Übersprungen: Beide Werte leer (Regel: if_sap_and_ad_not_empty)");
					continue;
				}
            }

            // Nur ändern wenn SAP nicht leer ist
            if (isset($config["if_sap_not_empty"]) && $config["if_sap_not_empty"]) 
            {
                if (empty($sapValue)) 
				{
					Logger::debug("    → Übersprungen: SAP-Wert leer (Regel: if_sap_not_empty)");
					continue;
				}
            }

			// Vergleich und Update
			if ($adValue !== $sapValue) 
			{
				try 
				{
					if (empty($sapValue)) 
					{
						Logger::debug("    ✓ Attribut wird auf NULL gesetzt:", [
							"alter_wert" => $adValue,
							"neuer_wert" => "(null)",
						]);
						// $adUser->setFirstAttribute($adAttr, null);
					} 
					else 
					{
						Logger::debug("    ✓ Attribut wird geändert:", [
							"von" => $adValue ?? "(null)",
							"nach" => $sapValue,
						]);
						// $adUser->setFirstAttribute($adAttr, $sapValue);
					}
					
					// $adUser->save();
					
					$this->changes[] = [
						"attribute" => $adAttr,
						"old" => $adValue,
						"new" => $sapValue ?: null,
					];
					
					$attributeChanges++;
                } 
				catch (\Exception $e) 
				{
					Logger::db("ad", "error", "Fehler bei beim Aktualisieren des Attributs '{$adAttr}' des Benutzers '{$username}'", [
                        "username" => $username,
                        "error" => $e->getMessage(),
                        "actor" => $this->actor,
                    ]);
                }
			}
			else 
			{
				Logger::debug("    ○ Attribut bleibt unverändert (Werte sind identisch)");
			}
        }
		
		Logger::debug("→ syncSimpleAttributes() Ende - {$attributeChanges} Attribute geändert");
    }

    protected function syncManager($adUser, $row, $username, $personalnummer): void
    {
		Logger::debug("→ syncManager() Start");
		
        $managerPersNr = ltrim(trim($row["d_leader"] ?? ""), "0");
        
		Logger::debug("  Manager-Personalnummer aus SAP:", [
			"rohdaten" => $row["d_leader"] ?? "(nicht vorhanden)",
			"bereinigt" => $managerPersNr ?: "(leer)",
		]);
		
        if (empty($managerPersNr)) 
		{
			Logger::debug("  ○ Kein Manager in SAP definiert - Übersprungen");
			return;
		}

        $managerUser = LdapUser::query()
            ->whereEquals("initials", $managerPersNr)
            ->first();

        if (!$managerUser) 
		{
			Logger::debug("  ✗ Manager-Benutzer nicht gefunden:", [
				"gesuchte_personalnummer" => $managerPersNr,
			]);
			return;
		}

        $currentManagerDn = $adUser->getFirstAttribute("manager");
        $newManagerDn = $managerUser->getFirstAttribute("distinguishedname");
		
		$managerUsername = $managerUser->getFirstAttribute("samaccountname");
		$managerName = $managerUser->getFirstAttribute("displayname");
		
		Logger::debug("  Manager-Vergleich:", [
			"manager_gefunden" => "{$managerName} ({$managerUsername})",
			"aktueller_manager_dn" => $currentManagerDn ?? "(null)",
			"neuer_manager_dn" => $newManagerDn,
			"sind_gleich" => $currentManagerDn === $newManagerDn,
		]);

        if ($currentManagerDn !== $newManagerDn) 
        {
            try 
			{
                Logger::debug("  ✓ Manager wird geändert:", [
					"von_dn" => $currentManagerDn ?? "(null)",
					"nach_dn" => $newManagerDn,
					"manager" => "{$managerName} ({$managerUsername})",
				]);
				
				// $adUser->setFirstAttribute("manager", $newManagerDn);
                // $adUser->save();
                
                $this->changes[] = [
                    "attribute" => "manager",
                    "old" => $currentManagerDn,
                    "new" => $newManagerDn,
                ];
            } 
			catch (\Exception $e) 
			{
                Logger::db("ad", "error", "Fehler bei beim Aktualisieren des Attributs 'Manager' des Benutzers '{$username}'", [
                    "username" => $username,
                    "error" => $e->getMessage(),
                    "actor" => $this->actor,
                ]);
            }
        }
		else 
		{
			Logger::debug("  ○ Manager bleibt unverändert");
		}
		
		Logger::debug("→ syncManager() Ende");
    }

	protected function createMutation(string $username, ?string $vorname, ?string $nachname): void
	{
		Logger::debug("→ createMutation() Start", [
			"username" => $username,
			"vorname" => $vorname ?? "(nicht gesetzt)",
			"nachname" => $nachname ?? "(nicht gesetzt)",
		]);
		
		try {
			$adUser = AdUser::where("username", $username)->first();
			
			if (!$adUser) {
				Logger::warning("AdUser '{$username}' nicht in Datenbank gefunden, Mutation konnte nicht erstellt werden");
				return;
			}
			
			$mailDomain = null;
			
			if ($adUser->email) {
				$parts = explode("@", $adUser->email);
				$mailDomain = isset($parts[1]) ? "@" . $parts[1] : null;
			}
			
			$data = [
				"vertragsbeginn" => today(),
				"ad_user_id" => $adUser->id,
				"mailendung" => $mailDomain,
				"kommentar" => "Dieser Antrag wurde automatisch erstellt.",
				"status_mail" => 1,
				"status_kis" => 1,
			];
			
			// Nur geänderte Felder hinzufügen
			if ($vorname !== null) {
				$data["vorname"] = $vorname;
			}
			
			if ($nachname !== null) {
				$data["nachname"] = $nachname;
			}
			
			Logger::debug("  Mutation-Daten:", $data);
			
			Mutation::create($data);
			
			$this->stats["mutations_created"]++;
			
			Logger::debug("  ✓ Mutation erfolgreich erstellt");
		} 
		catch (\Exception $e) {
			Logger::db("ad", "error", "Fehler beim Erstellen der Mutation für '{$username}'", [
				"username" => $username,
				"error" => $e->getMessage(),
				"actor" => $this->actor,
			]);
		}
		
		Logger::debug("→ createMutation() Ende");
	}
}