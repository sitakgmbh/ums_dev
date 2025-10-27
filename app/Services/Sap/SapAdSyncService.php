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

		$raw = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
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

			$adUser = $adUsers->first(function ($user) use ($personalnummer) {
				return $user->getFirstAttribute("initials") === $personalnummer;
			});

			if (!$adUser) 
			{
				Logger::warning("Kein AD-Benutzer zu Personalnummer {$personalnummer} gefunden");
				$this->stats["not_found"]++;
				continue;
			}		

			Logger::debug("AD-User: {$adUser->lastname} {$adUser->firstname}");

			$username = $adUser->getFirstAttribute("samaccountname");
			$this->stats["found"]++;
			
			$userChanges = [];

			$this->syncNames($adUser, $row, $username, $personalnummer);
			$this->syncDisplayNameAndUpn($adUser, $row, $username, $personalnummer);
			$this->syncSimpleAttributes($adUser, $row, $username, $personalnummer);
			$this->syncManager($adUser, $row, $username, $personalnummer);

			if (!empty($this->changes)) 
			{
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
				$this->stats["no_changes"]++;
			}
		}
	}

    protected function syncNames($adUser, $row, $username, $personalnummer): void
    {
        $vornameSAP = !empty($row["d_rufnm"]) ? trim($row["d_rufnm"]) : trim($row["d_vname"] ?? "");
        $nachnameSAP = trim($row["d_name"] ?? "");
		
		Logger::debug("Verarbeitung {$nachnameSAP} {$vornameSAP}");
		
        $vornameChanged = false;
        $nachnameChanged = false;
        
        // Vorname prüfen und anpassen
        $vornameAD = $adUser->getFirstAttribute("givenname");
        
        if ($vornameAD !== $vornameSAP && !empty($vornameSAP)) 
        {
            try 
            {
				Logger::debug("Änderung Vorname");
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

        // Nachname prüfen und anpassen
        $nachnameAD = $adUser->getFirstAttribute("sn");

        if ($nachnameAD !== $nachnameSAP && !empty($nachnameSAP)) 
        {
            try 
			{
                Logger::debug("Änderung Nachname");
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
        $vornameSAP = !empty($row["d_rufnm"]) ? trim($row["d_rufnm"]) : trim($row["d_vname"] ?? "");
        $nachnameSAP = trim($row["d_name"] ?? "");
        $displayName = trim($vornameSAP . " " . $nachnameSAP);
        $displayNameAD = $adUser->getFirstAttribute("displayname");
		
        if ($displayNameAD !== $displayName && !empty($displayName)) 
        {
            try 
			{
                Logger::debug("Änderung DisplayName");
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

        $nameAD = $adUser->getFirstAttribute("cn");
		
        if ($nameAD !== $displayName && !empty($displayName)) 
        {
            try 
			{
                Logger::debug("Benutzer umbenennen");
				// $adUser->rename($displayName);

                $currentUpn = $adUser->getFirstAttribute("userprincipalname");
                $upnDomain = explode("@", $currentUpn)[1];
                
                $cleanFirstName = UserHelper::normalize($vornameSAP);
                $cleanLastName = UserHelper::normalize($nachnameSAP);
                $newUpn = strtolower("{$cleanFirstName}.{$cleanLastName}@{$upnDomain}");
                
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
    }

    protected function syncSimpleAttributes($adUser, $row, $username, $personalnummer): void
    {
		foreach ($this->attributeMap as $adAttr => $config) 
		{
			$sapValue = trim($row[$config["sap"]] ?? "");
			$adValue = $adUser->getFirstAttribute($adAttr);

            // Nur ändern wenn SAP und AD nicht leer sind
            if (isset($config["if_sap_and_ad_not_empty"]) && $config["if_sap_and_ad_not_empty"]) 
            {
                if (empty($adValue) && empty($sapValue)) continue;
            }

            // Nur ändern wenn SAP nicht leer ist
            if (isset($config["if_sap_not_empty"]) && $config["if_sap_not_empty"]) 
            {
                if (empty($sapValue)) continue;
            }

			// Vergleich und Update
			if ($adValue !== $sapValue) 
			{
				try 
				{
					if (empty($sapValue)) 
					{
						Logger::debug("Änderung Attribut {$adValue} auf null");
						// $adUser->setFirstAttribute($adAttr, null);
					} 
					else 
					{
						Logger::debug("Änderung Attribut {$adValue} auf {$adValue}");
						// $adUser->setFirstAttribute($adAttr, $sapValue);
					}
					
					// $adUser->save();
					
					$this->changes[] = [
						"attribute" => $adAttr,
						"old" => $adValue,
						"new" => $sapValue ?: null,
					]; 
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
        }
    }

    protected function syncManager($adUser, $row, $username, $personalnummer): void
    {
        $managerPersNr = ltrim(trim($row["d_leader"] ?? ""), "0");
        
        if (empty($managerPersNr)) return;

        $managerUser = LdapUser::query()
            ->whereEquals("initials", $managerPersNr)
            ->first();

        if (!$managerUser) return;

        $currentManagerDn = $adUser->getFirstAttribute("manager");
        $newManagerDn = $managerUser->getFirstAttribute("distinguishedname");

        if ($currentManagerDn !== $newManagerDn) 
        {
            try 
			{
                Logger::debug("Änderung Manager");
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
    }

	protected function createMutation(string $username, ?string $vorname, ?string $nachname): void
	{
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
			
			Mutation::create($data);
			
			$this->stats["mutations_created"]++;
			
			Logger::debug("Mutation für '{$username}' erstellt", [
				"username" => $username,
				"vorname_neu" => $vorname,
				"nachname_neu" => $nachname,
			]);
		} 
		catch (\Exception $e) {
			Logger::db("ad", "error", "Fehler beim Erstellen der Mutation für '{$username}'", [
				"username" => $username,
				"error" => $e->getMessage(),
				"actor" => $this->actor,
			]);
		}
	}
}