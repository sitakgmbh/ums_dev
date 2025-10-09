<?php

namespace App\Livewire\Components\Modals\Eroeffnungen;

use App\Livewire\Components\Modals\BaseModal;

class EmailBearbeiten extends BaseModal
{
    public string $email = "";

    protected function openWith(array $payload): bool
    {
        $this->title      = "Vorläufige E-Mail-Adresse";
        $this->size       = "md";
        $this->backdrop   = true;
        $this->position   = "centered";
        $this->scrollable = false;
        $this->headerBg   = "bg-warning";
        $this->headerText = "text-white";

        if (!empty($payload["email"])) 
		{
            $this->email = $payload["email"];
        }

        return true;
    }

    public function confirm(): void
    {
        $this->dispatch("email-bearbeiten-selected", [
            "email" => $this->email,
        ]);

        $this->closeModal();
    }

    public function render()
    {
        return view("livewire.components.modals.eroeffnungen.email-bearbeiten");
    }
}
