<?php

namespace App\Livewire\Pages\Admin\Mutationen;

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
        return view('livewire.pages.admin.mutationen.index')
            ->layoutData([
                'pageTitle' => 'Mutationen',
            ]);
    }
}
