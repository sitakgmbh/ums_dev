<?php

namespace App\Livewire\Traits;

use App\Models\AdUser;
use App\Utils\Logging\Logger;

trait EroeffnungFormHooks
{
	use ModalFlow;
		
	// Dropdown-Kaskade: Arbeitsort geändert
    public function updatedFormArbeitsortId($value)
    {
        $this->form->unternehmenseinheit_id = null;
        $this->form->abteilung_id = null;
        $this->form->funktion_id = null;

        $this->form->loadUnternehmenseinheiten($this->form->neue_konstellation);

        $this->dispatch("select2-options", id: "unternehmenseinheit_id", options: $this->form->unternehmenseinheiten, value: null);
        $this->dispatch("select2-options", id: "abteilung_id", options: [], value: null);
        $this->dispatch("select2-options", id: "funktion_id", options: [], value: null);
        $this->dispatch("select2-options", id: "abteilung2_id", options: [], value: null);

        $this->form->filter_mitarbeiter ? $this->form->adusers = [] : $this->form->loadAdusers(null);

        $this->dispatch("select2-options", id: "bezugsperson_id", options: $this->form->adusers, value: null);
        $this->dispatch("select2-options", id: "vorlage_benutzer_id", options: $this->form->adusers, value: null);
    }

    // Dropdown-Kaskade: Unternehmenseinheit geändert
    public function updatedFormUnternehmenseinheitId($value)
    {
        $this->form->abteilung_id = null;
        $this->form->funktion_id = null;

        $this->form->loadAbteilungen($this->form->neue_konstellation);

        $this->dispatch("select2-options", id: "abteilung_id", options: $this->form->abteilungen, value: null);
        $this->dispatch("select2-options", id: "funktion_id", options: [], value: null);
        $this->dispatch("select2-options", id: "abteilung2_id", options: $this->form->abteilungen, value: null);

        $this->form->filter_mitarbeiter ? $this->form->adusers = [] : $this->form->loadAdusers(null);

        $this->dispatch("select2-options", id: "bezugsperson_id", options: $this->form->adusers, value: null);
        $this->dispatch("select2-options", id: "vorlage_benutzer_id", options: $this->form->adusers, value: null);
    }

    // Dropdown-Kaskade: Abteilung geändert
    public function updatedFormAbteilungId($value): void
    {
        $this->form->funktion_id = null;
        $this->form->loadFunktionen($this->form->neue_konstellation);

        $this->form->loadAdusers($this->form->filter_mitarbeiter ? $value : null);

        $this->dispatch("select2-options", id: "funktion_id", options: $this->form->funktionen, value: $this->form->funktion_id);
        $this->dispatch("select2-options", id: "bezugsperson_id", options: $this->form->adusers, value: $this->form->bezugsperson_id);
        $this->dispatch("select2-options", id: "vorlage_benutzer_id", options: $this->form->adusers, value: $this->form->vorlage_benutzer_id);

        if ($this->form->has_abteilung2) 
		{
            $this->dispatch("select2-options", id: "abteilung2_id", options: $this->form->abteilungen, value: $this->form->abteilung2_id);
        }
    }

    // Checkbox: Neue Konstellation
    public function updatedFormNeueKonstellation($value)
    {
        if ($value) 
		{
            $this->form->loadAlleArbeitsorte();
            $this->form->loadAlleUnternehmenseinheiten();
            $this->form->loadAlleAbteilungen();
            $this->form->loadAlleFunktionen();
        } 
		else 
		{
            $this->form->arbeitsort_id = null;
            $this->form->unternehmenseinheit_id = null;
            $this->form->abteilung_id = null;
            $this->form->funktion_id = null;
            $this->form->abteilung2_id = null;

            $this->form->loadArbeitsorte();
            $this->form->unternehmenseinheiten = [];
            $this->form->abteilungen = [];
            $this->form->funktionen = [];
        }

        $this->dispatch("select2-options", id: "arbeitsort_id", options: $this->form->arbeitsorte, value: $this->form->arbeitsort_id);
        $this->dispatch("select2-options", id: "unternehmenseinheit_id", options: $this->form->unternehmenseinheiten, value: $this->form->unternehmenseinheit_id);
        $this->dispatch("select2-options", id: "abteilung_id", options: $this->form->abteilungen, value: $this->form->abteilung_id);
        $this->dispatch("select2-options", id: "funktion_id", options: $this->form->funktionen, value: $this->form->funktion_id);
        $this->dispatch("select2-options", id: "abteilung2_id", options: $this->form->abteilungen, value: $this->form->abteilung2_id);

        $this->form->filter_mitarbeiter && !$this->form->abteilung_id ? $this->form->adusers = [] : $this->form->loadAdusers($this->form->filter_mitarbeiter ? $this->form->abteilung_id : null);

        $this->dispatch("select2-options", id: "bezugsperson_id", options: $this->form->adusers, value: $this->form->bezugsperson_id);
        $this->dispatch("select2-options", id: "vorlage_benutzer_id", options: $this->form->adusers, value: $this->form->vorlage_benutzer_id);
    }

    // Checkbox: Mitarbeiter filtern
    public function updatedFormFilterMitarbeiter(bool $value): void
    {
        if ($value) 
		{
            $this->form->abteilung_id ? $this->form->loadAdusers($this->form->abteilung_id) : $this->form->adusers = [];
        } 
		else 
		{
            $this->form->loadAdusers(null);
        }

        $this->dispatch("select2-options", id: "bezugsperson_id", options: $this->form->adusers, value: $this->form->bezugsperson_id);
        $this->dispatch("select2-options", id: "vorlage_benutzer_id", options: $this->form->adusers, value: $this->form->vorlage_benutzer_id);
    }

    // Checkbox: Zweite Abteilung
    public function updatedFormHasAbteilung2($value)
    {
        if (!$value) 
		{
            $this->form->abteilung2_id = null;
            $this->dispatch("select2-options", id: "abteilung2_id", options: [], value: null);
        } 
		else 
		{
            $this->form->abteilung2_id = null;
            $this->dispatch("select2-options", id: "abteilung2_id", options: $this->form->abteilungen, value: null);
        }
    }

	// Feldänderungen: Vorname / Nachname
	public function updatedFormVorname($value): void
	{
		$this->checkAllFlows();
	}

	public function updatedFormNachname($value): void
	{
		$this->checkAllFlows();
	}

	protected function checkAllFlows(): void
	{
		if (empty($this->form->vorname) || empty($this->form->nachname)) 
		{
			return;
		}

		$flow = [];

		// Eröffnung vorhanden?
		$eroeffnung = \App\Models\Eroeffnung::query()
			->where("vorname", $this->form->vorname)
			->where("nachname", $this->form->nachname)
			->first();

		if ($eroeffnung) 
		{
			$flow[] = [
				"id" => "components.modals.eroeffnungen.eroeffnung-vorhanden",
				"payload" => [
					"eroeffnung" => [
						"id"        => $eroeffnung->id,
						"vorname"   => $eroeffnung->vorname,
						"nachname"  => $eroeffnung->nachname,
						"erstellt"  => $eroeffnung->created_at?->format("d.m.Y H:i"),
						"aduser_id" => $eroeffnung->aduser_id,
					],
				],
			];
		}

		// Wiedereintritt?
		$users = AdUser::query()
			->where("firstname", $this->form->vorname)
			->where("lastname", $this->form->nachname)
			->get();

		if ($users->isNotEmpty()) 
		{
			$flow[] = [
				"id" => "components.modals.eroeffnungen.wiedereintritt",
				"payload" => [
					"vorname" => $this->form->vorname,
					"nachname" => $this->form->nachname,
					"users"   => $users->map(fn ($u) => [
						"id"           => $u->id,
						"vorname"      => $u->firstname,
						"nachname"     => $u->lastname,
						"email"        => $u->email,
						"initials"     => $u->initials,
						"beschreibung" => $u->description,
						"funktion"     => optional($u->funktion)->name,
						"enabled"      => $u->is_enabled,
					])->toArray(),
				],
			];
		}

		// Doppelnamen?
		if (str_contains(trim($this->form->vorname), " ") || str_contains(trim($this->form->nachname), " ")) 
		{
			$flow[] = [
				"id" => "components.modals.eroeffnungen.email-bearbeiten",
				"payload" => ["email" => $this->form->email],
			];
		}

		if (!empty($flow)) 
		{
			$this->startModalFlow($flow);
		}
	}

	#[\Livewire\Attributes\On("wiedereintritt-selected")]
	public function handleWiedereintrittSelected($payload = []): void
	{
		$this->form->wiedereintritt = true;
	}

	#[\Livewire\Attributes\On("email-bearbeiten-selected")]
	public function handleEmailOverrideSelected($payload = []): void
	{
		if (is_array($payload) && isset($payload["email"])) 
		{
			$this->form->email = $payload["email"];
		} 
		elseif (is_string($payload)) 
		{
			$this->form->email = $payload;
		}
	}
}
