<?php

namespace App\Livewire\Pages\Eroeffnungen;

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Livewire\Forms\EroeffnungForm;
use App\Models\Eroeffnung;
use App\Livewire\Traits\EroeffnungFormHooks;

#[Layout('layouts.app')]
class Create extends Component
{
    use EroeffnungFormHooks; // Kaskaden- und Checkbox-Methoden einbinden

    public EroeffnungForm $form;

    // Initiale Formularwerte und Dropdowns laden
    public function mount(): void
    {
        $this->form->isCreate = true;

        // Stammdaten
        $this->form->loadArbeitsorte();
        $this->form->loadAnreden();
        $this->form->loadTitel();
        $this->form->loadMailendungen();

        // AdUsers abhängig vom Filter
        $this->form->filter_mitarbeiter
            ? $this->form->adusers = []
            : $this->form->loadAdusers(null);

        // Select2-Dropdowns initialisieren
        foreach ([
            'anrede_id'              => $this->form->anreden,
            'titel_id'               => $this->form->titel,
            'mailendung'             => $this->form->mailendungen,
            'arbeitsort_id'          => $this->form->arbeitsorte,
            'unternehmenseinheit_id' => [],
            'abteilung_id'           => [],
            'funktion_id'            => [],
            'bezugsperson_id'        => $this->form->adusers,
            'vorlage_benutzer_id'    => $this->form->adusers,
            'abteilung2_id'          => [],
        ] as $id => $options) {
            $this->dispatch('select2-options', id: $id, options: $options, value: null);
        }
    }

    // Formular speichern (neu erstellen)
    public function save()
    {
        $this->form->validate();

        $data = $this->form->toArray();

        // Pflichtfelder sicherstellen
        $data['antragsteller_id'] = auth()->id();
        $data['bezugsperson_id']  = auth()->id();
        $data['email']            = "patrik@sitak.ch"; // Dummy

        Eroeffnung::create($data);

        session()->flash('success', 'Eröffnung erfolgreich erstellt.');
        return redirect()->route('eroeffnungen.index');
    }

    // Rendern der View
    public function render()
    {
        return view('livewire.pages.eroeffnungen.create')
            ->layoutData(['pageTitle' => 'Eröffnung erstellen']);
    }
}
