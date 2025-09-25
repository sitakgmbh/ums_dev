<?php

namespace App\Livewire\Actions;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Logout extends Component
{
    protected $listeners = ['perform-logout' => 'logout'];

    public function logout()
    {
        Auth::logout();
        session()->invalidate();
        session()->regenerateToken();

        return redirect()->route('login');
    }

	public function render()
	{
		return view('livewire.actions.logout');
	}


}
