<?php

namespace App\Livewire\Pages\Admin;

use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout("layouts.app")]
class Logs extends Component
{
    public string $filterLevel = "";
    public string $filterCategory = "";
    public ?string $dateFrom = null;
    public ?string $dateTo = null;

    public function render()
    {
        return view("livewire.pages.admin.logs")
            ->layoutData([
                "pageTitle" => "Logs",
            ]);
    }
}
