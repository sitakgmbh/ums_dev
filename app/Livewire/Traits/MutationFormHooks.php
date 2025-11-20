<?php

namespace App\Livewire\Traits;

use App\Models\AdUser;

/**
 * Event-Handler und Logik Formular Eröffnungen
 */
trait MutationFormHooks
{
    use ModalFlow;

    public function updatedFormArbeitsortId($value)
    {
        $this->form->unternehmenseinheit_id = null;
        $this->form->abteilung_id = null;
        $this->form->funktion_id = null;

		$this->form->loadUnternehmenseinheiten();

        $this->dispatch("select2-options", id: "unternehmenseinheit_id", options: $this->form->unternehmenseinheiten, value: null);
        $this->dispatch("select2-options", id: "abteilung_id", options: [], value: null);
        $this->dispatch("select2-options", id: "funktion_id", options: [], value: null);
        $this->dispatch("select2-options", id: "abteilung2_id", options: [], value: null);

        $this->form->filter_mitarbeiter ? $this->form->adusers = [] : $this->form->loadAdusers(null);

        $this->dispatch("select2-options", id: "vorlage_benutzer_id", options: $this->form->adusers, value: null);
    }

    public function updatedFormUnternehmenseinheitId($value)
    {
        $this->form->abteilung_id = null;
        $this->form->funktion_id = null;

		$this->form->loadAbteilungen();

        $this->dispatch("select2-options", id: "abteilung_id", options: $this->form->abteilungen, value: null);
        $this->dispatch("select2-options", id: "funktion_id", options: [], value: null);
        $this->dispatch("select2-options", id: "abteilung2_id", options: $this->form->abteilungen, value: null);

        $this->form->filter_mitarbeiter ? $this->form->adusers = [] : $this->form->loadAdusers(null);

        $this->dispatch("select2-options", id: "vorlage_benutzer_id", options: $this->form->adusers, value: null);
    }

	public function updatedFormAbteilungId($value): void
	{
		$this->form->funktion_id = null;
		$this->form->loadFunktionen();

		if ($this->form->filter_mitarbeiter && $value) 
		{
			$this->form->loadAdusers($value);
		} 
		else 
		{
			$this->form->loadAdusers(null);
		}

		$this->dispatch("select2-options", id: "funktion_id", options: $this->form->funktionen, value: $this->form->funktion_id);
		$this->dispatch("select2-options", id: "vorlage_benutzer_id", options: $this->form->adusers, value: $this->form->vorlage_benutzer_id);

		if ($this->form->has_abteilung2) 
		{
			$this->dispatch("select2-options", id: "abteilung2_id", options: $this->form->abteilungen, value: $this->form->abteilung2_id);
		}
	}

    public function updatedFormNeueKonstellation($value)
    {
        if ($value) 
		{
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
        $this->dispatch("select2-options", id: "vorlage_benutzer_id", options: $this->form->adusers, value: $this->form->vorlage_benutzer_id);
    }

    public function updatedFormFilterMitarbeiter(bool $value): void
    {
        if ($value) 
		{
            $this->form->abteilung_id
                ? $this->form->loadAdusers($this->form->abteilung_id)
                : $this->form->adusers = [];
        } 
		else 
		{
            $this->form->loadAdusers(null);
        }

        $this->dispatch("select2-options", id: "vorlage_benutzer_id", options: $this->form->adusers, value: $this->form->vorlage_benutzer_id);
    }

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

	protected function checkAllFlows(): void
	{
		if (empty($this->form->vorname) || empty($this->form->nachname)) 
		{
			return;
		}

		$flow = [];

		$eroeffnung = \App\Models\Eroeffnung::query()
			->where("vorname", $this->form->vorname)
			->where("nachname", $this->form->nachname)
			->first();

		if ($eroeffnung) 
		{
			$flow[] = [
				"id" => "components.modals.mutationen.mutation-vorhanden",
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

		if (!empty($flow)) 
		{
			$this->startModalFlow($flow);
		}
	}

	public function updatedFormAdUserId($value): void
	{
		if (!$value) return;

		$user = \App\Models\AdUser::with(['funktion', 'abteilung', 'unternehmenseinheit', 'arbeitsort', 'anrede', 'titel'])
			->find($value);

		if (!$user) return;

		$this->form->anrede_id = $user->anrede_id;
		$this->form->titel_id = $user->titel_id;
		$this->form->arbeitsort_id = $user->arbeitsort_id;
		$this->form->unternehmenseinheit_id = $user->unternehmenseinheit_id;
		$this->form->abteilung_id = $user->abteilung_id;
		$this->form->funktion_id = $user->funktion_id;

		$this->form->loadUnternehmenseinheiten();
		$this->form->loadAbteilungen();
		$this->form->loadFunktionen();

		$this->dispatch("select2-options", id: "anrede_id", options: $this->form->anreden, value: $this->form->anrede_id);
		$this->dispatch("select2-options", id: "titel_id", options: $this->form->titel, value: $this->form->titel_id);
		$this->dispatch("select2-options", id: "arbeitsort_id", options: $this->form->arbeitsorte, value: $this->form->arbeitsort_id);
		$this->dispatch("select2-options", id: "unternehmenseinheit_id", options: $this->form->unternehmenseinheiten, value: $this->form->unternehmenseinheit_id);
		$this->dispatch("select2-options", id: "abteilung_id", options: $this->form->abteilungen, value: $this->form->abteilung_id);
		$this->dispatch("select2-options", id: "funktion_id", options: $this->form->funktionen, value: $this->form->funktion_id);

		$mutation = \App\Models\Mutation::query()
			->where("ad_user_id", $user->id)
			->where("archiviert", false)
			->first();

		if ($mutation) 
		{
			$this->dispatch("open-modal", modal: "components.modals.mutationen.mutation-vorhanden", payload: [
				"mutation" => [
					"id"       => $mutation->id,
					"erstellt" => $mutation->created_at?->format("d.m.Y H:i"),
				],
			]);

			$this->form->ad_user_id = null;
			$this->dispatch("select2-clear", id: "ad_user_id");

			return;
		}

		if ($this->form->filter_mitarbeiter) 
		{
			if (!empty($this->form->abteilung_id)) 
			{
				$this->form->loadAdusers($this->form->abteilung_id);
			} 
			else 
			{
				$this->form->adusers = [];
			}
		} 
		else 
		{
			$this->form->loadAdusers(null);
		}

		if ($this->form->vorlage_benutzer_id && !collect($this->form->adusers)->pluck('id')->contains($this->form->vorlage_benutzer_id)) 
		{
			$this->form->vorlage_benutzer_id = null;
			$this->dispatch("select2-clear", id: "vorlage_benutzer_id");
		}

		$this->dispatch("select2-options", id: "vorlage_benutzer_id", options: $this->form->adusers, value: $this->form->vorlage_benutzer_id);
	}

	public function updatedFormEnableAnrede($value): void
	{
		$this->dispatch("toggle-select", id: "anrede_id", enabled: (bool)$value && !$this->form->isReadonly);
	}

	public function updatedFormEnableTitel($value): void
	{
		$this->dispatch("toggle-select", id: "titel_id", enabled: (bool)$value && !$this->form->isReadonly);
	}

	public function updatedFormEnableArbeitsort($value): void
	{
		$enabled = (bool)$value && !$this->form->isReadonly;
		$this->dispatch("toggle-select", id: "arbeitsort_id", enabled: $enabled);

		if ($enabled && empty($this->form->arbeitsort_id)) 
		{
			$fallback = null;

			if (!empty($this->form->ad_user_id)) 
			{
				$ad = \App\Models\AdUser::find($this->form->ad_user_id);
				$fallback = $ad?->arbeitsort_id;
			} 
			elseif (property_exists($this, 'mutation') && $this->mutation?->adUser) 
			{
				$fallback = $this->mutation->adUser->arbeitsort_id ?? null;
			}

			if ($fallback) 
			{
				$this->form->arbeitsort_id = $fallback;
				$this->form->loadArbeitsorte();
				$this->dispatch('select2-options', id: 'arbeitsort_id', options: $this->form->arbeitsorte, value: $fallback);
			}
		}
	}

	public function updatedFormEnableUnternehmenseinheit($value): void
	{
		$enabled = (bool)$value && !$this->form->isReadonly;
		$this->dispatch("toggle-select", id: "unternehmenseinheit_id", enabled: $enabled);

		if ($enabled && empty($this->form->unternehmenseinheit_id)) 
		{
			$fallback = null;

			if (!empty($this->form->ad_user_id)) 
			{
				$ad = \App\Models\AdUser::find($this->form->ad_user_id);
				$fallback = $ad?->unternehmenseinheit_id;
			} 
			elseif (property_exists($this, 'mutation') && $this->mutation?->adUser) 
			{
				$fallback = $this->mutation->adUser->unternehmenseinheit_id ?? null;
			}

			if ($fallback) 
			{
				$this->form->unternehmenseinheit_id = $fallback;
				$this->form->loadUnternehmenseinheiten();
				$this->dispatch('select2-options', id: 'unternehmenseinheit_id', options: $this->form->unternehmenseinheiten, value: $fallback);
			}
		}
	}

	public function updatedFormEnableAbteilung($value): void
	{
		$enabled = (bool)$value && !$this->form->isReadonly;
		$this->dispatch("toggle-select", id: "abteilung_id", enabled: $enabled);

		if ($enabled && empty($this->form->abteilung_id)) 
		{
			$fallback = null;

			if (!empty($this->form->ad_user_id)) 
			{
				$ad = \App\Models\AdUser::find($this->form->ad_user_id);
				$fallback = $ad?->abteilung_id;
			} 
			elseif (property_exists($this, 'mutation') && $this->mutation?->adUser) 
			{
				$fallback = $this->mutation->adUser->abteilung_id ?? null;
			}

			if ($fallback) 
			{
				$this->form->abteilung_id = $fallback;
				$this->form->loadAbteilungen();
				$this->dispatch('select2-options', id: 'abteilung_id', options: $this->form->abteilungen, value: $fallback);
			}
		}
	}

	public function updatedFormEnableFunktion($value): void
	{
		$enabled = (bool)$value && !$this->form->isReadonly;
		$this->dispatch("toggle-select", id: "funktion_id", enabled: $enabled);

		if ($enabled && empty($this->form->funktion_id)) 
		{
			$fallback = null;

			if (!empty($this->form->ad_user_id)) 
			{
				$ad = \App\Models\AdUser::find($this->form->ad_user_id);
				$fallback = $ad?->funktion_id;
			} 
			elseif (property_exists($this, 'mutation') && $this->mutation?->adUser) 
			{
				$fallback = $this->mutation->adUser->funktion_id ?? null;
			}

			if ($fallback) 
			{
				$this->form->funktion_id = $fallback;
				$this->form->loadFunktionen();
				$this->dispatch('select2-options', id: 'funktion_id', options: $this->form->funktionen, value: $fallback);
			}
		}
	}

	public function updatedFormEnableMailendung($value): void
	{
		$this->dispatch("toggle-select", id: "mailendung", enabled: (bool)$value && !$this->form->isReadonly);
	}

	public function updatedFormEnableVorlage($value): void
	{
		$this->dispatch("toggle-select", id: "vorlage_benutzer_id", enabled: (bool)$value && !$this->form->isReadonly);
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
