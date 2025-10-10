<?php

namespace App\Livewire\Pages\Mutationen;

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Livewire\Forms\MutationForm;
use App\Models\Mutation;
use App\Livewire\Traits\MutationFormHooks;
use App\Utils\AntragHelper;

#[Layout("layouts.app")]
class Show extends Component
{
    use MutationFormHooks;

    public MutationForm $form;
    public Mutation $mutation;

    public function mount(Mutation $mutation): void
    {
		if (! AntragHelper::canView($mutation, auth()->user())) abort(403);
		
        $this->form->isReadonly = true;
        $this->form->isCreate   = false;
        $this->form->fillFromModel($mutation);
        $this->mutation = $mutation;

        // Daten für Dropdowns laden
		$this->form->loadArbeitsorte($mutation);
		$this->form->loadUnternehmenseinheiten($mutation);
		$this->form->loadAbteilungen($mutation);
		$this->form->loadFunktionen($mutation);
		$this->form->loadAnreden($mutation);
		$this->form->loadTitel($mutation);
		$this->form->loadMailendungen();
		$this->form->loadSapRollen($mutation);
		$this->form->loadAdusers($mutation);

        // Select2-Dropdowns initialisieren
        foreach ([
			"ad_user_id" => $this->form->adusers,
            "anrede_id"               => $this->form->anreden,
            "titel_id"                => $this->form->titel,
            "mailendung"              => $this->form->mailendungen,
            "arbeitsort_id"           => $this->form->arbeitsorte,
            "unternehmenseinheit_id"  => $this->form->unternehmenseinheiten,
            "abteilung_id"            => $this->form->abteilungen,
            "funktion_id"             => $this->form->funktionen,
            "vorlage_benutzer_id"     => $this->form->adusers,
            "abteilung2_id"           => $this->form->abteilungen,
            "kalender_berechtigungen" => $this->form->adusersKalender,
        ] as $id => $options) {
            $this->dispatch(
                "select2-options",
                id: $id,
                options: $options,
                value: $this->form->$id
            );
        }
    }

    public function render()
    {
        return view("livewire.pages.mutationen.show")
            ->layoutData([
                "pageTitle" => "Mutation {$this->mutation->vorname} {$this->mutation->nachname}"
            ]);
    }
}
