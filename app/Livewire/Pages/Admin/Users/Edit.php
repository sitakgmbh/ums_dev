<?php

namespace App\Livewire\Pages\Admin\Users;

use App\Livewire\Forms\UserForm;
use App\Models\User;
use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout("layouts.app")]
class Edit extends Component
{
    public UserForm $form;

    public function mount(User $user): void
    {
        $this->form->setUser($user, false);
    }

    public function save()
    {
        $this->form->validate();

        $user = $this->form->user;

        $user->update([
            "username"   => $this->form->username,
            "firstname"  => $this->form->firstname,
            "lastname"   => $this->form->lastname,
            "email"      => $this->form->email,
            "auth_type"  => $this->form->auth_type,
            "is_enabled" => $this->form->is_enabled,
            "password"   => $this->form->password
                ? bcrypt($this->form->password)
                : $user->password,
        ]);

        $user->syncRoles([$this->form->role]);

        session()->flash("success", "Benutzer erfolgreich aktualisiert.");
        return redirect()->route("admin.users.index");
    }

    public function render()
    {
        return view("livewire.pages.admin.users.edit", [
            "roles" => $this->form->roles(),
        ])->layoutData([
            "pageTitle" => "Benutzer bearbeiten",
        ]);
    }
}
