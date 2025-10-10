<?php

namespace App\Livewire\Pages\Eroeffnungen;

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Livewire\Forms\EroeffnungForm;
use App\Models\Eroeffnung;
use App\Livewire\Traits\EroeffnungFormHooks;
use App\Utils\AntragHelper;

#[Layout("layouts.app")]
/**
 * Anzeigen einer Eröffnung
 */
class Show extends Component
{
    use EroeffnungFormHooks;

    public EroeffnungForm $form;
    public Eroeffnung $eroeffnung;

    public function mount(Eroeffnung $eroeffnung): void
    {
		// Nur eigene Anträge
		if (! AntragHelper::canView($eroeffnung, auth()->user())) abort(403);

        $this->form->isReadonly = true;
        $this->form->isCreate   = false;
		$this->form->fillFromModel($eroeffnung);
        $this->eroeffnung = $eroeffnung;

		$this->form->loadArbeitsorte($eroeffnung);
		$this->form->loadAnreden($eroeffnung);
		$this->form->loadTitel($eroeffnung);
		$this->form->loadMailendungen();
		$this->form->loadSapRollen($eroeffnung);
		$this->form->loadAdusersKalender($eroeffnung);
		$this->form->loadUnternehmenseinheiten($eroeffnung);
		$this->form->loadAbteilungen($eroeffnung);
		$this->form->loadFunktionen($eroeffnung);
		$this->form->loadAdusers($eroeffnung);

        // Select2-Dropdowns initialisieren
        foreach ([
            "anrede_id"              => $this->form->anreden,
            "titel_id"               => $this->form->titel,
            "mailendung"             => $this->form->mailendungen,
            "arbeitsort_id"          => $this->form->arbeitsorte,
            "unternehmenseinheit_id" => $this->form->unternehmenseinheiten,
            "abteilung_id"           => $this->form->abteilungen,
            "funktion_id"            => $this->form->funktionen,
            "bezugsperson_id"        => $this->form->adusers,
            "vorlage_benutzer_id"    => $this->form->adusers,
            "abteilung2_id"          => $this->form->abteilungen,
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
        return view("livewire.pages.eroeffnungen.show")
            ->layoutData(["pageTitle" => "Eröffnung {$this->eroeffnung->vorname} {$this->eroeffnung->nachname}"]);
    }
}
