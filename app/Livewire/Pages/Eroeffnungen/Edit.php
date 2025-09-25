<?php

namespace App\Livewire\Pages\Eroeffnungen;

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Livewire\Forms\EroeffnungForm;
use App\Models\Eroeffnung;
use App\Livewire\Traits\EroeffnungFormHooks;

#[Layout('layouts.app')]
class Edit extends Component
{
    use EroeffnungFormHooks;

    public EroeffnungForm $form;
    public Eroeffnung $eroeffnung;

    // Initialisierung mit bestehender Eröffnung
    public function mount(Eroeffnung $eroeffnung): void
    {
        $this->form->isCreate   = false;
        $this->form->fill($eroeffnung->toArray());
        $this->eroeffnung = $eroeffnung;

        // Stammdaten laden
        $this->form->loadArbeitsorte($this->form->neue_konstellation);
        $this->form->loadAnreden();
        $this->form->loadTitel();
        $this->form->loadMailendungen();
        $this->form->loadUnternehmenseinheiten($this->form->neue_konstellation);
        $this->form->loadAbteilungen($this->form->neue_konstellation);
        $this->form->loadFunktionen($this->form->neue_konstellation);
        $this->form->loadAdusers($this->form->filter_mitarbeiter ? $this->form->abteilung_id : null);

        // Dropdowns füllen
        foreach ([
            'anrede_id'              => $this->form->anreden,
            'titel_id'               => $this->form->titel,
            'mailendung'             => $this->form->mailendungen,
            'arbeitsort_id'          => $this->form->arbeitsorte,
            'unternehmenseinheit_id' => $this->form->unternehmenseinheiten,
            'abteilung_id'           => $this->form->abteilungen,
            'funktion_id'            => $this->form->funktionen,
            'bezugsperson_id'        => $this->form->adusers,
            'vorlage_benutzer_id'    => $this->form->adusers,
            'abteilung2_id'          => $this->form->abteilungen,
        ] as $id => $options) {
            $this->dispatch('select2-options', id: $id, options: $options, value: $this->form->$id);
        }
    }

    // Speichern (Update)
    public function save()
    {
        $this->form->validate();
        $data = $this->form->toArray();

        $this->eroeffnung->update($data);

        session()->flash('success', 'Eröffnung erfolgreich aktualisiert.');
        return redirect()->route('eroeffnungen.index');
    }

    // Render View
    public function render()
    {
        return view('livewire.pages.eroeffnungen.edit')
            ->layoutData(['pageTitle' => 'Eröffnung bearbeiten']);
    }
}
