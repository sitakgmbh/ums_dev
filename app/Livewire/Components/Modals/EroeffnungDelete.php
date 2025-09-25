<?php

namespace App\Livewire\Components\Modals;

use App\Livewire\Components\Modals\BaseModal;
use App\Models\Eroeffnung;
use App\Utils\Logging\Logger;

class EroeffnungDelete extends BaseModal
{
    public ?Eroeffnung $eroeffnung = null;

    protected function openWith(array $payload): bool
    {
        $id = $payload['id'] ?? null;

        if (!$id || !($this->eroeffnung = Eroeffnung::find($id))) {
            $this->dispatch('open-modal', modal: 'alert-modal', payload: [
                'message'  => "Die Eröffnung konnte nicht gefunden werden (ID: {$id}).",
                'headline' => 'Fehler',
                'color'    => 'bg-danger',
                'icon'     => 'ri-close-circle-line',
            ]);
            return false;
        }

        $this->title      = "Eröffnung löschen";
        $this->size       = 'md';
        $this->backdrop   = true;
        $this->position   = 'centered';
        $this->scrollable = true;
        $this->headerBg   = 'bg-danger';
        $this->headerText = 'text-white';

        return true;
    }

    public function delete(): void
    {
        if (!$this->eroeffnung || !Eroeffnung::find($this->eroeffnung->id)) {
            $this->dispatch('open-modal', modal: 'alert-modal', payload: [
                'message'  => 'Die Eröffnung ist nicht mehr vorhanden.',
                'headline' => 'Fehler',
                'color'    => 'bg-danger',
                'icon'     => 'ri-close-circle-line',
            ]);
            return;
        }

        $id   = $this->eroeffnung->id;
        $name = "{$this->eroeffnung->vorname} {$this->eroeffnung->nachname}";

        $this->eroeffnung->delete();

        Logger::db('system', 'warning', "Eröffnung {$name} (ID {$id}) wurde gelöscht.");

        $this->closeModal();
        $this->dispatch('notify', message: "{$name} wurde erfolgreich gelöscht.", type: 'danger');
        $this->dispatch('eroeffnung-deleted', id: $id);
    }

    public function render()
    {
        return view('livewire.components.modals.eroeffnung-delete');
    }
}
