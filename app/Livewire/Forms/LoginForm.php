<?php

namespace App\Livewire\Forms;

use Illuminate\Auth\Events\Lockout;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Validate;
use Livewire\Form;

class LoginForm extends Form
{
    #[Validate("required|string")]
    public string $username = "";

    #[Validate("required|string")]
    public string $password = "";

    #[Validate("boolean")]
    public bool $remember = false;

    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        if (! Auth::attempt([
            "username" => $this->username,
            "password" => $this->password,
        ], $this->remember)) {
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                "form.username" => trans("auth.failed"),
            ]);
        }

        RateLimiter::clear($this->throttleKey());
    }

    // Rate-Limiting prüfen
    protected function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout(request()));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            "form.username" => trans("auth.throttle", [
                "seconds" => $seconds,
                "minutes" => ceil($seconds / 60),
            ]),
        ]);
    }

    // Generiert einen eindeutigen Schlüssel für das Rate-Limiting
    protected function throttleKey(): string
    {
        return Str::lower($this->username) . "|" . request()->ip();
    }
}
