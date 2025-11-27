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
    protected array $stats = [];
    protected array $changes = [];

    protected array $attributeMap = [
        "title"					=> ["sap" => "d_0032_batchbez"],
        "description"			=> ["sap" => "d_0032_batchbez"],
        "department"			=> ["sap" => "d_abt_txt"],
        "extensionAttribute2"	=> ["sap" => "d_gbdat"],
        "extensionAttribute5"	=> ["sap" => "d_0032_batchbez"],
        "extensionAttribute6"	=> ["sap" => "d_pers_txt"],
        "extensionAttribute7"	=> ["sap" => "d_zzkader"],
        "extensionAttribute8"	=> ["sap" => "d_zzbereit"],
        "extensionAttribute9"	=> ["sap" => "d_einri"],
        "extensionAttribute11"	=> ["sap" => "d_einda"],
		"extensionAttribute12"	=> ["sap" => "d_endda"],
        "extensionAttribute13"	=> ["sap" => "d_titel"],
        "extensionAttribute15"	=> ["sap" => "d_abt_nr"],
    ];

    public function sync(): void
    {
        $this->stats = [
            "found" => 0,
            "not_found" => 0,
            "updated" => 0,
            "no_changes" => 0,
            "mutations_created" => 0,
        ];

		Logger::debug("SapAdSyncService: Start");

		$this->changes = [];
     
		$sapRows = SapExport::all();

		if ($sapRows->isEmpty()) {
			throw new \RuntimeException("SapExport Tabelle ist leer – bitte zuerst SapImportService::import() ausführen.");
		}

		Logger::debug("SapAdSyncService: AD-Benutzer abfragen");

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
			'extensionattribute12',
			'extensionattribute13',
			'extensionattribute15',
		])
		->in(config("ums.ldap.ad_users_to_sync"))
		->get();
		
		Logger::debug("SapAdSyncService: Erstelle AD-User Map");
		
		$adUsersMap = [];
		
		foreach ($adUsers as $user) 
		{
			$initials = $user->getFirstAttribute("initials");
			
			if ($initials) 
			{
				$adUsersMap[$initials] = $user;
			}
		}
		
		Logger::debug("SapAdSyncService: Iteriere durch SAP-Export");

		foreach ($sapRows as $row) 
		{
			$personalnummer = ltrim(trim($row["d_pernr"] ?? ""), "0");
			
			if (empty($personalnummer)) continue;

			$adUser = $adUsersMap[$personalnummer] ?? null;

			if (!$adUser) 
			{
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
				]);
				
				$this->stats["updated"]++;
				$this->changes = [];
			} 
			else 
			{
				$this->stats["no_changes"]++;
			}
		}
		
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

			// Ohne Austrittsdatum enthält der SAP-Export '00000000' und das wollen wir nicht. Wir nullen daher das Attribut im AD.
			if ($adAttr === "extensionAttribute12" && $sapValue === "00000000") $sapValue = null;

			$sapValue = $sapValue === "" ? null : $sapValue;
			$adValue = $adUser->getFirstAttribute($adAttr);

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
			]);
		}
	}
}