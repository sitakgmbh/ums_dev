<?php

namespace App\Livewire\Components\Modals\Mutationen;

use App\Livewire\Components\Modals\BaseModal;
use App\Models\Mutation;
use Illuminate\Support\Facades\Mail;
use App\Support\SafeMail;
use Carbon\Carbon;
use LdapRecord\Models\ActiveDirectory\User as AdUser;
use App\Utils\LdapHelper;
use App\Utils\Logging\Logger;

class Auftraege extends BaseModal
{
    public ?Mutation $entry = null;
    public array $pendingAuftraege = [];
	public array $auftraegeDetails = [];

    protected function openWith(array $payload): bool
    {
        if (! isset($payload["entryId"])) 
		{
            return false;
        }

        $this->entry = Mutation::find($payload["entryId"]);

        if (! $this->entry) 
		{
            return false;
        }

        $this->title      = "Aufträge versenden";
        $this->size       = "md";
        $this->position   = "centered";
        $this->backdrop   = true;
        $this->headerBg   = "bg-primary";
        $this->headerText = "text-white";

        $this->pendingAuftraege = $this->determineAuftraege();
		$this->auftraegeDetails = $this->getAuftraegeDetails();

        return true;
    }

	private function determineAuftraege(): array
	{
		return array_filter([
			"sap"    => ($this->entry->sap_rolle_id || $this->entry->sap_delete) ? "SAP-Benutzer" : null,
			"lei" => ($this->entry->is_lei) ? "Info Leistungserbringer" : null,
			"raumbeschriftung"=> $this->entry->raumbeschriftung ? "Raumbeschriftung" : null,
			"berufskleider"   => $this->entry->berufskleider    ? "Berufskleider"    : null,
			"garderobe"       => $this->entry->garderobe        ? "Garderobe"        : null,
			"zutrittsrechte"  => ($this->entry->key_wh_badge || $this->entry->key_wh_schluessel ||
								   $this->entry->key_be_badge || $this->entry->key_be_schluessel ||
								   $this->entry->key_rb_badge || $this->entry->key_rb_schluessel
			) ? "Zutrittsrechte" : null,
		]);
	}

	private function getAuftraegeDetails(): array
	{
		$details = [];

		foreach ($this->pendingAuftraege as $key => $label) {

			$configs = $this->resolveMailConfig($key);

			$details[$key] = [];

			foreach ($configs as $conf) {
				$details[$key][] = [
					"label"    => $label,
					"standort" => $conf["standort"] ?? null,
					"to"       => $conf["to"] ?? [],
					"cc"       => $conf["cc"] ?? [],
				];
			}
		}

		return $details;
	}

	private function resolveMailKey(string $baseKey): ?string
	{
		$ort = $this->entry->arbeitsort?->name;

		if (! $ort) 
		{
			return null;
		}

		return match ($baseKey) {
			"raumbeschriftung" => match ($ort) {
				"Chur"                     => "raumbeschriftung_wh",
				"Cazis", "Rothenbrunnen"   => "raumbeschriftung_be",
				default                    => "raumbeschriftung_rb",
			},
			"zutrittsrechte" => match ($ort) {
				"Chur"                     => "zutrittsrechte_wh",
				"Cazis", "Rothenbrunnen"   => "zutrittsrechte_be",
				default                    => "zutrittsrechte_rb",
			},
			default => $baseKey,
		};
	}

	private function resolveMailConfig(string $key): array
	{
		$configs = [];

		$arbeitsort = $this->entry->arbeitsort?->name;

		switch ($key) 
		{
			case "sap":
				$configs[] = [
					"standort" => null,
					"to"       => config("ums.mutation.mail.sap.to", []),
					"cc"       => config("ums.mutation.mail.sap.cc", []),
					"mailable" => new \App\Mail\Mutationen\AuftragSap($this->entry),
				];
				break;

			case "lei":
				$configs[] = [
					"standort" => null,
					"to"       => config("ums.mutation.mail.sap_lei.to", []),
					"cc"       => config("ums.mutation.mail.sap_lei.cc", []),
					"mailable" => new \App\Mail\Mutationen\AuftragLei($this->entry),
				];
				break;

			case "raumbeschriftung":

				if ($arbeitsort === "Chur") 
				{
					$to = config("ums.mutation.mail.raumbeschriftung_wh.to", []);
					$cc = config("ums.mutation.mail.raumbeschriftung_wh.cc", []);
				} 
				elseif ($arbeitsort === "Cazis") 
				{
					$to = config("ums.mutation.mail.raumbeschriftung_be.to", []);
					$cc = config("ums.mutation.mail.raumbeschriftung_be.cc", []);
				} 
				elseif ($arbeitsort === "Rothenbrunnen") 
				{
					$to = config("ums.mutation.mail.raumbeschriftung_rb.to", []);
					$cc = config("ums.mutation.mail.raumbeschriftung_rb.cc", []);
				} 
				else 
				{
					$to = config("ums.mutation.mail.raumbeschriftung_wh.to", []); // Fallback
					$cc = config("ums.mutation.mail.raumbeschriftung_wh.cc", []); // Fallback
				}

				$configs[] = [
					"standort" => $arbeitsort,
					"to"       => $to,
					"cc"       => $cc,
					"mailable" => new \App\Mail\Mutationen\AuftragRaumbeschriftung($this->entry),
				];
				
				break;

			case "berufskleider":

				$configs[] = [
					"standort" => null,
					"to"       => config("ums.mutation.mail.berufskleider.to", []),
					"cc"       => config("ums.mutation.mail.berufskleider.cc", []),
					"mailable" => new \App\Mail\Mutationen\AuftragBerufskleider($this->entry),
				];
				
				break;

			case "garderobe":

				$configs[] = [
					"standort" => null,
					"to"       => config("ums.mutation.mail.garderobe.to", []),
					"cc"       => config("ums.mutation.mail.garderobe.cc", []),
					"mailable" => new \App\Mail\Mutationen\AuftragGarderobe($this->entry),
				];
				
				break;

			case "zutrittsrechte":

				$standorte = [];

				if ($this->entry->key_wh_badge || $this->entry->key_wh_schluessel) 
				{
					$standorte[] = "Chur";
				}
				
				if ($this->entry->key_be_badge || $this->entry->key_be_schluessel) 
				{
					$standorte[] = "Cazis";
				}
				
				if ($this->entry->key_rb_badge || $this->entry->key_rb_schluessel) 
				{
					$standorte[] = "Rothenbrunnen";
				}

				foreach ($standorte as $ort) 
				{
					switch ($ort) 
					{
						case "Chur":
							$to = config("ums.mutation.mail.zutrittsrechte_wh.to", []);
							$cc = config("ums.mutation.mail.zutrittsrechte_wh.cc", []);
							break;

						case "Cazis":
							$to = config("ums.mutation.mail.zutrittsrechte_be.to", []);
							$cc = config("ums.mutation.mail.zutrittsrechte_be.cc", []);
							break;

						case "Rothenbrunnen":
							$to = config("ums.mutation.mail.zutrittsrechte_rb.to", []);
							$cc = config("ums.mutation.mail.zutrittsrechte_rb.cc", []);
							break;

						default:
							continue 2;
					}

					$configs[] = [
						"standort" => $ort,
						"to"       => $to,
						"cc"       => $cc,
						"mailable" => new \App\Mail\Mutationen\AuftragZutrittsrechte($this->entry, $ort),
					];
				}
				
			break;
		}

		return $configs;
	}

	public function confirm(): void
	{
		if (! $this->entry) 
		{
			$this->addError("general", "Keine Mutation gefunden");
			return;
		}

		foreach ($this->pendingAuftraege as $key => $label) 
		{
			$configs = $this->resolveMailConfig($key);

			foreach ($configs as $conf) 
			{

				$recipients = $conf["to"] ?? [];
				$cc         = $conf["cc"] ?? [];
				$mailable   = $conf["mailable"] ?? null;

				if ($key === "sap" && $this->entry->komm_lei) 
				{
					$cc = array_merge(
						$cc,
						config("ums.mutation.mail.sap_lei.to", [])
					);
				}

				if (empty($recipients) && empty($cc)) 
				{
					$this->addError("general", "Keine Empfänger für {$label} definiert");
					continue;
				}

				if (! $mailable) 
				{
					$this->addError("general", "Kein Mailable für {$label} gefunden");
					continue;
				}

				/*
				logger()->info("Versand {$label}", [
					"to"    => $recipients,
					"cc"    => $cc,
					"entry" => $this->entry->id,
				]);
				*/

				SafeMail::send($mailable, $recipients, $cc);

				try 
				{
					$username = $this->entry->adUser->username;
					$date = Carbon::now()->format("Ymd");
					LdapHelper::setAdAttribute($username, "extensionAttribute4", $date);
				} 
				catch (\Throwable $e) 
				{
					Logger::error("Fehler beim Setzen extensionAttribute4: " . $e->getMessage());
				}
			}
		}

		if (!$this->getErrorBag()->isNotEmpty()) 
		{
			$this->entry->update(["status_auftrag" => 2]);
			$this->dispatch("auftraege-versendet");
			$this->closeModal();
		}
	}

    public function render()
    {
        return view("livewire.components.modals.mutationen.auftraege");
    }
}
