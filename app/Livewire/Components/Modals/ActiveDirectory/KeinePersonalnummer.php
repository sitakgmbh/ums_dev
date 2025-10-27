<?php

namespace App\Livewire\Components\Modals\ActiveDirectory;

use App\Livewire\Components\Modals\BaseModal;
use App\Models\AdUser;

class KeinePersonalnummer extends BaseModal
{
    public $userOhnePersNr = [];

    protected function openWith(array $payload): bool
    {	
		$this->userOhnePersNr = AdUser::whereNull("initials")
            // ->where('is_existing', true)
			->orderBy('display_name', 'asc')
			->get();

        $this->title = "Benutzer ohne Personalnummer";
        $this->size = "lg";
        $this->position = "centered";
        $this->backdrop = false;
        $this->headerBg = "bg-primary";
        $this->headerText = "text-white";

        return true;
    }

    public function render()
    {
        return view("livewire.components.modals.active-directory.keine-personalnummer");
    }
}