<?php

namespace App\Livewire\Pages\Mutationen;

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Livewire\Forms\MutationForm;
use App\Models\Mutation;
use App\Livewire\Traits\MutationFormHooks;
use App\Utils\AntragHelper;

#[Layout("layouts.app")]
class Edit extends Component
{
    use MutationFormHooks;

    public MutationForm $form;
    public Mutation $mutation;

    public function mount(Mutation $mutation): void
    {
		if (! AntragHelper::canView($mutation, auth()->user())) abort(403);

        $status = AntragHelper::statusForBearbeitung($mutation, auth()->user());

        $this->form->isCreate   = false;
        $this->form->fillFromModel($mutation);
        $this->mutation = $mutation;
        $this->form->isReadonly = ! $status['canEdit'];

        // Daten für Select2-Dropdowns laden
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
            "vorlage_benutzer_id"     => $this->form->adusers,
            "abteilung2_id"           => $this->form->abteilungen,
            "kalender_berechtigungen" => $this->form->adusersKalender,
            "ad_user_id"              => $this->form->adusers,
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

            $this->form->applyStatus($this->mutation);
            $data = $this->form->toArray();

            $this->mutation->update($data);

            session()->flash("success", "Mutation erfolgreich aktualisiert.");
            return redirect()->route("mutationen.index");

        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;

        } catch (\Throwable $e) {
            \App\Utils\Logging\Logger::error("Fehler beim Bearbeiten der Mutation", [
                "message" => $e->getMessage(),
                "trace"   => $e->getTraceAsString(),
                "data"    => $this->form->toArray(),
                "id"      => $this->mutation->id,
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
		$status = AntragHelper::statusForBearbeitung($this->mutation, auth()->user());

		return view("livewire.pages.mutationen.edit", [
			"form"           => $this->form,
			"mutation"       => $this->mutation,
			"canEdit"        => $status['canEdit'],
			"statusMessages" => $status['messages'],
		])->layoutData([
			"pageTitle" => "Mutation bearbeiten"
		]);
	}

}
