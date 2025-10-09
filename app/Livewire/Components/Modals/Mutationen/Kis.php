<?php

namespace App\Livewire\Components\Modals\Mutationen;

use App\Livewire\Components\Modals\BaseModal;
use App\Models\Mutation;

class Kis extends BaseModal
{
    public ?Mutation $entry = null;

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

        $this->title    = "KIS-Benutzer mutieren";
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
            $this->entry->update(["status_kis" => 2]);
        }

        $this->dispatch("kis-updated");
        $this->closeModal();
    }

    public function render()
    {
        return view("livewire.components.modals.mutationen.kis");
    }
}
