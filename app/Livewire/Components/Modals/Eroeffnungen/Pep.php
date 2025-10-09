<?php

namespace App\Livewire\Components\Modals\Eroeffnungen;

use App\Livewire\Components\Modals\BaseModal;
use App\Models\Eroeffnung;

class Pep extends BaseModal
{
    public ?Eroeffnung $entry = null;

    protected function openWith(array $payload): bool
    {
        if (! isset($payload['entryId'])) {
            return false;
        }

        $this->entry = Eroeffnung::find($payload['entryId']);

        if (! $this->entry) {
            return false;
        }

        $this->title    = "PEP-Benutzer erstellen";
        $this->size     = "md";
        $this->position = "centered";
        $this->backdrop = true;
        $this->headerBg   = "bg-primary";
        $this->headerText = "text-white";

        return true;
    }

    public function confirm(): void
    {
        if ($this->entry) {
            $this->entry->update(['status_pep' => 2]);
        }

        $this->dispatch('pep-updated');
        $this->closeModal();
    }

    public function render()
    {
        return view('livewire.components.modals.eroeffnungen.pep');
    }
}
