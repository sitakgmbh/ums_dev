<?php

namespace App\Livewire\Pages\Admin\Austritte;

use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('layouts.app')]
class Index extends Component
{
    public bool $showArchived = false;

    public function toggleArchived(): void
    {
        $this->showArchived = ! $this->showArchived;
    }

    public function render()
    {
        return view('livewire.pages.admin.austritte.index')
            ->layoutData([
                'pageTitle' => 'Austritte',
            ]);
    }
}
