<?php

namespace App\Livewire\Pages\Profile;

use App\Livewire\Forms\ProfileForm;
use Livewire\Component;

class Edit extends Component
{
    public ProfileForm $form;

    public function mount(): void
    {
        // Angemeldeten Benutzer laden
        $this->form->setUser();
    }

    public function save()
    {
        $this->form->validate();

        $user = $this->form->user;

        $user->update([
            "firstname" => $this->form->firstname,
            "lastname"  => $this->form->lastname,
            "email"     => $this->form->email,
            "password"  => $this->form->password
                ? bcrypt($this->form->password)
                : $user->password,
        ]);

        session()->flash("success", "Profil erfolgreich aktualisiert.");
    }

    public function render()
    {
        return view("livewire.pages.profile.edit")
            ->layout("layouts.app", [
                "pageTitle" => "Profil bearbeiten",
            ]);
    }
}
