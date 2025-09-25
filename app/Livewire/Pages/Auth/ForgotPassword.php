<?php

namespace App\Livewire\Pages\Auth;

use App\Models\User;
use Illuminate\Support\Facades\Password;
use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('layouts.auth')]
class ForgotPassword extends Component
{
    public string $email = '';

    public function submit()
    {
        $this->validate([
            'email' => ['required', 'email'],
        ]);

        $user = User::where('email', $this->email)->first();

        if (! $user || $user->auth_type !== 'local') {
            $this->addError('email', 'Für diesen Benutzer ist kein Passwort-Reset möglich.');
            return;
        }

        $status = Password::sendResetLink(['email' => $this->email]);

        if ($status === Password::RESET_LINK_SENT) {
            session()->flash('status', __($status));
        } else {
            $this->addError('email', __($status));
        }
    }

    public function render()
    {
        return view('livewire.pages.auth.forgot-password');
    }
}
