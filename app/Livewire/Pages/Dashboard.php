<?php

namespace App\Livewire\Pages;
use App\Models\Eroeffnung;
use App\Models\Mutation;

use Livewire\Component;

#[Layout('layouts.app')]
class Dashboard extends Component
{
    public int $eroeffnungenCount = 0;
    public int $mutationenCount = 0;

    public function mount()
    {
		$userSid = auth()->user()->adUser->sid ?? null;
		
		$this->eroeffnungenCount = Eroeffnung::whereHas("antragsteller", function ($query) use ($userSid) {
			$query->where("sid", $userSid);
		})->count();

		$this->mutationenCount = Mutation::whereHas("antragsteller", function ($query) use ($userSid) {
			$query->where("sid", $userSid);
		})->count();
    }
	
    public function render()
    {
        return view("livewire.pages.dashboard")
			->layout("layouts.app", [
				"pageTitle" => "Dashboard",
			]);
    }
}
