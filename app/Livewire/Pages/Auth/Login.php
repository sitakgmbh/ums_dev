<?php

namespace App\Livewire\Pages\Auth;

use App\Livewire\Forms\LoginForm;
use Illuminate\Support\Facades\Session;
use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout("layouts.auth")]
class Login extends Component
{
    public LoginForm $form;

    public function mount(): void
    {
        if (auth()->check()) 
		{
            $user = auth()->user();

            session([
                "darkmode_enabled" => (bool) $user->getSetting("darkmode_enabled", false),
            ]);

            $this->redirectIntended(route("dashboard"));
        }
    }

    public function login(): void
    {
        $this->validate();

        $this->form->authenticate();

        Session::regenerate();

        $user = auth()->user();

        session([
            "darkmode_enabled" => (bool) $user->getSetting("darkmode_enabled", false),
        ]);

        $this->redirectIntended(route("dashboard"));
    }

    public function render()
    {
        return view("livewire.pages.auth.login");
    }
}
