<?php

namespace App\Livewire\Components\Modals\Eroeffnungen;

use App\Livewire\Components\Modals\BaseModal;
use App\Models\Eroeffnung;
use Illuminate\Support\Facades\Mail;

class Auftraege extends BaseModal
{
    public ?Eroeffnung $entry = null;
    public array $pendingAuftraege = [];

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

        return true;
    }

    private function determineAuftraege(): array
    {
        return array_filter([
            "sap"    => ($this->entry->sap_rolle_id || $this->entry->is_lei) ? "SAP" : null,
            "raumbeschriftung"=> $this->entry->raumbeschriftung  ? "Raumbeschriftung"       : null,
            "berufskleider"   => $this->entry->berufskleider     ? "Berufskleider"          : null,
            "garderobe"       => $this->entry->garderobe         ? "Garderobe"              : null,
            "zutrittsrechte"  => ($this->entry->key_wh_badge || $this->entry->key_wh_schluessel ||
                                  $this->entry->key_be_badge || $this->entry->key_be_schluessel ||
                                  $this->entry->key_rb_badge || $this->entry->key_rb_schluessel
            ) ? "Zutrittsrechte" : null,
        ]);
    }

    public function confirm(): void
    {
        if (! $this->entry) {
            $this->addError("general", "Keine Eröffnung gefunden");
            return;
        }

        foreach ($this->pendingAuftraege as $key => $label) {
            try {
                // Empfänger aus Config holen (mit Standort-Logik)
                [$recipients, $cc, $mailable] = $this->resolveMailConfig($key);

                if (empty($recipients) && empty($cc)) {
                    $this->addError("general", "Keine Empfänger für {$label} definiert");
                    continue;
                }

                if (! $mailable) {
                    $this->addError("general", "Kein Mailable für {$label} gefunden");
                    continue;
                }

                logger()->info("Versand {$label}", [
                    "to"    => $recipients,
                    "cc"    => $cc,
                    "entry" => $this->entry->id,
                ]);

                Mail::to($recipients)->cc($cc)->send($mailable);

            } catch (\Exception $e) {
                logger()->error("Fehler bei {$label}: " . $e->getMessage());
                $this->addError("general", "Fehler bei {$label}: " . $e->getMessage());
            }
        }

        if (! $this->getErrorBag()->isNotEmpty()) {
            $this->entry->update(["status_auftrag" => 2]);
            $this->dispatch("auftraege-versendet");
            $this->closeModal();
        }
    }

    private function resolveMailConfig(string $key): array
    {
        $recipients = [];
        $cc = [];
        $mailable  = null;

        $arbeitsort = $this->entry->arbeitsort?->name;

        switch ($key) {
            case "sap":
                $recipients = config("ums.eroeffnung.mail.sap.to", []);
                $cc         = config("ums.eroeffnung.mail.sap.cc", []);
                $mailable   = new \App\Mail\Eroeffnungen\AuftragSap($this->entry);
                break;

            case "sap_lei":
                $recipients = config("ums.eroeffnung.mail.sap_lei.to", []);
                $cc         = config("ums.eroeffnung.mail.sap_lei.cc", []);
                $mailable   = new \App\Mail\Eroeffnungen\AuftragSapLei($this->entry);
                break;

            case "raumbeschriftung":
                if ($arbeitsort === "Chur") {
                    $recipients = config("ums.eroeffnung.mail.raumbeschriftung_wh.to", []);
                    $cc         = config("ums.eroeffnung.mail.raumbeschriftung_wh.cc", []);
                } elseif ($arbeitsort === "Cazis") {
                    $recipients = config("ums.eroeffnung.mail.raumbeschriftung_be.to", []);
                    $cc         = config("ums.eroeffnung.mail.raumbeschriftung_be.cc", []);
                } elseif ($arbeitsort === "Rothenbrunnen") {
                    $recipients = config("ums.eroeffnung.mail.raumbeschriftung_rb.to", []);
                    $cc         = config("ums.eroeffnung.mail.raumbeschriftung_rb.cc", []);
                }
                $mailable = new \App\Mail\Eroeffnungen\AuftragRaumbeschriftung($this->entry);
                break;

            case "berufskleider":
                $recipients = config("ums.eroeffnung.mail.berufskleider.to", []);
                $cc         = config("ums.eroeffnung.mail.berufskleider.cc", []);
                $mailable   = new \App\Mail\Eroeffnungen\AuftragBerufskleider($this->entry);
                break;

            case "garderobe":
                $recipients = config("ums.eroeffnung.mail.garderobe.to", []);
                $cc         = config("ums.eroeffnung.mail.garderobe.cc", []);
                $mailable   = new \App\Mail\Eroeffnungen\AuftragGarderobe($this->entry);
                break;

            case "zutrittsrechte":
                if ($arbeitsort === "Chur") {
                    $recipients = config("ums.eroeffnung.mail.zutrittsrechte_wh.to", []);
                    $cc         = config("ums.eroeffnung.mail.zutrittsrechte_wh.cc", []);
                } elseif ($arbeitsort === "Cazis") {
                    $recipients = config("ums.eroeffnung.mail.zutrittsrechte_be.to", []);
                    $cc         = config("ums.eroeffnung.mail.zutrittsrechte_be.cc", []);
                } elseif ($arbeitsort === "Rothenbrunnen") {
                    $recipients = config("ums.eroeffnung.mail.zutrittsrechte_rb.to", []);
                    $cc         = config("ums.eroeffnung.mail.zutrittsrechte_rb.cc", []);
                }
                $mailable = new \App\Mail\Eroeffnungen\AuftragZutrittsrechte($this->entry);
                break;
        }

        return [$recipients, $cc, $mailable];
    }

    public function render()
    {
        return view("livewire.components.modals.eroeffnungen.auftraege");
    }
}
