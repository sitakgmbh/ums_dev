<?php

namespace App\Livewire\Pages\Eroeffnungen;

use Throwable;
use Illuminate\Validation\ValidationException;
use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Livewire\Forms\EroeffnungForm;
use App\Livewire\Traits\EroeffnungFormHooks;
use App\Models\Eroeffnung;
use App\Models\SapRolle;
use App\Models\AdUser;
use App\Utils\Logging\Logger;
use App\Utils\UserHelper;
use App\Utils\LdapHelper;

#[Layout("layouts.app")]
/**
 * Erstellung einer Eröffnung
 */
class Create extends Component
{
    use EroeffnungFormHooks;

    public EroeffnungForm $form;

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

        // Select2-Dropdowns initialisieren
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

			if ($this->form->wiedereintritt) 
			{
				$data["benutzername"] = $this->form->benutzername;
				$data["email"] = $this->form->email;
			} 
			else 
			{
				$data["benutzername"] = UserHelper::generateUsername($data["vorname"], $data["nachname"]);
			}
			
			$data["passwort"] = UserHelper::generatePassword();

			if (empty($data["email"])) 
			{
				$data["email"] = UserHelper::generateEmail(
					$data["vorname"],
					$data["nachname"],
					$data["mailendung"],
					$username
				);
			}

			$vorlageUser = AdUser::findOrFail($data["vorlage_benutzer_id"]);
			$groups = LdapHelper::getAdGroups($vorlageUser->username);
			$data["ad_gruppen"] = $groups;

			Eroeffnung::create($data);

			session()->flash("success", "Eröffnung erfolgreich erstellt.");
			return redirect()->route("eroeffnungen.index");
		} 
		catch (\Illuminate\Validation\ValidationException $e) 
		{
			throw $e; // Validierungsfehler weiterleiten
		} 
		catch (\Throwable $e) 
		{
			\App\Utils\Logging\Logger::error("Fehler beim Erstellen einer Eröffnung Eröffnung", [
				"message" => $e->getMessage(),
				"trace"   => $e->getTraceAsString(),
				"data"    => $this->form->toArray(),
			]);

			// Zeigt dem Benutzer eine allgemeine Fehlermeldung an
			$this->dispatch("open-modal", modal: "alert-modal", payload: [
				"message"  => "Es ist ein Fehler beim aufgetreten. Bitte wende dich an den ICT-Servicedesk.",
				"headline" => "Fehler",
				"color"    => "bg-danger",
				"icon"     => "ri-close-circle-line",
			]);

			return null;
		}
	}

    public function render()
    {
        return view("livewire.pages.eroeffnungen.create")
            ->layoutData(["pageTitle" => "Eröffnung erstellen"]);
    }
}
