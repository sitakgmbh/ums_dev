<?php

namespace App\Livewire\Pages\Profile;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class UserSettings extends Component
{
    public bool $darkmode_enabled = false;

    public function mount()
    {
        $user = Auth::user();

        $this->darkmode_enabled  = (bool) $user->getSetting('darkmode_enabled', false);
    }

    public function save()
    {
        $user = Auth::user();

        $user->setSetting('darkmode_enabled', $this->darkmode_enabled);

        // Session-Werte ebenfalls aktualisieren
        session([
            'darkmode_enabled'  => $this->darkmode_enabled,
        ]);

        session()->flash('success', 'Deine Einstellungen wurden gespeichert.');
    }

    public function render()
    {
        return view('livewire.pages.profile.user-settings')
            ->layout('layouts.app', [
                'pageTitle' => 'Benutzereinstellungen',
            ]);
    }
}
