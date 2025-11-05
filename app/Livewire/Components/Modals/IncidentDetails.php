<?php

namespace App\Livewire\Components\Modals;

use App\Livewire\Components\Modals\BaseModal;
use App\Models\Incident;

class IncidentDetails extends BaseModal
{
    public ?Incident $incident = null;

    protected function openWith(array $payload): bool
    {
        $id = $payload['id'] ?? null;

        if (!$id || !($this->incident = Incident::find($id))) {
            $this->dispatch('open-modal', modal: 'alert-modal', payload: [
                'message'  => 'Der Incident konnte nicht gefunden werden.',
                'headline' => 'Fehler',
                'color'    => 'bg-danger',
                'icon'     => 'ri-close-circle-line',
            ]);
            return false;
        }

        $this->title      = "Incident Details #{$this->incident->id}";
        $this->size       = 'lg';
        $this->backdrop   = false;
        $this->position   = 'centered';
        $this->scrollable = true;
        $this->headerBg   = 'bg-primary';
        $this->headerText = 'text-white';

        return true;
    }

    public function render()
    {
        return view('livewire.components.modals.incident-details');
    }
}
