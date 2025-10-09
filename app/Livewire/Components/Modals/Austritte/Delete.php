<?php

namespace App\Livewire\Components\Modals\Austritte;

use App\Livewire\Components\Modals\BaseModal;
use App\Models\Austritt;

class Delete extends BaseModal
{
    public ?Austritt $austritt = null;

    protected function openWith(array $payload): bool
    {
        $id = $payload["id"] ?? null;

        if (!$id || !($this->austritt = Austritt::find($id))) 
		{
            $this->dispatch("open-modal", modal: "alert-modal", payload: [
                "message"  => "Der Antrag konnte nicht gefunden werden (ID: {$id}).",
                "headline" => "Fehler",
                "color"    => "bg-danger",
                "icon"     => "ri-close-circle-line",
            ]);
			
            return false;
        }

        $this->title      = "Austritt löschen";
        $this->size       = "md";
        $this->backdrop   = true;
        $this->position   = "centered";
        $this->scrollable = true;
        $this->headerBg   = "bg-danger";
        $this->headerText = "text-white";

        return true;
    }

    public function delete(): void
    {
        if (!$this->austritt || !Austritt::find($this->austritt->id)) 
		{
            $this->dispatch("open-modal", modal: "alert-modal", payload: [
                "message"  => "Der Antrag ist nicht mehr vorhanden.",
                "headline" => "Fehler",
                "color"    => "bg-danger",
                "icon"     => "ri-close-circle-line",
            ]);
			
            return;
        }

        $id   = $this->austritt->id;
        $name = "{$this->austritt->vorname} {$this->austritt->nachname}";

        $this->austritt->delete();

        $this->closeModal();
        $this->dispatch("notify", message: "{$name} wurde erfolgreich gelöscht.", type: "danger");
        $this->dispatch("austritt-deleted", id: $id);
    }

    public function render()
    {
        return view("livewire.components.modals.austritte.delete");
    }
}
