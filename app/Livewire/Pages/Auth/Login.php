<?php

namespace App\Livewire\Pages\Auth;

use App\Livewire\Forms\LoginForm;
use Illuminate\Support\Facades\Session;
use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('layouts.auth')]
class Login extends Component
{
    public LoginForm $form;

    public function login(): void
    {
        $this->validate(); // Regeln aus LoginForm

        $this->form->authenticate();

        Session::regenerate();

        $user = auth()->user();

        session([
            'darkmode_enabled'  => (bool) $user->getSetting('darkmode_enabled', false),
            'sidebar_collapsed' => (bool) $user->getSetting('sidebar_collapsed', false),
        ]);

        $this->redirectIntended(route('dashboard'));
    }

    public function render()
    {
        return view('livewire.pages.auth.login');
    }
}
