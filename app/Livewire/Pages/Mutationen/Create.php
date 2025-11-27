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
/**
 * Erstellung einer Mutation
 */
class Create extends Component
{
    use MutationFormHooks;

    public MutationForm $form;

	public function mount(): void
	{
		// Flag setzen
		$this->form->isCreate = true;

		// Alle Dropdowns laden
		$this->form->loadArbeitsorte();
		$this->form->loadAnreden();
		$this->form->loadTitel();
		$this->form->loadMailendungen();
		$this->form->loadSapRollen();
		$this->form->loadAdusers();

		// Select2 initialisieren
		foreach ([
			"ad_user_id"               => $this->form->adusers,
			"anrede_id"                => $this->form->anreden,
			"titel_id"                 => $this->form->titel,
			"mailendung"               => $this->form->mailendungen,
			"arbeitsort_id"            => $this->form->arbeitsorte,
			"unternehmenseinheit_id"   => [],
			"abteilung_id"             => [],
			"funktion_id"              => [],
			"bezugsperson_id"          => $this->form->adusers,
			"vorlage_benutzer_id"      => $this->form->adusers,
			"abteilung2_id"            => [],
			// "kalender_berechtigungen"  => $this->form->adusersKalender,
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

			if (!empty($data["vorlage_benutzer_id"])) 
			{
				$vorlageUser = AdUser::findOrFail($data["vorlage_benutzer_id"]);
				$groups = LdapHelper::getAdGroups($vorlageUser->username);
				$data["ad_gruppen"] = $groups;
			}

			if (!empty($data["ad_user_id"])) 
			{				
				$adUser = AdUser::findOrFail($data["ad_user_id"]);
				$data["vorname_old"] = $adUser->firstname;
				$data["nachname_old"] = $adUser->lastname;
				$data["anrede_id_old"] = $adUser->anrede?->id;
				$data["titel_id_old"] = $adUser->titel?->id;
				$data["arbeitsort_id_old"] = $adUser->arbeitsort?->id;
				$data["unternehmenseinheit_id_old"] = $adUser->unternehmenseinheit?->id;
				$data["abteilung_id_old"] = $adUser->abteilung?->id;
				$data["funktion_id_old"] = $adUser->funktion?->id;
			}

			Mutation::create($data);

			session()->flash("success", "Mutation erfolgreich erstellt.");
			return redirect()->route("mutationen.index");

		} 
		catch (\Illuminate\Validation\ValidationException $e) 
		{
			throw $e; // Validierungsfehler weiterleiten

		} 
		catch (\Throwable $e) 
		{
			Logger::error("Fehler bei der Erstellung einer Mutation", [
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
