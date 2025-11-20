<?php

namespace App\Livewire\Components\Modals\Eroeffnungen;

use App\Livewire\Components\Modals\BaseModal;
use App\Models\Eroeffnung;

class Archivieren extends BaseModal
{
    public ?Eroeffnung $entry = null;

    protected function openWith(array $payload): bool
    {
        $id = $payload["entryId"] ?? null;

        if (!$id || !($this->entry = Eroeffnung::find($id))) 
		{
            $this->dispatch("open-modal", modal: "alert-modal", payload: [
                "message"  => "Die Eröffnung konnte nicht gefunden werden (ID: {$id}).",
                "headline" => "Fehler",
                "color"    => "bg-danger",
                "icon"     => "ri-close-circle-line",
            ]);
			
            return false;
        }

        $this->title      = "Eröffnung archivieren";
        $this->size       = "md";
        $this->backdrop   = false;
        $this->position   = "centered";
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
			$this->dispatch("redirect", route('admin.eroeffnungen.index'));
		}
	}

    public function render()
    {
        return view("livewire.components.modals.eroeffnungen.archivieren");
    }
}
