<?php

namespace App\Livewire\Traits;

use App\Models\AdUser;
use App\Utils\Logging\Logger;

/**
 * Event-Handler und Logik Formular Eröffnungen
 */
trait EroeffnungFormHooks
{
	use ModalFlow;
		
	// Event Arbeitsort geändert
	public function updatedFormArbeitsortId(?int $value): void
	{
		// Wenn kein Arbeitsort alle Folgefelder leeren
		if (!$value) 
		{
			$this->form->unternehmenseinheit_id = null;
			$this->form->abteilung_id = null;
			$this->form->funktion_id = null;

			$this->form->unternehmenseinheiten = [];
			$this->form->abteilungen = [];
			$this->form->funktionen = [];

			$this->dispatch('select2-options', id: 'unternehmenseinheit_id', options: [], value: null);
			$this->dispatch('select2-options', id: 'abteilung_id', options: [], value: null);
			$this->dispatch('select2-options', id: 'funktion_id', options: [], value: null);
			$this->dispatch('select2-options', id: 'abteilung2_id', options: [], value: null);
			return;
		}

		// Unternehmenseinheiten basierend auf Arbeitsort laden
		$this->form->loadUnternehmenseinheiten();

		// Falls Auswahl ungültig wird Felder zurücksetzen
		if ($this->form->unternehmenseinheit_id && !collect($this->form->unternehmenseinheiten)->pluck('id')->contains($this->form->unternehmenseinheit_id)) 
		{
			$this->form->unternehmenseinheit_id = null;
		}

		// Abteilungen und Funktionen neu laden
		$this->form->loadAbteilungen();
		$this->form->loadFunktionen();

		$this->dispatch('select2-options', id: 'unternehmenseinheit_id', options: $this->form->unternehmenseinheiten, value: $this->form->unternehmenseinheit_id);
		$this->dispatch('select2-options', id: 'abteilung_id', options: $this->form->abteilungen, value: $this->form->abteilung_id);
		$this->dispatch('select2-options', id: 'funktion_id', options: $this->form->funktionen, value: $this->form->funktion_id);

		// Mitarbeiterliste aktualisieren
		if ($this->form->filter_mitarbeiter) 
		{
			$this->form->loadAdusers();
			$this->dispatch('select2-options', id: 'bezugsperson_id', options: $this->form->adusers, value: $this->form->bezugsperson_id);
			$this->dispatch('select2-options', id: 'vorlage_benutzer_id', options: $this->form->adusers, value: $this->form->vorlage_benutzer_id);
		}
	}

    // Event Unternehmenseinheit geändert
	public function updatedFormUnternehmenseinheitId(?int $value): void
	{
		// Abteilungen und Funktionen zurücksetzen
		$this->form->abteilung_id = null;
		$this->form->funktion_id = null;

		// Neue Daten laden
		$this->form->loadAbteilungen();
		$this->form->loadFunktionen();

		$this->dispatch('select2-options', id: 'abteilung_id', options: $this->form->abteilungen, value: $this->form->abteilung_id);
		$this->dispatch('select2-options', id: 'funktion_id', options: $this->form->funktionen, value: $this->form->funktion_id);
		$this->dispatch('select2-options', id: 'abteilung2_id', options: $this->form->abteilungen, value: $this->form->abteilung2_id);

		// Mitarbeiter laden
		if ($this->form->filter_mitarbeiter) 
		{
			$this->form->loadAdusers();
			$this->dispatch('select2-options', id: 'bezugsperson_id', options: $this->form->adusers, value: $this->form->bezugsperson_id);
			$this->dispatch('select2-options', id: 'vorlage_benutzer_id', options: $this->form->adusers, value: $this->form->vorlage_benutzer_id);
		}
	}

    // Event Abteilung geändert
	public function updatedFormAbteilungId(?int $value): void
	{
		$this->form->funktion_id = null;
		$this->form->loadFunktionen();

		// Mitarbeiter neu laden (abhängig vom Filter)
		if ($this->form->filter_mitarbeiter) 
		{
			$this->form->loadAdusers();
		} 
		else 
		{
			$this->form->loadAdusers(null);
		}

		// Select2-Updates
		$this->dispatch('select2-options', id: 'funktion_id', options: $this->form->funktionen, value: $this->form->funktion_id);
		$this->dispatch('select2-options', id: 'bezugsperson_id', options: $this->form->adusers, value: $this->form->bezugsperson_id);
		$this->dispatch('select2-options', id: 'vorlage_benutzer_id', options: $this->form->adusers, value: $this->form->vorlage_benutzer_id);

		if ($this->form->has_abteilung2) 
		{
			$this->dispatch('select2-options', id: 'abteilung2_id', options: $this->form->abteilungen, value: $this->form->abteilung2_id);
		}
	}

    // Checkbox Neue Konstellation geklickt
    public function updatedFormNeueKonstellation($value)
    {
		if ($value) 
		{
			// Neue Konstellation
			$this->form->loadArbeitsorte();
			$this->form->loadUnternehmenseinheiten();
			$this->form->loadAbteilungen();
			$this->form->loadFunktionen();
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

    // Checkbox Mitarbeiter filtern geklickt
	public function updatedFormFilterMitarbeiter(bool $value): void
	{
		// Wenn Filter aktiv ist nur Benutzer aus gewählter Abteilung
		if ($value) 
		{
			if ($this->form->abteilung_id) 
			{
				// Nur Benutzer der gewählten Abteilung
				$this->form->loadAdusers($this->form->abteilung_id);
			} 
			else 
			{
				// Leeres Dropdown wenn keine Abteilung ausgewählt
				$this->form->adusers = [];
			}
		} 
		else 
		{
			$this->form->loadAdusers(); // Wenn Filter deaktiviert ist alle aktiven Benutzer
		}

		// Select2 Dropdowns aktualisieren
		$this->dispatch('select2-options', id: 'bezugsperson_id', options: $this->form->adusers, value: $this->form->bezugsperson_id);
		$this->dispatch('select2-options', id: 'vorlage_benutzer_id', options: $this->form->adusers, value: $this->form->vorlage_benutzer_id);
	}

    // Checkbox Zweite Abteilung geklickt
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

	// Wert Feld Vorname geändert
	public function updatedFormVorname($value): void
	{
		$this->checkAllFlows();
	}

	// Wert Feld Nachname geändert
	public function updatedFormNachname($value): void
	{
		$this->checkAllFlows();
	}

	// Modal-Flow starten
	protected function checkAllFlows(): void
	{
		if (empty($this->form->vorname) || empty($this->form->nachname)) 
		{
			return;
		}

		$flow = [];

		// Eröffnung mit Vor- und Nachname vorhanden?
		$eroeffnung = \App\Models\Eroeffnung::query()
			->where("archiviert", false)
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
			$mailendung = $this->form->mailendung ?? "pdgr.ch";
			$vorlaeufigeEmail = strtolower(str_replace(" ", "", $this->form->vorname)) . "." . strtolower(str_replace(" ", "", $this->form->nachname)) . "@" . $mailendung;

			$flow[] = [
				"id" => "components.modals.eroeffnungen.email-bearbeiten",
				"payload" => ["email" => $vorlaeufigeEmail],
			];
		}

		if (!empty($flow)) 
		{
			$this->startModalFlow($flow);
		}
	}

	// Beim Bestätigen eines Wiedereintritts
	#[\Livewire\Attributes\On("wiedereintritt-selected")]
	public function handleWiedereintrittSelected($payload = []): void
	{
		$this->form->wiedereintritt = true;
	}

	// Beim Speichern der vorläufigen E-Mail-Adresse
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
