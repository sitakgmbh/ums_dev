<?php

namespace App\Livewire\Components\Modals\Eroeffnungen;

use App\Livewire\Components\Modals\BaseModal;
use App\Models\Eroeffnung;
use App\Mail\InfoMail as InfoMailMailable;
use Illuminate\Support\Facades\Mail;

class InfoMail extends BaseModal
{
    public ?Eroeffnung $entry = null;

    protected function openWith(array $payload): bool
    {
        if (isset($payload["entryId"])) 
		{
            $this->entry = Eroeffnung::find($payload["entryId"]);
        }

        $this->title      = "Info-Mail versenden";
        $this->size       = "md";
        $this->backdrop   = true;
        $this->headerBg   = "bg-primary";
        $this->headerText = "text-white";

        return (bool) $this->entry;
    }

    public function confirm(): void
    {
        if (! $this->entry) 
		{
            $this->addError("general", "Keine Eröffnung gefunden");
            return;
        }

        try 
		{
            $recipients = config("eroeffnung_tasks.eroeffnung.mail.info.to", []);
            $cc         = config("eroeffnung_tasks.eroeffnung.mail.info.cc", []);

            if (empty($recipients) && empty($cc)) 
			{
                $this->addError("general", "Keine Empfänger für Info-Mail definiert");
                return;
            }

            Mail::to($recipients)
                ->cc($cc)
                ->send(new InfoMailMailable($this->entry));

            $this->entry->update(["status_info" => 2]);
            $this->dispatch("info-updated");
            $this->closeModal();
        } 
		catch (\Exception $e) 
		{
            $this->addError("general", "Fehler beim Versand: " . $e->getMessage());
        }
    }

    public function render()
    {
        return view("livewire.components.modals.eroeffnungen.info-mail");
    }
}
