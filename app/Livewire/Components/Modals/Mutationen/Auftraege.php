<?php

namespace App\Livewire\Components\Modals\Mutationen;

use App\Livewire\Components\Modals\BaseModal;
use App\Models\Mutation;
use Illuminate\Support\Facades\Mail;

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
			"sap"    => ($this->entry->sap_rolle_id || $this->entry->sap_delete || $this->entry->komm_lei) ? "SAP" : null,
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

    switch ($key) {

        /*
        |--------------------------------------------------------------------------
        | SAP (1 Mail)
        |--------------------------------------------------------------------------
        */
        case "sap":
            $to = config("ums.mutation.mail.sap.to", []);
            $cc = config("ums.mutation.mail.sap.cc", []);

            // LEI zu CC hinzufügen
            if ($this->entry->komm_lei) {
                $cc = array_merge(
                    $cc,
                    config("ums.mutation.mail.sap_lei.to", [])
                );
            }

            $configs[] = [
                "standort" => null,
                "to"       => $to,
                "cc"       => $cc,
                "mailable" => new \App\Mail\Mutationen\AuftragSap($this->entry),
            ];
            break;


        /*
        |--------------------------------------------------------------------------
        | RAUMBESCHRIFTUNG (1 Mail basierend auf Arbeitsort)
        |--------------------------------------------------------------------------
        */
        case "raumbeschriftung":

            if ($arbeitsort === "Chur") {
                $to = config("ums.mutation.mail.raumbeschriftung_wh.to", []);
                $cc = config("ums.mutation.mail.raumbeschriftung_wh.cc", []);
            } elseif ($arbeitsort === "Cazis") {
                $to = config("ums.mutation.mail.raumbeschriftung_be.to", []);
                $cc = config("ums.mutation.mail.raumbeschriftung_be.cc", []);
            } elseif ($arbeitsort === "Rothenbrunnen") {
                $to = config("ums.mutation.mail.raumbeschriftung_rb.to", []);
                $cc = config("ums.mutation.mail.raumbeschriftung_rb.cc", []);
            } else {
                $to = [];
                $cc = [];
            }

            $configs[] = [
                "standort" => $arbeitsort,
                "to"       => $to,
                "cc"       => $cc,
                "mailable" => new \App\Mail\Mutationen\AuftragRaumbeschriftung($this->entry),
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
                "to"       => config("ums.mutation.mail.berufskleider.to", []),
                "cc"       => config("ums.mutation.mail.berufskleider.cc", []),
                "mailable" => new \App\Mail\Mutationen\AuftragBerufskleider($this->entry),
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
                "to"       => config("ums.mutation.mail.garderobe.to", []),
                "cc"       => config("ums.mutation.mail.garderobe.cc", []),
                "mailable" => new \App\Mail\Mutationen\AuftragGarderobe($this->entry),
            ];
            break;


        /*
        |--------------------------------------------------------------------------
        | ZUTRITTSRECHTE (MEHRERE Mails — je Standort)
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
            try 
			{
                $mailKey = $this->resolveMailConfig($key);

                if (! $mailKey) 
				{
                    $this->addError("general", "Kein Mail-Key für {$label} ermittelt");
                    continue;
                }

                $recipients = config("ums.mutation.mail.{$mailKey}.to", []);
                $cc         = config("ums.mutation.mail.{$mailKey}.cc", []);

				// Wenn SAP und komm_lei, dann sap_lei.to zu CC hinzufügen
				if ($key === "sap" && $this->entry->komm_lei) 
				{
					$leiRecipients = config("ums.mutation.mail.sap_lei.to", []);
					$cc = array_merge($cc, $leiRecipients);
				}

                if (empty($recipients) && empty($cc)) 
				{
                    $this->addError("general", "Keine Empfänger für {$label} definiert");
                    continue;
                }

                $mailable = match ($key) {
					"sap"   => new \App\Mail\Mutationen\AuftragSap($this->entry),
                    "raumbeschriftung"=> new \App\Mail\Mutationen\AuftragRaumbeschriftung($this->entry),
                    "berufskleider"   => new \App\Mail\Mutationen\AuftragBerufskleider($this->entry),
                    "garderobe"       => new \App\Mail\Mutationen\AuftragGarderobe($this->entry),
                    "zutrittsrechte"  => new \App\Mail\Mutationen\AuftragZutrittsrechte($this->entry),
                    default           => null,
                };

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
            } 
			catch (\Exception $e) 
			{
                logger()->error("Fehler bei {$label}: " . $e->getMessage());
                $this->addError("general", "Fehler bei {$label}: " . $e->getMessage());
            }
        }

        if (! $this->getErrorBag()->isNotEmpty()) 
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
