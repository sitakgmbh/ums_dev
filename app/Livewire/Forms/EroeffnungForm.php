<?php

namespace App\Livewire\Forms;

use Livewire\Form;
use App\Models\AdUser;
use App\Models\Arbeitsort;
use App\Models\Unternehmenseinheit;
use App\Models\Abteilung;
use App\Models\Funktion;
use App\Models\Anrede;
use App\Models\Titel;

class EroeffnungForm extends Form
{
    public bool $isCreate = true;
    public bool $isReadonly = false;

    // --- Organisation ---
    public ?int $arbeitsort_id = null;
    public ?int $unternehmenseinheit_id = null;
    public ?int $abteilung_id = null;
    public ?int $funktion_id = null;
    public ?int $abteilung2_id = null;
    public bool $has_abteilung2 = false;

    public array $arbeitsorte = [];
    public array $unternehmenseinheiten = [];
    public array $abteilungen = [];
    public array $funktionen = [];

    // --- Personendaten ---
    public ?int $anrede_id = null;
    public ?int $titel_id = null;
    public string $vorname = '';
    public string $nachname = '';
    public ?string $vertragsbeginn = null;

    public array $anreden = [];
    public array $titel = [];
    public array $mailendungen = [];
    public ?string $mailendung = null;

    // --- Beziehungen ---
    public ?int $bezugsperson_id = null;
    public ?int $vorlage_benutzer_id = null;
    public array $adusers = [];

    // --- Optionen ---
    public bool $neue_konstellation = false;
    public bool $filter_mitarbeiter = true;

    public function rules(): array
    {
        return [
            'anrede_id'              => ['nullable', 'exists:anreden,id'],
            'titel_id'               => ['nullable', 'exists:titel,id'],
            'vorname'                => ['required', 'string', 'max:255'],
            'nachname'               => ['required', 'string', 'max:255'],
            'vertragsbeginn'         => ['required', 'date'],
            'arbeitsort_id'          => ['required', 'exists:arbeitsorte,id'],
            'unternehmenseinheit_id' => ['required', 'exists:unternehmenseinheiten,id'],
            'abteilung_id'           => ['required', 'exists:abteilungen,id'],
            'funktion_id'            => ['required', 'exists:funktionen,id'],
            'abteilung2_id'          => ['nullable', 'exists:abteilungen,id'],
            'has_abteilung2'         => ['boolean'],
            'bezugsperson_id'        => ['nullable', 'integer'],
            'vorlage_benutzer_id'    => ['nullable', 'integer'],
            'mailendung'             => ['nullable', 'string'],
            'neue_konstellation'     => ['boolean'],
            'filter_mitarbeiter'     => ['boolean'],
        ];
    }

    // --- Loader ---
    public function loadArbeitsorte(bool $all = false): void
    {
        $this->arbeitsorte = Arbeitsort::query()
            ->when(!$all, fn($q) => $q->where('enabled', true))
            ->orderBy('name')
            ->get(['id', 'name'])
            ->toArray();
    }

    public function loadAlleArbeitsorte(): void
    {
        $this->arbeitsorte = Arbeitsort::orderBy('name')->get(['id', 'name'])->toArray();
    }

    public function loadUnternehmenseinheiten(bool $all = false): void
    {
        if ($all || $this->neue_konstellation) {
            $this->unternehmenseinheiten = Unternehmenseinheit::orderBy('name')->get(['id', 'name'])->toArray();
            return;
        }

        if (!$this->arbeitsort_id) {
            $this->unternehmenseinheiten = [];
            return;
        }

        $this->unternehmenseinheiten = Unternehmenseinheit::whereHas('konstellationen', function ($q) {
                $q->where('arbeitsort_id', $this->arbeitsort_id);
            })
            ->where('enabled', true)
            ->orderBy('name')
            ->get(['id', 'name'])
            ->toArray();
    }

    public function loadAlleUnternehmenseinheiten(): void
    {
        $this->unternehmenseinheiten = Unternehmenseinheit::orderBy('name')->get(['id', 'name'])->toArray();
    }

    public function loadAbteilungen(bool $all = false): void
    {
        if ($all || $this->neue_konstellation) {
            $this->abteilungen = Abteilung::orderBy('name')->get(['id', 'name'])->toArray();
            return;
        }

        if (!$this->arbeitsort_id || !$this->unternehmenseinheit_id) {
            $this->abteilungen = [];
            return;
        }

        $this->abteilungen = Abteilung::whereHas('konstellationen', function ($q) {
                $q->where('arbeitsort_id', $this->arbeitsort_id)
                  ->where('unternehmenseinheit_id', $this->unternehmenseinheit_id);
            })
            ->where('enabled', true)
            ->orderBy('name')
            ->get(['id', 'name'])
            ->toArray();
    }

    public function loadAlleAbteilungen(): void
    {
        $this->abteilungen = Abteilung::orderBy('name')->get(['id', 'name'])->toArray();
    }

    public function loadFunktionen(bool $all = false): void
    {
        if ($all || $this->neue_konstellation) {
            $this->funktionen = Funktion::orderBy('name')->get(['id', 'name'])->toArray();
            return;
        }

        if (!$this->arbeitsort_id || !$this->unternehmenseinheit_id || !$this->abteilung_id) {
            $this->funktionen = [];
            return;
        }

        $this->funktionen = Funktion::whereHas('konstellationen', function ($q) {
                $q->where('arbeitsort_id', $this->arbeitsort_id)
                  ->where('unternehmenseinheit_id', $this->unternehmenseinheit_id)
                  ->where('abteilung_id', $this->abteilung_id);
            })
            ->where('enabled', true)
            ->orderBy('name')
            ->get(['id', 'name'])
            ->toArray();
    }

    public function loadAlleFunktionen(): void
    {
        $this->funktionen = Funktion::orderBy('name')->get(['id', 'name'])->toArray();
    }

    public function loadAnreden(): void
    {
        $this->anreden = Anrede::where('enabled', true)
            ->orderBy('name')
            ->get(['id', 'name'])
            ->toArray();
    }

    public function loadTitel(): void
    {
        $this->titel = Titel::where('enabled', true)
            ->orderBy('name')
            ->get(['id', 'name'])
            ->toArray();
    }

    public function loadMailendungen(): void
    {
        $this->mailendungen = [
            ['id' => '@waldhaus.ch', 'name' => '@waldhaus.ch'],
            ['id' => '@kliniken-gr.ch', 'name' => '@kliniken-gr.ch'],
            ['id' => '@example.org', 'name' => '@example.org'],
        ];
    }

	public function loadAdusers(?int $abteilungId = null): void
	{
		$query = AdUser::query()
			->with('funktion') // Funktion gleich mitladen
			->orderBy('display_name');

		if ($this->filter_mitarbeiter && $abteilungId) {
			$query->where('abteilung_id', $abteilungId);
		}

		$this->adusers = $query->get()->map(function ($user) {
			return [
				'id' => $user->id,
				'display_name' => $user->funktion
					? $user->display_name . ' (' . $user->funktion->name . ')'
					: $user->display_name,
			];
		})->toArray();
	}



public function updatedFormNeueKonstellation(bool $value): void
{
    if ($value) {
        // alle laden
        $this->form->loadAlleArbeitsorte();
        $this->form->loadAlleUnternehmenseinheiten();
        $this->form->loadAlleAbteilungen();
        $this->form->loadAlleFunktionen();
    } else {
        // gefiltert laden, Auswahl prüfen
        $this->form->loadArbeitsorte();

        // prüfen ob aktueller Arbeitsort noch gültig ist
        if ($this->form->arbeitsort_id && !collect($this->form->arbeitsorte)->pluck('id')->contains($this->form->arbeitsort_id)) {
            $this->form->arbeitsort_id = null;
        }

        $this->form->loadUnternehmenseinheiten();
        if ($this->form->unternehmenseinheit_id && !collect($this->form->unternehmenseinheiten)->pluck('id')->contains($this->form->unternehmenseinheit_id)) {
            $this->form->unternehmenseinheit_id = null;
        }

        $this->form->loadAbteilungen();
        if ($this->form->abteilung_id && !collect($this->form->abteilungen)->pluck('id')->contains($this->form->abteilung_id)) {
            $this->form->abteilung_id = null;
        }

        $this->form->loadFunktionen();
        if ($this->form->funktion_id && !collect($this->form->funktionen)->pluck('id')->contains($this->form->funktion_id)) {
            $this->form->funktion_id = null;
        }
    }

    // Kaskade refreshen
    $this->dispatch('select2-options', id: 'arbeitsort_id', options: $this->form->arbeitsorte, value: $this->form->arbeitsort_id);
    $this->dispatch('select2-options', id: 'unternehmenseinheit_id', options: $this->form->unternehmenseinheiten, value: $this->form->unternehmenseinheit_id);
    $this->dispatch('select2-options', id: 'abteilung_id', options: $this->form->abteilungen, value: $this->form->abteilung_id);
    $this->dispatch('select2-options', id: 'funktion_id', options: $this->form->funktionen, value: $this->form->funktion_id);
    $this->dispatch('select2-options', id: 'abteilung2_id', options: $this->form->abteilungen, value: $this->form->abteilung2_id);

    // Mitarbeiter nach Filter-Status aktualisieren
    if ($this->form->filter_mitarbeiter && !$this->form->abteilung_id) {
        $this->form->adusers = [];
    } else {
        $this->form->loadAdusers($this->form->filter_mitarbeiter ? $this->form->abteilung_id : null);
    }

    $this->dispatch('select2-options', id: 'bezugsperson_id', options: $this->form->adusers, value: $this->form->bezugsperson_id);
    $this->dispatch('select2-options', id: 'vorlage_benutzer_id', options: $this->form->adusers, value: $this->form->vorlage_benutzer_id);
}


    public function updatedHasAbteilung2($value): void
    {
        if (!$value) {
            $this->abteilung2_id = null;
            $this->dispatch('select2-options', id: 'abteilung2_id', options: [], value: null);
        } else {
            $this->abteilung2_id = null;
            $this->dispatch('select2-options', id: 'abteilung2_id', options: $this->abteilungen, value: null);
        }
    }

    public function loadAdusersForAbteilung(?int $abteilungId = null): void
    {
        $this->loadAdusers($abteilungId);
    }
}
