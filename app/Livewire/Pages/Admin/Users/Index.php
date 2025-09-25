<?php

namespace App\Livewire\Pages\Admin\Users;

use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('layouts.app')]
class Index extends Component
{
    public function render()
    {
        return view('livewire.pages.admin.users.index')
            ->layoutData([
                'pageTitle'   => 'Benutzerverwaltung',
            ]);
    }
}
