<?php

namespace App\Livewire\Components\Modals\Mutationen;

use App\Livewire\Components\Modals\BaseModal;
use App\Models\Mutation;
use App\Mail\Mutationen\InfoMail as InfoMailMailable;
use Illuminate\Support\Facades\Mail;
use App\Support\SafeMail;

class InfoMail extends BaseModal
{
    public ?Mutation $entry = null;

    protected function openWith(array $payload): bool
    {
        if (isset($payload["entryId"])) 
		{
            $this->entry = Mutation::find($payload["entryId"]);
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
            $recipients = config("ums.mutation.mail.info.to", []);
            $cc         = config("ums.mutation.mail.info.cc", []);

            if (empty($recipients) && empty($cc)) 
			{
                $this->addError("general", "Keine Empfänger für Info-Mail definiert");
                return;
            }

            SafeMail::send(new InfoMailMailable($this->entry), $recipients, $cc);

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
        return view("livewire.components.modals.mutationen.info-mail");
    }
}
