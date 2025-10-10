<?php

namespace App\Livewire\Components\Modals\Austritte;

use App\Livewire\Components\Modals\BaseModal;
use App\Models\Austritt;

class Archivieren extends BaseModal
{
    public ?Austritt $entry = null;

    protected function openWith(array $payload): bool
    {
        if (! isset($payload['entryId'])) 
		{
            return false;
        }

        $this->entry = Austritt::find($payload['entryId']);

        if (! $this->entry) 
		{
            return false;
        }

        $this->title      = "Archivieren";
        $this->size       = "md";
        $this->position   = "centered";
        $this->backdrop   = false;
        $this->headerBg   = "bg-primary";
        $this->headerText = "text-white";

        return true;
    }

	public function confirm(): void
	{
		if ($this->entry) 
		{
			$this->entry->update(["archiviert" => 1]);
			$this->closeModal();

			$this->dispatch("notify", message: "Der Antrag wurde archiviert.", type: "info");
			$this->dispatch("redirect", route('admin.austritte.index'));
		}
	}

    public function render()
    {
        return view("livewire.components.modals.austritte.archivieren");
    }
}
