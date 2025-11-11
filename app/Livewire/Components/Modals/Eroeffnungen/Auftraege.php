<?php

namespace App\Livewire\Components\Modals\Eroeffnungen;

use App\Livewire\Components\Modals\BaseModal;
use App\Models\Eroeffnung;
use Illuminate\Support\Facades\Mail;
use App\Support\SafeMail;
use Carbon\Carbon;
use LdapRecord\Models\ActiveDirectory\User as AdUser;
use App\Utils\LdapHelper;
use App\Utils\Logging\Logger;

class Auftraege extends BaseModal
{
    public ?Eroeffnung $entry = null;
    public array $pendingAuftraege = [];
	public array $auftraegeDetails = [];

    protected function openWith(array $payload): bool
    {
        if (! isset($payload["entryId"])) {
            return false;
        }

        $this->entry = Eroeffnung::find($payload["entryId"]);
        if (! $this->entry) {
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
			"sap" => ($this->entry->sap_rolle_id) ? "SAP" : null,
			"raumbeschriftung"=> $this->entry->raumbeschriftung  ? "Raumbeschriftung" : null,
			"berufskleider"   => $this->entry->berufskleider     ? "Berufskleider"    : null,
			"garderobe"       => $this->entry->garderobe         ? "Garderobe"        : null,
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


	private function resolveMailConfig(string $key): array
	{
		// Standard: genau 1 Mail-Konfiguration
		$configs = [];

		$arbeitsort = $this->entry->arbeitsort?->name;

		switch ($key) {

			/*
			|--------------------------------------------------------------------------
			| SAP (1 Mail)
			|--------------------------------------------------------------------------
			*/
			case "sap":
				$to = config("ums.eroeffnung.mail.sap.to", []);
				$cc = config("ums.eroeffnung.mail.sap.cc", []);

				if ($this->entry->is_lei) {
					$cc = array_merge(
						$cc,
						config("ums.eroeffnung.mail.sap_lei.to", [])
					);
				}

				$configs[] = [
					"standort" => null,
					"to"       => $to,
					"cc"       => $cc,
					"mailable" => new \App\Mail\Eroeffnungen\AuftragSap($this->entry),
				];
				
				break;


			/*
			|--------------------------------------------------------------------------
			| RAUMBESCHRIFTUNG (1 Mail basierend auf arbeitsort)
			|--------------------------------------------------------------------------
			*/
			case "raumbeschriftung":

				if ($arbeitsort === "Chur") {
					$to = config("ums.eroeffnung.mail.raumbeschriftung_wh.to", []);
					$cc = config("ums.eroeffnung.mail.raumbeschriftung_wh.cc", []);
				} elseif ($arbeitsort === "Cazis") {
					$to = config("ums.eroeffnung.mail.raumbeschriftung_be.to", []);
					$cc = config("ums.eroeffnung.mail.raumbeschriftung_be.cc", []);
				} elseif ($arbeitsort === "Rothenbrunnen") {
					$to = config("ums.eroeffnung.mail.raumbeschriftung_rb.to", []);
					$cc = config("ums.eroeffnung.mail.raumbeschriftung_rb.cc", []);
				} else {
					$to = [];
					$cc = [];
				}

				$configs[] = [
					"standort" => $arbeitsort,
					"to"       => $to,
					"cc"       => $cc,
					"mailable" => new \App\Mail\Eroeffnungen\AuftragRaumbeschriftung($this->entry),
				];
				break;


			/*
			|--------------------------------------------------------------------------
			| BERUFSKLEIDER (1 Mail)
			|--------------------------------------------------------------------------
			*/
			case "berufskleider":

				$configs[] = [
					"standort" => null,
					"to"       => config("ums.eroeffnung.mail.berufskleider.to", []),
					"cc"       => config("ums.eroeffnung.mail.berufskleider.cc", []),
					"mailable" => new \App\Mail\Eroeffnungen\AuftragBerufskleider($this->entry),
				];
				break;


			/*
			|--------------------------------------------------------------------------
			| GARDEROBE (1 Mail)
			|--------------------------------------------------------------------------
			*/
			case "garderobe":

				$configs[] = [
					"standort" => null,
					"to"       => config("ums.eroeffnung.mail.garderobe.to", []),
					"cc"       => config("ums.eroeffnung.mail.garderobe.cc", []),
					"mailable" => new \App\Mail\Eroeffnungen\AuftragGarderobe($this->entry),
				];
				break;


			/*
			|--------------------------------------------------------------------------
			| ZUTRITTSRECHTE (MEHRERE Mails – je Standort)
			|--------------------------------------------------------------------------
			*/
			case "zutrittsrechte":

				$standorte = [];

				if ($this->entry->key_wh_badge || $this->entry->key_wh_schluessel) {
					$standorte[] = "Chur";
				}
				if ($this->entry->key_be_badge || $this->entry->key_be_schluessel) {
					$standorte[] = "Cazis";
				}
				if ($this->entry->key_rb_badge || $this->entry->key_rb_schluessel) {
					$standorte[] = "Rothenbrunnen";
				}

				foreach ($standorte as $ort) {

					switch ($ort) {
						case "Chur":
							$to = config("ums.eroeffnung.mail.zutrittsrechte_wh.to", []);
							$cc = config("ums.eroeffnung.mail.zutrittsrechte_wh.cc", []);
							break;

						case "Cazis":
							$to = config("ums.eroeffnung.mail.zutrittsrechte_be.to", []);
							$cc = config("ums.eroeffnung.mail.zutrittsrechte_be.cc", []);
							break;

						case "Rothenbrunnen":
							$to = config("ums.eroeffnung.mail.zutrittsrechte_rb.to", []);
							$cc = config("ums.eroeffnung.mail.zutrittsrechte_rb.cc", []);
							break;

						default:
							continue 2;
					}

					$configs[] = [
						"standort" => $ort,
						"to"       => $to,
						"cc"       => $cc,
						"mailable" => new \App\Mail\Eroeffnungen\AuftragZutrittsrechte($this->entry, $ort),
					];
				}

				break;
		}

		return $configs;
	}

	public function confirm(): void
	{
		if (! $this->entry) {
			$this->addError("general", "Keine Eröffnung gefunden");
			return;
		}

		foreach ($this->pendingAuftraege as $key => $label) 
		{
			try 
			{
				[$recipients, $cc, $mailable] = $this->resolveMailConfig($key);

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

				logger()->info("Versand {$label}", [
					"to"    => $recipients,
					"cc"    => $cc,
					"entry" => $this->entry->id,
				]);

				SafeMail::send($mailable, $recipients, $cc);

				try 
				{
					$username = $this->entry->benutzername;
					$date = Carbon::now()->format("Ymd");
					LdapHelper::setAdAttribute($username, "extensionAttribute4", $date);
				} 
				catch (\Throwable $e) 
				{
					Logger::error("Fehler beim Setzen extensionAttribute4: " . $e->getMessage());
				}
			} 
			catch (\Throwable $e) 
			{
				logger()->error("Fehler bei {$label}: " . $e->getMessage(), [
					"trace" => $e->getTraceAsString(),
				]);
				
				$this->addError("general", "Fehler bei {$label}: " . $e->getMessage());
			}
		}

		// Wenn keine Fehler Status aktualisieren
		if (! $this->getErrorBag()->isNotEmpty()) 
		{
			$this->entry->update(["status_auftrag" => 2]);
			$this->dispatch("auftraege-versendet");
			$this->closeModal();
		}
	}

    public function render()
    {
        return view("livewire.components.modals.eroeffnungen.auftraege");
    }
}
