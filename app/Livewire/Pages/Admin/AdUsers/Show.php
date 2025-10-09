<?php

namespace App\Livewire\Pages\Admin\AdUsers;

use App\Models\AdUser;
use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout("layouts.app")]
class Show extends Component
{
    public AdUser $adUser;

    public function mount(AdUser $adUser): void
    {
        $this->adUser = $adUser;
    }

    public function render()
    {
        return view("livewire.pages.admin.ad-users.show")
            ->layoutData([
                "pageTitle" => "AD-Benutzer " . ($this->adUser->display_name ?? $this->adUser->username),
            ]);
    }
}
