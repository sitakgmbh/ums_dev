<?php

namespace App\Livewire\Pages\Admin\Eroeffnungen;

use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('layouts.app')]
class Index extends Component
{
    public bool $showArchived = false; // Zustand steuern

    public function toggleArchived(): void
    {
        $this->showArchived = ! $this->showArchived;
    }

    public function render()
    {
        return view('livewire.pages.admin.eroeffnungen.index')
            ->layoutData([
                'pageTitle' => 'Eröffnungen',
            ]);
    }
}
