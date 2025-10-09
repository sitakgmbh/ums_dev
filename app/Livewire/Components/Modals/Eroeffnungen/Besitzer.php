<?php

namespace App\Livewire\Components\Modals\Eroeffnungen;

use App\Livewire\Components\Modals\BaseModal;
use App\Models\Eroeffnung;
use App\Models\User;

class Besitzer extends BaseModal
{
    public ?Eroeffnung $entry = null;
    public ?int $owner_id = null;
    public array $users = [];

    protected function openWith(array $payload): bool
    {
        if (! isset($payload["entryId"])) 
		{
            return false;
        }

        $this->entry = Eroeffnung::find($payload["entryId"]);
		
        if (! $this->entry) 
		{
            return false;
        }

        $this->title      = "Besitzer zuweisen";
        $this->size       = "md";
        $this->headerBg   = "bg-primary";
        $this->headerText = "text-white";

        $this->owner_id = $this->entry->owner_id;

		$this->users = User::role("admin")
			->whereNotNull("ad_sid")
			->whereHas("adUser")
			->with("adUser")
			->get()
			->map(fn($u) => [
				"id"   => $u->adUser->id,
				"name" => "{$u->adUser->display_name}",
			])
			->sortBy("name")
			->values()
			->toArray();

        return true;
    }

    public function confirm(): void
    {
        if (! $this->entry) 
		{
            return;
        }

        $this->entry->update(["owner_id" => $this->owner_id]); // ad_users.id

        $this->dispatch("besitzer-updated");
        $this->closeModal();
    }

    public function render()
    {
        return view("livewire.components.modals.eroeffnungen.besitzer");
    }
}
