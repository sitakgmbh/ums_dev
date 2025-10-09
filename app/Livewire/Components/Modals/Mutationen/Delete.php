<?php

namespace App\Livewire\Components\Modals\Mutationen;

use App\Livewire\Components\Modals\BaseModal;
use App\Models\Mutation;

class Delete extends BaseModal
{
    public ?Mutation $mutation = null;

    protected function openWith(array $payload): bool
    {
        $id = $payload["id"] ?? null;

        if (!$id || !($this->mutation = Mutation::find($id))) {
            $this->dispatch("open-modal", modal: "alert-modal", payload: [
                "message"  => "Die Mutation konnte nicht gefunden werden (ID: {$id}).",
                "headline" => "Fehler",
                "color"    => "bg-danger",
                "icon"     => "ri-close-circle-line",
            ]);

            return false;
        }

        $this->title      = "Mutation löschen";
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
        if (!$this->mutation || !Mutation::find($this->mutation->id)) {
            $this->dispatch("open-modal", modal: "alert-modal", payload: [
                "message"  => "Die Mutation ist nicht mehr vorhanden.",
                "headline" => "Fehler",
                "color"    => "bg-danger",
                "icon"     => "ri-close-circle-line",
            ]);

            return;
        }

        $id   = $this->mutation->id;
        $name = trim(($this->mutation->vorname ?? '') . ' ' . ($this->mutation->nachname ?? ''));

        $this->mutation->delete();

        $this->closeModal();
        $this->dispatch("notify", message: "Mutation {$id} ({$name}) wurde erfolgreich gelöscht.", type: "danger");
        $this->dispatch("mutation-deleted", id: $id);
    }

    public function render()
    {
        return view("livewire.components.modals.mutationen.delete");
    }
}
