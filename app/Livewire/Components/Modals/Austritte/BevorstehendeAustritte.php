<?php

namespace App\Livewire\Components\Modals\Austritte;

use App\Livewire\Components\Modals\BaseModal;
use App\Models\AdUser;

class BevorstehendeAustritte extends BaseModal
{
    public $austritte = [];

	public function mount()
	{
		logger()->info('Modal-Name: ' . static::getName());
	}

    protected function openWith(array $payload): bool
    {	
		$this->austritte = AdUser::with([
				'arbeitsort',
				'unternehmenseinheit',
				'abteilung',
				'funktion',
				'anrede',
				'titel',
			])
			->whereNotNull('account_expiration_date')
            // ->where('is_existing', false)
            // ->where('is_enabled', true)
			->orderBy('account_expiration_date', 'asc')
			->get();

        $this->title = "Bevorstehende Austritte";
        $this->size = "full-width";
        $this->position = "centered";
        $this->backdrop = false;
        $this->headerBg = "bg-primary";
        $this->headerText = "text-white";

        return true;
    }

    public function render()
    {
        return view("livewire.components.modals.austritte.bevorstehende-austritte");
    }
}