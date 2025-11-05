<?php

namespace App\Livewire\Pages\Admin\Incidents;

use Livewire\Component;
use App\Models\Incident;
use Livewire\Attributes\Layout;

#[Layout('layouts.app')]
class Index extends Component
{
    public function render()
    {
        $openIncidents = Incident::open()->with(['creator', 'resolver'])->orderBy('priority', 'desc')->orderBy('created_at', 'desc')->get();
        $resolvedIncidents = Incident::resolved()->with(['creator', 'resolver'])->orderBy('resolved_at', 'desc')->get();

        return view('livewire.pages.admin.incidents.index', [
            'openIncidents' => $openIncidents,
            'resolvedIncidents' => $resolvedIncidents,
        ])
		->layout('layouts.app', [
				'pageTitle' => 'Incidents',
			]);
    }
}
