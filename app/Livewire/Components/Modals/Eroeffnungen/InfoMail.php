<?php
namespace App\Livewire\Components\Modals\Eroeffnungen;

use App\Livewire\Components\Modals\BaseModal;
use App\Models\Eroeffnung;
use App\Mail\Eroeffnungen\InfoMail as InfoMailMailable;
use Illuminate\Support\Facades\Mail;
use App\Support\SafeMail;

class InfoMail extends BaseModal
{
    public ?Eroeffnung $entry = null;
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
            $this->entry = Eroeffnung::find($payload["entryId"]);
        }

        if (!$this->entry) 
		{
            return false;
        }

        $defaultRecipients = config("ums.eroeffnung.mail.info.to", []);
        $defaultCc = config("ums.eroeffnung.mail.info.cc", []);
        $toHr = config("ums.eroeffnung.mail.info-hr.to", []);

		if ($mail = $this->entry->bezugsperson->email ?? null) 
		{
			$defaultRecipients[] = $mail;
		}
		
        // Falls KIS-Benutzer bestellt, HR zu CC hinzufügen
        if ($this->entry->status_kis == 2 && $this->entry->status_info !== 2) 
        {
            $defaultCc = array_merge($defaultCc, (array) $toHr);
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
            $recipientsList = array_map('trim', preg_split('/[,;]+/', $this->recipients));
            $recipientsList = array_filter($recipientsList);

            $ccList = [];
			
            if (!empty($this->cc)) 
			{
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
        return view("livewire.components.modals.eroeffnungen.info-mail");
    }
}