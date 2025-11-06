<?php

namespace App\Services\Sap;

use App\Utils\Logging\Logger;
use App\Utils\UserHelper;
use App\Models\Setting;
use App\Models\Mutation;
use App\Models\AdUser;
use App\Models\SapExport;
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
		Logger::debug("SapAdSyncService: Start");
		
		if (!file_exists($filePath)) 
		{
			throw new \RuntimeException("SAP Export nicht gefunden: {$filePath}");
		}

		$this->changes = [];

		$content = file_get_contents($filePath);

		// Encoding erkennen und nach UTF-8 konvertieren
		$encoding = mb_detect_encoding($content, ["UTF-8", "ISO-8859-1", "Windows-1252", "ASCII"], true);

		if ($encoding && $encoding !== "UTF-8") 
		{
			$content = mb_convert_encoding($content, "UTF-8", $encoding);
		}

		$raw = explode("\n", $content);
		$raw = array_map("trim", $raw);
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

		Logger::debug("SapAdSyncService: AD-Benutzer abfragen");

		// OPTIMIERT: Nur benötigte Attribute laden
		$adUsers = LdapUser::select([
			'samaccountname',
			'initials', 
			'givenname', 
			'sn', 
			'displayname', 
			'cn', 
			'userprincipalname', 
			'distinguishedname', 
			'manager',
			'title',
			'description',
			'department',
			'extensionattribute2',
			'extensionattribute5',
			'extensionattribute6',
			'extensionattribute7',
			'extensionattribute8',
			'extensionattribute9',
			'extensionattribute11',
			'extensionattribute13',
			'extensionattribute15',
		])->get();
		
		Logger::debug("SapAdSyncService: Erstelle AD-User Map");
		
		// OPTIMIERT: AD Users in Map konvertieren für schnellen Zugriff
		$adUsersMap = [];
		foreach ($adUsers as $user) {
			$initials = $user->getFirstAttribute("initials");
			if ($initials) {
				$adUsersMap[$initials] = $user;
			}
		}
		
		// Excludes-Liste laden
		$excludes = Setting::getValue('personalnummer_abgleich_excludes', '');
		$excludeList = array_filter(array_map('trim', explode(',', $excludes)));

		Logger::debug("SapAdSyncService: Iteriere durch SAP-Export");

		foreach ($rows as $row) 
		{
			$personalnummer = ltrim(trim($row["d_pernr"] ?? ""), "0");
			if (empty($personalnummer)) continue;
			
			// Eintrittsdatum prüfen
			$eintrittsdatum = trim($row["d_einda"] ?? "");
			if (!empty($eintrittsdatum)) {
				try {
					$eintritt = \Carbon\Carbon::createFromFormat('Ymd', $eintrittsdatum)->startOfDay();
					
					if ($eintritt->isFuture()) 
					{
						// Logger::debug("Personalnummer {$personalnummer} übersprungen: Eintrittsdatum {$eintritt->format('d.m.Y')} liegt in der Zukunft");
						continue;
					}
				} catch (\Exception $e) {
					// Ungültiges Datum ignorieren
				}
			}

			// OPTIMIERT: Lookup in Map statt Collection durchsuchen
			$adUser = $adUsersMap[$personalnummer] ?? null;

			if (!$adUser) 
			{
				if (!in_array($personalnummer, $excludeList)) 
				{
					// Logger::warning("Kein AD-Benutzer zu Personalnummer {$personalnummer} gefunden");
				}
				
				$this->stats["not_found"]++;
				continue;
			}

			$username = $adUser->getFirstAttribute("samaccountname");

			$this->stats["found"]++;
			
			$this->syncNames($adUser, $row, $username, $personalnummer);
			$this->syncDisplayNameAndUpn($adUser, $row, $username, $personalnummer);
			$this->syncSimpleAttributes($adUser, $row, $username, $personalnummer);
			$this->syncManager($adUser, $row, $username, $personalnummer, $adUsersMap);

			if (!empty($this->changes)) 
			{
				Logger::db("sap", "info", "Benutzer '{$username}' aktualisiert", [
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
				$this->stats["no_changes"]++;
			}
		}
		
		Logger::debug("SapAdSyncService: Start Personalnummerabgleich");
		
		$this->syncMissingInitials($adUsers, $rows);
		
		Logger::debug("SapAdSyncService: Ende");
	}

    protected function syncNames($adUser, $row, $username, $personalnummer): void
    {
        $vornameSAP = !empty($row["d_rufnm"]) ? trim($row["d_rufnm"]) : trim($row["d_vname"] ?? "");
        $nachnameSAP = trim($row["d_name"] ?? "");
		
        $vornameChanged = false;
        $nachnameChanged = false;
        
        // Vorname prüfen und anpassen
        $vornameAD = $adUser->getFirstAttribute("givenname");
		
        if ($vornameAD !== $vornameSAP && !empty($vornameSAP)) 
        {
            try 
            {
				$adUser->setFirstAttribute("givenname", $vornameSAP);
                $adUser->save();
                
                $this->changes[] = [
                    "attribute" => "givenname",
                    "old" => $vornameAD,
                    "new" => $vornameSAP,
                ];
                
                $vornameChanged = true;
            } 
            catch (\Exception $e) 
            {
                Logger::db("sap", "error", "Fehler beim Aktualisieren des Vornamens des Benutzers '{$username}'", [
                    "username" => $username,
                    "error" => $e->getMessage(),
                    "actor" => $this->actor,
                ]);
            }
        }

        // Nachname prüfen und anpassen
        $nachnameAD = $adUser->getFirstAttribute("sn");

        if ($nachnameAD !== $nachnameSAP && !empty($nachnameSAP)) 
        {
            try 
			{
				$adUser->setFirstAttribute("sn", $nachnameSAP);
                $adUser->save();
                
                $this->changes[] = [
                    "attribute" => "sn",
                    "old" => $nachnameAD,
                    "new" => $nachnameSAP,
                ];
                
                $nachnameChanged = true;
            } 
			catch (\Exception $e) 
			{
                Logger::db("sap", "error", "Fehler beim Aktualisieren des Nachnamens des Benutzers '{$username}'", [
                    "username" => $username,
                    "error" => $e->getMessage(),
                    "actor" => $this->actor,
                ]);
            }
        }
        
        // Mutation erstellen wenn mindestens ein Name geändert wurde
		if ($vornameChanged || $nachnameChanged) 
		{
			$this->createMutation(
				$username, 
				$vornameChanged ? $vornameSAP : null, 
				$nachnameChanged ? $nachnameSAP : null
			);
		}
    }

	protected function syncDisplayNameAndUpn($adUser, $row, $username, $personalnummer): void
	{
		$vornameSAP = !empty($row["d_rufnm"]) ? trim($row["d_rufnm"]) : trim($row["d_vname"] ?? "");
		$nachnameSAP = trim($row["d_name"] ?? "");
		$displayName = trim($nachnameSAP . " " . $vornameSAP);
		$displayNameAD = $adUser->getFirstAttribute("displayname");
		$newCN = trim($nachnameSAP . " " . $vornameSAP . " / " . $username);
		
		if ($displayNameAD !== $displayName && !empty($displayName)) 
		{
			try 
			{
				$adUser->setFirstAttribute("displayname", $displayName);
				$adUser->save();
				
				$this->changes[] = [
					"attribute" => "displayname",
					"old" => $displayNameAD,
					"new" => $displayName,
				];
			} 
			catch (\Exception $e) 
			{
				Logger::db("sap", "error", "Fehler beim Aktualisieren des Anzeigenamens des Benutzers '{$username}'", [
					"username" => $username,
					"error" => $e->getMessage(),
					"actor" => $this->actor,
				]);
			}
		}

		$nameAD = $adUser->getFirstAttribute("cn");
		
		if ($nameAD !== $newCN && !empty($newCN)) 
		{
			try 
			{
				$currentUpn = $adUser->getFirstAttribute("userprincipalname");
				$upnDomain = explode("@", $currentUpn)[1];
				
				$cleanFirstName = UserHelper::normalize($vornameSAP);
				$cleanLastName = UserHelper::normalize($nachnameSAP);
				$newUpn = strtolower("{$cleanFirstName}.{$cleanLastName}@{$upnDomain}");
				
				$adUser->rename($newCN);
				$adUser->setFirstAttribute("userprincipalname", $newUpn);
				$adUser->save();

				$this->changes[] = [
					"attribute" => "cn",
					"old" => $nameAD,
					"new" => $newCN,
				];
				
				$this->changes[] = [
					"attribute" => "userprincipalname",
					"old" => $currentUpn,
					"new" => $newUpn,
				];
			} 
			catch (\Exception $e) 
			{
				Logger::db("sap", "error", "Fehler beim Umbenennen oder Aktualisieren des UPNs des Benutzers '{$username}'", [
					"username" => $username,
					"error" => $e->getMessage(),
					"actor" => $this->actor,
				]);
			}
		}
	}

    protected function syncSimpleAttributes($adUser, $row, $username, $personalnummer): void
    {
		$attributeChanges = 0;
		
		foreach ($this->attributeMap as $adAttr => $config) 
		{
			$sapField = $config["sap"];
			$sapValue = trim($row[$sapField] ?? "");
			$sapValue = $sapValue === "" ? null : $sapValue;
			$adValue = $adUser->getFirstAttribute($adAttr);

            // Nur ändern wenn SAP und AD nicht leer sind
            if (isset($config["if_sap_and_ad_not_empty"]) && $config["if_sap_and_ad_not_empty"]) 
            {
                if (empty($adValue) && empty($sapValue)) 
				{
					continue;
				}
            }

            // Nur ändern wenn SAP nicht leer ist
            if (isset($config["if_sap_not_empty"]) && $config["if_sap_not_empty"]) 
            {
                if (empty($sapValue)) 
				{
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
						$adUser->setFirstAttribute($adAttr, null);
					} 
					else 
					{
						$adUser->setFirstAttribute($adAttr, $sapValue);
					}
					
					$adUser->save();
					
					$this->changes[] = [
						"attribute" => $adAttr,
						"old" => $adValue,
						"new" => $sapValue ?: null,
					];
					
					$attributeChanges++;
                } 
				catch (\Exception $e) 
				{
					Logger::db("sap", "error", "Fehler bei beim Aktualisieren des Attributs '{$adAttr}' des Benutzers '{$username}'", [
                        "username" => $username,
                        "error" => $e->getMessage(),
                        "actor" => $this->actor,
                    ]);
                }
			}
        }
    }

    protected function syncManager($adUser, $row, $username, $personalnummer, $adUsersMap): void
    {
        $managerPersNr = ltrim(trim($row["d_leader"] ?? ""), "0");
        
        if (empty($managerPersNr)) 
		{
			return;
		}

		// OPTIMIERT: Lookup in Map statt LDAP Query
        $managerUser = $adUsersMap[$managerPersNr] ?? null;

        if (!$managerUser) 
		{
			return;
		}

        $currentManagerDn = $adUser->getFirstAttribute("manager");
        $newManagerDn = $managerUser->getFirstAttribute("distinguishedname");

        if ($currentManagerDn !== $newManagerDn) 
        {
            try 
			{
				$adUser->setFirstAttribute("manager", $newManagerDn);
                $adUser->save();
                
                $this->changes[] = [
                    "attribute" => "manager",
                    "old" => $currentManagerDn,
                    "new" => $newManagerDn,
                ];
            } 
			catch (\Exception $e) 
			{
                Logger::db("sap", "error", "Fehler bei beim Aktualisieren des Attributs 'Manager' des Benutzers '{$username}'", [
                    "username" => $username,
                    "error" => $e->getMessage(),
                    "actor" => $this->actor,
                ]);
            }
        }
    }

	protected function createMutation(string $username, ?string $vorname, ?string $nachname): void
	{
		try 
		{
			$adUser = AdUser::where("username", $username)->first();
			
			if (!$adUser) 
			{
				Logger::warning("AdUser '{$username}' nicht in Datenbank gefunden, Mutation konnte nicht erstellt werden");
				return;
			}
			
			$mailDomain = null;
			
			if ($adUser->email) 
			{
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
			if ($vorname !== null) 
			{
				$data["vorname"] = $vorname;
			}
			
			if ($nachname !== null) 
			{
				$data["nachname"] = $nachname;
			}
			
			Mutation::create($data);
			
			$this->stats["mutations_created"]++;
		} 
		catch (\Exception $e) 
		{
			Logger::db("sap", "error", "Fehler beim Erstellen der Mutation für '{$username}'", [
				"username" => $username,
				"error" => $e->getMessage(),
				"actor" => $this->actor,
			]);
		}
	}

	protected function syncMissingInitials($adUsers, $rows): void
	{
		// Alle AD-Benutzer mit initials = 99999 finden
		$usersWithoutInitials = $adUsers->filter(function ($user) {
			return $user->getFirstAttribute("initials") === "99999";
		});
		
		foreach ($usersWithoutInitials as $adUser) {
			$username = $adUser->getFirstAttribute("samaccountname");
			$vornameAD = $adUser->getFirstAttribute("givenname");
			$nachnameAD = $adUser->getFirstAttribute("sn");
			$descriptionAD = $adUser->getFirstAttribute("description");
			
			// Im SAP Export nach passendem Eintrag suchen
			foreach ($rows as $row) 
			{
				$vornameSAP = !empty($row["d_rufnm"]) ? trim($row["d_rufnm"]) : trim($row["d_vname"] ?? "");
				$nachnameSAP = trim($row["d_name"] ?? "");
				$batchbezSAP = trim($row["d_0032_batchbez"] ?? "");
				
				// Prüfen ob Vorname, Nachname und Description übereinstimmen
				if ($vornameAD === $vornameSAP && $nachnameAD === $nachnameSAP && $descriptionAD === $batchbezSAP) 
				{
					$personalnummer = ltrim(trim($row["d_pernr"] ?? ""), "0");
					
					try 
					{
						// Personalnummer im AD setzen
						$adUser->setFirstAttribute("initials", $personalnummer);
						$adUser->save();
						
						Logger::db("sap", "info", "Personalnummer für Benutzer '{$username}' gesetzt", [
							"username" => $username,
							"alte_initials" => "99999",
							"neue_initials" => $personalnummer,
							"matched_by" => [
								"vorname" => $vornameSAP,
								"nachname" => $nachnameSAP,
								"description" => $batchbezSAP,
							],
							"actor" => $this->actor,
						]);
						
						$this->stats["initials_updated"] = ($this->stats["initials_updated"] ?? 0) + 1;
						
						// Nach erstem Match abbrechen
						break;
					} 
					catch (\Exception $e) 
					{
						Logger::db("sap", "error", "Fehler beim Setzen der Personalnummer für Benutzer '{$username}'", [
							"username" => $username,
							"personalnummer" => $personalnummer,
							"error" => $e->getMessage(),
							"actor" => $this->actor,
						]);
					}
				}
			}
		}
	}

}