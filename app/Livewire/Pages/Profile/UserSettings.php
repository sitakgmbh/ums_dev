<?php
namespace App\Livewire\Pages\Profile;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use App\Models\AdUser;

class UserSettings extends Component
{
    public bool $darkmode_enabled = false;
    public array $representations = [];
	public $representingUsers = [];
    public $adUsers;


public function mount()
{
    $user = Auth::user();
    $this->darkmode_enabled = (bool) $user->getSetting('darkmode_enabled', false);
    $this->representations = $user->myRepresentation()->pluck('ad_users.id')->toArray();
    $this->adUsers = AdUser::orderBy('display_name')->get(['id', 'display_name']);

    // Benutzer, die dich als Vertreter eingetragen haben
    $this->representingUsers = \App\Models\User::whereHas('myRepresentation', function($q) use ($user) {
        $q->where('ad_user_id', $user->adUser?->id);
    })
    ->with('adUser')
    ->get()
    ->map(fn($u) => $u->adUser?->display_name . ' (' . $u->username . ')')
    ->toArray();
}


    public function save()
    {
        $user = Auth::user();
        $user->setSetting('darkmode_enabled', $this->darkmode_enabled);

        $neueRepresentations = array_map('intval', $this->representations);

        // Sync über myRepresentation
        $user->myRepresentation()->sync($neueRepresentations);

        session()->flash('success', 'Deine Einstellungen wurden gespeichert.');
        $this->dispatch('select2Updated', representations: $this->representations);
    }

    public function render()
    {
        return view('livewire.pages.profile.user-settings')
            ->layout('layouts.app', ['pageTitle' => 'Benutzereinstellungen']);
    }
}
