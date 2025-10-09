<?php

namespace App\Livewire\Pages\Mutationen;

use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout("layouts.app")]
class Index extends Component
{
    public function render()
    {
        return view("livewire.pages.mutationen.index")
            ->layoutData([
                "pageTitle" => "Mutationen",
            ]);
    }
}
