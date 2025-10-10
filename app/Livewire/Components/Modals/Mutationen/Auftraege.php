<?php

namespace App\Livewire\Components\Modals\Mutationen;

use App\Livewire\Components\Modals\BaseModal;
use App\Models\Mutation;
use Illuminate\Support\Facades\Mail;

class Auftraege extends BaseModal
{
    public ?Mutation $entry = null;
    public array $pendingAuftraege = [];

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
                $mailKey = $this->resolveMailKey($key);

                if (! $mailKey) 
				{
                    $this->addError("general", "Kein Mail-Key für {$label} ermittelt");
                    continue;
                }

                $recipients = config("ums.mutation.mail.{$mailKey}.to", []);
                $cc         = config("ums.mutation.mail.{$mailKey}.cc", []);

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

                Mail::to($recipients)->cc($cc)->send($mailable);
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
