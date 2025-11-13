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
    public string $recipients = '';
    public string $cc = '';

    protected function rules()
    {
        return [
            'recipients' => 'required|string',
            'cc' => 'nullable|string',
        ];
    }

    protected function messages()
    {
        return [
            'recipients.required' => 'Bitte gib mindestens einen Empfänger an.',
        ];
    }

    protected function openWith(array $payload): bool
    {
        if (isset($payload["entryId"])) 
        {
            $this->entry = Mutation::find($payload["entryId"]);
        }

        if (!$this->entry) {
            return false;
        }

        // Vorausfüllen mit Config-Werten
        $defaultRecipients = config("ums.mutation.mail.info.to", []);
        $defaultCc = config("ums.mutation.mail.info.cc", []);

		if ($antragstellerMail = $this->entry->antragsteller->email ?? null) 
		{
			$defaultRecipients[] = $antragstellerMail;
		}

		if ($aduserMail = $this->entry->adUser->email ?? null) 
		{
			$defaultCc[] = $aduserMail;
		}

        $this->recipients = implode(', ', $defaultRecipients);
        $this->cc = implode(', ', array_unique($defaultCc));

        $this->title      = "Info-Mail versenden";
        $this->size       = "md";
        $this->backdrop   = true;
        $this->headerBg   = "bg-primary";
        $this->headerText = "text-white";

        return true;
    }

    public function confirm(): void
    {
        $this->validate();

        if (!$this->entry) 
        {
            $this->addError("general", "Keine Eröffnung gefunden");
            return;
        }

        try 
        {
            // Parse Empfänger (Komma- oder Semikolon-getrennt)
            $recipientsList = array_map('trim', preg_split('/[,;]+/', $this->recipients));
            $recipientsList = array_filter($recipientsList);

            $ccList = [];
            if (!empty($this->cc)) {
                $ccList = array_map('trim', preg_split('/[,;]+/', $this->cc));
                $ccList = array_filter($ccList);
            }

            if (empty($recipientsList)) 
            {
                $this->addError("recipients", "Keine gültigen Empfänger angegeben");
                return;
            }

            SafeMail::send(new InfoMailMailable($this->entry), $recipientsList, $ccList);

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