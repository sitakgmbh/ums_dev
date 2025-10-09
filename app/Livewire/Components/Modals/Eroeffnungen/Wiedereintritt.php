<?php

namespace App\Livewire\Components\Modals\Eroeffnungen;

use App\Livewire\Components\Modals\BaseModal;
use App\Models\AdUser;

class Wiedereintritt extends BaseModal
{
    public array $adusers = [];
    public ?int $selectedUserId = null;
	public ?bool $selectedUserEnabled = null;

    protected function openWith(array $payload): bool
    {
        if (isset($payload["users"])) 
		{
            $this->adusers = $payload["users"];
        } 
		else 
		{
            $vorname  = $payload["vorname"] ?? null;
            $nachname = $payload["nachname"] ?? null;

            if (!$vorname || !$nachname) 
			{
                return false;
            }

            $this->adusers = AdUser::with("funktion")
                ->where("firstname", $vorname)
                ->where("lastname", $nachname)
                ->get()
                ->map(fn ($u) => [
                    "id"           => $u->id,
                    "vorname"      => $u->firstname,
                    "nachname"     => $u->lastname,
                    "email"        => $u->email,
                    "initials"     => $u->initials,
                    "beschreibung" => $u->description,
                    "funktion"     => optional($u->funktion)->name,
                    "enabled"      => $u->is_enabled,
                ])
                ->toArray();
        }

        if (empty($this->adusers)) 
		{
            return false;
        }

        $this->title = "Möglicher Wiedereintritt";
        $this->size = "lg";
        $this->backdrop = true;
        $this->position = "centered";
        $this->scrollable = true;
        $this->headerBg = "bg-warning";
        $this->headerText = "text-white";

        return true;
    }

	public function selectUser(int $id): void
	{
		$this->selectedUserId = $id;
		$user = collect($this->adusers)->firstWhere("id", $id);

		$this->selectedUserEnabled = $user["enabled"] ?? null;
	}

    public function confirm(): void
    {
        $this->dispatch("wiedereintritt-selected", [
            "id" => $this->selectedUserId,
        ]);

        $this->closeModal();
    }

    public function render()
    {
        return view("livewire.components.modals.eroeffnungen.wiedereintritt");
    }
}
