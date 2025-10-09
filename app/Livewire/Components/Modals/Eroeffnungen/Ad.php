<?php

namespace App\Livewire\Components\Modals\Eroeffnungen;

use App\Livewire\Components\Modals\BaseModal;
use App\Models\Eroeffnung;
use App\Utils\UserHelper;

class Ad extends BaseModal
{
    public ?Eroeffnung $entry = null;

    public string $username = "";
    public string $email = "";
    public string $infoText = "";
    public bool $usernameReadonly = false;

    protected function openWith(array $payload): bool
    {
        if (! isset($payload["entryId"])) 
		{
            return false;
        }

        $this->entry = Eroeffnung::find($payload["entryId"]);

        if (! $this->entry) 
		{
            return false;
        }

        if ($this->entry->wiedereintritt) 
		{
            // Wiedereintritt: Username fix, E-Mail aus DB
            $this->username = $this->entry->benutzername ?? "";
            $this->email    = $this->entry->email ?? "";
            $this->usernameReadonly = true;
            $this->infoText = "Es handelt sich um einen Wiedereintritt. "
                . "Der Benutzername kann daher nicht angepasst werden. "
                . "Du kannst die E-Mail-Adresse optional vor dem Erstellen des AD-Benutzers anpassen. "
                . "Die vorhandenen AD-Gruppenmitgliedschaften werden überschrieben.";
        } 
		else 
		{
			$data = $this->entry->toArray();

			$username = UserHelper::generateUsername($data["vorname"], $data["nachname"], $this->entry->id);
			$email    = UserHelper::generateEmail($data["vorname"], $data["nachname"], $data["mailendung"], $username, $this->entry->id);

			$this->username = $username;
			$this->email    = $email;

            $this->usernameReadonly = false;
            $this->infoText = "Benutzername und E-Mail-Adresse wurden neu generiert "
                . "und können von den Informationen im Antrag abweichen. "
                . "Du kannst beide Werte optional vor dem Erstellen des AD-Benutzers anpassen.";
        }

        $this->title      = "AD-Benutzer erstellen";
        $this->size       = "md";
        $this->position   = "centered";
        $this->backdrop   = true;
        $this->headerBg   = "bg-primary";
        $this->headerText = "text-white";

        return true;
    }

    public function confirm(): void
    {
        if ($this->entry) 
		{
            $this->entry->update([
                "benutzername" => $this->username,
                "email"        => $this->email,
                "status_ad"    => 3,
            ]);
        }

        $this->dispatch("ad-updated");
        $this->closeModal();
    }

    public function render()
    {
        return view("livewire.components.modals.eroeffnungen.ad");
    }
}
