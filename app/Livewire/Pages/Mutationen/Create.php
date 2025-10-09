<?php

namespace App\Livewire\Pages\Mutationen;

use Throwable;
use Illuminate\Validation\ValidationException;
use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Livewire\Forms\MutationForm;
use App\Models\Mutation;
use App\Models\SapRolle;
use App\Models\AdUser;
use App\Livewire\Traits\MutationFormHooks;
use App\Utils\Logging\Logger;
use App\Utils\UserHelper;
use App\Utils\LdapHelper;

#[Layout("layouts.app")]
class Create extends Component
{
    use MutationFormHooks;

    public MutationForm $form;

    public function mount(): void
    {
        $this->form->isCreate = true;

        $this->form->loadArbeitsorte();
        $this->form->loadAnreden();
        $this->form->loadTitel();
        $this->form->loadMailendungen();
		$this->form->loadSapRollen();
		$this->form->loadAdusersKalender();

        $this->form->filter_mitarbeiter ? $this->form->adusers = [] : $this->form->loadAdusers(null);

        // Select2 initialisieren
        foreach ([
            "anrede_id"              => $this->form->anreden,
            "titel_id"               => $this->form->titel,
            "mailendung"             => $this->form->mailendungen,
            "arbeitsort_id"          => $this->form->arbeitsorte,
            "unternehmenseinheit_id" => [],
            "abteilung_id"           => [],
            "funktion_id"            => [],
            "bezugsperson_id"        => $this->form->adusers,
            "vorlage_benutzer_id"    => $this->form->adusers,
            "abteilung2_id"          => [],
			"kalender_berechtigungen" => $this->form->adusersKalender,
			"ad_user_id" => $this->form->adusersKalender,
        ] as $id => $options) {
            $this->dispatch("select2-options", id: $id, options: $options, value: null);
        }
    }

	public function save()
	{
		try 
		{
			$this->form->validate(
				$this->form->rules(),
				$this->form->messages(),
				$this->form->attributes()
			);

			$this->form->applyStatus();
			$data = $this->form->toArray();

			$data["antragsteller_id"] = auth()->user()?->adUser?->id;

			// nur laden, wenn eine Vorlage ausgewählt wurde
			if (!empty($data["vorlage_benutzer_id"])) {
				$vorlageUser = AdUser::findOrFail($data["vorlage_benutzer_id"]);
				$groups = LdapHelper::getAdGroups($vorlageUser->username);
				$data["ad_gruppen"] = $groups;
			}

			Mutation::create($data);

			session()->flash("success", "Mutation erfolgreich erstellt.");
			return redirect()->route("mutationen.index");

		} 
		catch (\Illuminate\Validation\ValidationException $e) 
		{
			// Validierungsfehler weiterleiten
			throw $e;

		} 
		catch (\Throwable $e) 
		{
			\App\Utils\Logging\Logger::error("Fehler bei Mutation", [
				"message" => $e->getMessage(),
				"trace"   => $e->getTraceAsString(),
				"data"    => $this->form->toArray(),
			]);

			$this->dispatch("open-modal", modal: "alert-modal", payload: [
				"message"  => "Es ist ein unerwarteter Fehler aufgetreten. Bitte wenden Sie sich an den Support.",
				"headline" => "Fehler",
				"color"    => "bg-danger",
				"icon"     => "ri-close-circle-line",
			]);

			return null;
		}
	}

    public function render()
    {
        return view("livewire.pages.mutationen.create")
            ->layoutData(["pageTitle" => "Mutation erstellen"]);
    }
}
