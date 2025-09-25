<?php

namespace App\Livewire\Pages\Eroeffnungen;

use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('layouts.app')]
class Index extends Component
{
    public function render()
    {
        return view('livewire.pages.eroeffnungen.index')
            ->layoutData([
                'pageTitle' => 'Eröffnungen',
            ]);
    }
}
