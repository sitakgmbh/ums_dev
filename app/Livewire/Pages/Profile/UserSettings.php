<?php
namespace App\Livewire\Pages\Profile;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use App\Models\AdUser;

class UserSettings extends Component
{
    public bool $darkmode_enabled = false;
    public array $representations = [];
    public $adUsers;

    public function mount()
    {
        $user = Auth::user();
        $this->darkmode_enabled = (bool) $user->getSetting('darkmode_enabled', false);
        $this->representations   = $user->getSetting('representations', []);
        $this->adUsers          = AdUser::orderBy('display_name')->get(['id', 'display_name']);
    }

	public function save()
	{
		$user = Auth::user();
		$user->setSetting('darkmode_enabled', $this->darkmode_enabled);
		
		// IDs zu Integers konvertieren
		$representations = array_map('intval', $this->representations);
		$user->setSetting('representations', $representations);
		
		session()->flash('success', 'Deine Einstellungen wurden gespeichert.');
		$this->dispatch('select2Updated', representations: $this->representations);
	}

    public function render()
    {
        return view('livewire.pages.profile.user-settings')
            ->layout('layouts.app', ['pageTitle' => 'Benutzereinstellungen']);
    }
}