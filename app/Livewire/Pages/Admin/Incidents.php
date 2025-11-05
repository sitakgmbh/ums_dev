<?php

namespace App\Livewire\Pages\Admin;

use App\Models\Incident;
use Livewire\Component;
use App\Livewire\Components\Modals\IncidentDetails;
use Livewire\Attributes\Layout;

#[Layout('layouts.app')]
class Incidents extends Component
{
    /**
     * Markiert einen Incident als gelöst.
     */
    public function resolveIncident(int $id): void
    {
        $incident = Incident::findOrFail($id);
        $incident->resolve();

        // Event für Frontend-Benachrichtigung
        $this->dispatch('success', message: 'Incident wurde als gelöst markiert.');
    }

    /**
     * Öffnet das IncidentDetails-Modal über das BaseModal-System.
     */
    public function openIncidentDetails(int $id): void
    {
        $this->dispatch('open-modal', modal: IncidentDetails::class, payload: ['id' => $id]);
    }

    /**
     * Rendert die Livewire-View mit offenen und gelösten Incidents.
     */
    public function render()
    {
        $openIncidents = Incident::open()
            ->with(['creator', 'resolver'])
            ->orderBy('priority', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        $resolvedIncidents = Incident::resolved()
            ->with(['creator', 'resolver'])
            ->orderBy('resolved_at', 'desc')
            ->get();

        return view('livewire.pages.admin.incidents', [
            'openIncidents'     => $openIncidents,
            'resolvedIncidents' => $resolvedIncidents,
        ])->layout('layouts.app', [
            'pageTitle' => 'Incidents',
        ]);
    }
}
