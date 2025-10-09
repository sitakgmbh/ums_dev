<?php

namespace App\Livewire\Components\Modals;

use App\Livewire\Components\Modals\BaseModal;
use App\Models\User;
use App\Utils\Logging\Logger;

class UserDelete extends BaseModal
{
    public ?User $user = null;

    protected function openWith(array $payload): bool
    {
        $id = $payload["id"] ?? null;

        if (! $id || ! ($this->user = User::find($id))) 
		{
            $this->dispatch("open-modal", modal: "alert-modal", payload: [
                "message"  => "Der Benutzer konnte nicht gefunden werden (ID: {$id}).",
                "headline" => "Fehler",
                "color"    => "bg-danger",
                "icon"     => "ri-close-circle-line",
            ]);
			
            return false;
        }

        $this->title      = "Benutzer löschen";
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
        if (! $this->user || ! User::find($this->user->id)) 
		{
            $this->dispatch("open-modal", modal: "alert-modal", payload: [
                "message"  => "Der Benutzer ist nicht mehr vorhanden.",
                "headline" => "Fehler",
                "color"    => "bg-danger",
                "icon"     => "ri-close-circle-line",
            ]);
            return;
        }

        $id       = $this->user->id;
        $username = $this->user->username ?? $this->user->email ?? "User#{$id}";
        $fullname = trim("{$this->user->firstname} {$this->user->lastname}") ?: $username;

        $this->user->delete();

        $actor     = auth()->user();
        $actorName = $actor?->username ?? $actor?->name ?? "unbekannt";

        $this->closeModal();
        $this->dispatch("notify", message: "{$fullname} wurde erfolgreich gelöscht.", type: "danger");
        $this->dispatch("user-deleted", id: $id);
    }

    public function render()
    {
        return view("livewire.components.modals.user-delete");
    }
}
