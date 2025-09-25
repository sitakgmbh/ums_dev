<?php

namespace App\Livewire\Pages\Admin\AdUsers;

use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('layouts.app')]
class Index extends Component
{
    public function render()
    {
        return view('livewire.pages.admin.ad-users.index')
            ->layoutData([
                'pageTitle' => 'Active Directory Benutzer',
            ]);
    }
}
