<?php

namespace App\Livewire\Components\Modals\Austritte;

use App\Livewire\Components\Modals\BaseModal;
use App\Models\Austritt;

class Logimen extends BaseModal
{
    public ?Austritt $entry = null;

    protected function openWith(array $payload): bool
    {
        if (! isset($payload["entryId"])) 
		{
            return false;
        }

        $this->entry = Austritt::find($payload["entryId"]);

        if (! $this->entry) 
		{
            return false;
        }

        $this->title    = "Logimen";
        $this->size     = "md";
        $this->position = "centered";
		$this->backdrop = true;
        $this->headerBg   = "bg-primary";
        $this->headerText = "text-white";

        return true;
    }

    public function confirm(): void
    {
        if ($this->entry) 
		{
            $this->entry->update(["status_logimen" => 2]);
        }

        $this->dispatch("logimen-updated");
        $this->closeModal();
    }

    public function render()
    {
        return view("livewire.components.modals.austritte.logimen");
    }
}
