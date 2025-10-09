<?php

namespace App\Livewire\Pages\Eroeffnungen;

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Livewire\Forms\EroeffnungForm;
use App\Models\Eroeffnung;
use App\Livewire\Traits\EroeffnungFormHooks;
use App\Utils\AntragHelper;

#[Layout("layouts.app")]
class Edit extends Component
{
    use EroeffnungFormHooks;

    public EroeffnungForm $form;
    public Eroeffnung $eroeffnung;

    public function mount(Eroeffnung $eroeffnung): void
    {
		if (! AntragHelper::canView($eroeffnung, auth()->user())) abort(403);

        $status = AntragHelper::statusForBearbeitung($eroeffnung, auth()->user());

        $this->form->isCreate   = false;
        $this->form->fillFromModel($eroeffnung);
        $this->eroeffnung = $eroeffnung;
        $this->form->isReadonly = ! $status['canEdit'];

        $this->form->loadArbeitsorte($this->form->neue_konstellation);
        $this->form->loadAnreden();
        $this->form->loadTitel();
        $this->form->loadMailendungen();
        $this->form->loadSapRollen();
        $this->form->loadAdusersKalender();
        $this->form->loadUnternehmenseinheiten($this->form->neue_konstellation);
        $this->form->loadAbteilungen($this->form->neue_konstellation);
        $this->form->loadFunktionen($this->form->neue_konstellation);
        $this->form->loadAdusers($this->form->filter_mitarbeiter ? $this->form->abteilung_id : null);

        foreach ([
            "anrede_id"               => $this->form->anreden,
            "titel_id"                => $this->form->titel,
            "mailendung"              => $this->form->mailendungen,
            "arbeitsort_id"           => $this->form->arbeitsorte,
            "unternehmenseinheit_id"  => $this->form->unternehmenseinheiten,
            "abteilung_id"            => $this->form->abteilungen,
            "funktion_id"             => $this->form->funktionen,
            "bezugsperson_id"         => $this->form->adusers,
            "vorlage_benutzer_id"     => $this->form->adusers,
            "abteilung2_id"           => $this->form->abteilungen,
            "kalender_berechtigungen" => $this->form->adusersKalender,
        ] as $id => $options) {
            $this->dispatch("select2-options", id: $id, options: $options, value: $this->form->$id);
        }
    }

    public function save()
    {
        try {
            $this->form->validate(
                $this->form->rules(),
                $this->form->messages(),
                $this->form->attributes()
            );

            $this->form->applyStatus($this->eroeffnung);
            $data = $this->form->toArray();

            $this->eroeffnung->update($data);

            session()->flash("success", "Eröffnung erfolgreich aktualisiert.");
            return redirect()->route("eroeffnungen.index");

        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;

        } catch (\Throwable $e) {
            \App\Utils\Logging\Logger::error("Fehler beim Bearbeiten der Eröffnung", [
                "message" => $e->getMessage(),
                "trace"   => $e->getTraceAsString(),
                "data"    => $this->form->toArray(),
                "id"      => $this->eroeffnung->id,
            ]);

            $this->dispatch("open-modal", modal: "alert-modal", payload: [
                "message"  => "Es ist ein unerwarteter Fehler beim Bearbeiten aufgetreten. Bitte wenden Sie sich an den Support.",
                "headline" => "Fehler",
                "color"    => "bg-danger",
                "icon"     => "ri-close-circle-line",
            ]);

            return null;
        }
    }

	public function render()
	{
		$status = AntragHelper::statusForBearbeitung($this->eroeffnung, auth()->user());

		return view("livewire.pages.eroeffnungen.edit", [
			"form"           => $this->form,
			"eroeffnung"     => $this->eroeffnung,
			"canEdit"        => $status['canEdit'],
			"statusMessages" => $status['messages'],
		])->layoutData([
			"pageTitle" => "Eröffnung {$this->eroeffnung->vorname} {$this->eroeffnung->nachname} bearbeiten"
		]);
	}

}
