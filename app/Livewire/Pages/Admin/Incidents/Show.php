<?php

namespace App\Livewire\Pages\Admin\Incidents;

use Livewire\Component;
use App\Models\Incident;
use Livewire\Attributes\Layout;

#[Layout('layouts.app')]
class Show extends Component
{
    public ?Incident $incident = null;

    /**
     * Lädt den Incident inkl. Creator & Resolver
     */
    public function mount(int $id)
    {
        $this->incident = Incident::with(['creator','resolver'])->findOrFail($id);
    }

    /**
     * Markiert den Incident als gelöst
     */
    public function resolveIncident(): void
    {
        if (!$this->incident || $this->incident->resolved_at) {
            return;
        }

        $this->incident->resolve();

        // Reload, damit die View den aktuellen Status anzeigt
        $this->incident = $this->incident->fresh(['creator','resolver']);
    }

    public function render()
    {
        return view('livewire.pages.admin.incidents.show', [
            'incident' => $this->incident,
        ])->layout('layouts.app', [
            'pageTitle' => "Details Incident #{$this->incident->id}",
        ]);
    }
}
