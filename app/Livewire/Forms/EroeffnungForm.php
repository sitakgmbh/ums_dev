<?php

namespace App\Livewire\Forms;

use Livewire\Form;
use Illuminate\Support\Str;
use App\Models\Eroeffnung;
use App\Models\AdUser;
use App\Models\Arbeitsort;
use App\Models\Unternehmenseinheit;
use App\Models\Abteilung;
use App\Models\Funktion;
use App\Models\Anrede;
use App\Models\Titel;
use App\Models\SapRolle;
use App\Utils\DateHelper;

class EroeffnungForm extends Form
{
    public bool $isCreate = true;
    public bool $isReadonly = false;

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

    public ?int $anrede_id = null;
    public ?int $titel_id = null;
    public string $vorname = "";
    public string $nachname = "";
    public ?string $vertragsbeginn = null;

    public array $anreden = [];
    public array $titel = [];
    public array $mailendungen = [];
    public ?string $mailendung = null;

    public ?int $bezugsperson_id = null;
    public ?int $vorlage_benutzer_id = null;
    public array $adusers = [];

    public bool $neue_konstellation = false;
    public bool $filter_mitarbeiter = true;
    public bool $wiedereintritt = false;
    public ?string $email = null;
	public ?string $kommentar = null;

	public bool $kis_status = false;

	public array $sapRollen = [];
	public bool $sap_status = false;
	public ?int $sap_rolle_id = null;

	public bool $is_lei = false;

	public bool $tel_status = false;
	public ?string $tel_auswahl = null;
	public ?string $tel_nr = null;
	public bool $tel_tischtel = false;
	public bool $tel_mobiltel = false;
	public bool $tel_ucstd = false;
	public bool $tel_alarmierung = false;
	public ?string $tel_headset = null;

	public bool $raumbeschriftung_flag = false;
	public ?string $raumbeschriftung = null;

	public bool $key_waldhaus = false;
	public bool $key_wh_badge = false;
	public bool $key_wh_schluessel = false;

	public bool $key_beverin = false;
	public bool $key_be_badge = false;
	public bool $key_be_schluessel = false;

	public bool $key_rothenbr = false;
	public bool $key_rb_badge = false;
	public bool $key_rb_schluessel = false;

	public bool $berufskleider = false;
	public bool $garderobe = false;
	
	public bool $vorab_lizenzierung = false;
	public array $kalender_berechtigungen = [];
	public array $adusersKalender = [];

	public int $status_sap = 0;
	public int $status_tel = 0;
	public int $status_kis = 0;
	public int $status_auftrag = 0;

	public function rules(): array
	{
		$rules = [
			"anrede_id" => ["required", "exists:anreden,id"],
			"titel_id" => ["nullable", "exists:titel,id"],
			"vorname" => ["required", "string", "max:255"],
			"nachname" => ["required", "string", "max:255"],
			"arbeitsort_id" => ["required", "exists:arbeitsorte,id"],
			"unternehmenseinheit_id" => ["required", "exists:unternehmenseinheiten,id"],
			"abteilung_id" => ["required", "exists:abteilungen,id"],
			"funktion_id" => ["required", "exists:funktionen,id"],
			"has_abteilung2" => ["boolean"],
			"abteilung2_id" => ["required_if:has_abteilung2,true", "nullable", "exists:abteilungen,id"],
			"bezugsperson_id" => ["required", "integer", "exists:ad_users,id"],
			"vorlage_benutzer_id" => ["required", "integer", "exists:ad_users,id"],
			"mailendung" => ["required", "string"],
			"neue_konstellation" => ["boolean"],
			"filter_mitarbeiter" => ["boolean"],
			"wiedereintritt" => ["boolean"],
			"email" => ["nullable", "email", "max:255"],
			"kommentar" => ["nullable", "string", "max:1000"],
			"kis_status" => ["boolean"],
			"sap_status" => ["boolean"],
			"is_lei" => ["boolean"],
			"tel_status" => ["boolean"],
			"key_waldhaus" => ["boolean"],
			"key_beverin" => ["boolean"],
			"key_rothenbr" => ["boolean"],
			"berufskleider" => ["boolean"],
			"garderobe" => ["boolean"],
			"tel_auswahl" => ["nullable", "string", "in:uebernehmen,neu,manuell"],
			"tel_nr" => ["nullable", "string", "max:50"],
			"tel_tischtel" => ["boolean"],
			"tel_mobiltel" => ["boolean"],
			"tel_ucstd" => ["boolean"],
			"tel_alarmierung" => ["boolean"],
			"tel_headset" => ["nullable", "string", "in:mono,stereo"],
			"vorab_lizenzierung" => ["boolean"],
			"vertragsbeginn" => [
				"required",
				"date",
				function ($attribute, $value, $fail) {
					if ($message = DateHelper::validateVertragsbeginn($value)) {
						$fail($message);
					}
				},
			],
		];

		if ($this->sap_status) 
		{
			$rules["sap_rolle_id"] = ["required", "exists:sap_rollen,id"];
		}

		if ($this->raumbeschriftung_flag) 
		{
			$rules["raumbeschriftung"] = ["required", "string", "max:255"];
		}

		if ($this->key_waldhaus) 
		{
			$rules["key_wh_badge"] = ["boolean"];
			$rules["key_wh_schluessel"] = ["boolean"];

			$rules["key_waldhaus"] = [
				function ($attribute, $value, $fail) {
					if (!$this->key_wh_badge && !$this->key_wh_schluessel) 
					{
						$fail("Bitte mindestens eine Option bei Schlüsselrecht Klinik Waldhaus wählen.");
					}
				}
			];
		}

		if ($this->key_beverin) 
		{
			$rules["key_be_badge"] = ["boolean"];
			$rules["key_be_schluessel"] = ["boolean"];

			$rules["key_beverin"] = [
				function ($attribute, $value, $fail) {
					if (!$this->key_be_badge && !$this->key_be_schluessel) 
					{
						$fail("Bitte mindestens eine Option bei Schlüsselrecht Klinik Beverin wählen.");
					}
				}
			];
		}

		if ($this->key_rothenbr) 
		{
			$rules["key_rb_badge"] = ["boolean"];
			$rules["key_rb_schluessel"] = ["boolean"];

			$rules["key_rothenbr"] = [
				function ($attribute, $value, $fail) {
					if (!$this->key_rb_badge && !$this->key_rb_schluessel) 
					{
						$fail("Bitte mindestens eine Option bei Schlüsselrecht Rothenbrunnen wählen.");
					}
				}
			];
		}

		if ($this->tel_status) 
		{
			$rules["tel_auswahl"][] = "required";

			if (in_array($this->tel_auswahl, ["uebernehmen", "manuell"])) 
			{
				$rules["tel_nr"][] = function ($attribute, $value, $fail) 
				{
					if (!$value) 
					{
						$fail("Bitte Telefonnummer angeben.");
					} 
					else 
					{
						// Nur 4-Stellig oder im Format +41 58 225 XXXX
						if (!preg_match("/^(\d{4}|\\+41\s58\s225\s\d{4})$/", $value)) 
						{
							$fail("Die Telefonnummer muss im Format +41 58 225 XXXX oder XXXX angegeben werden.");
						}
					}
				};
			}

			// Headset wenn Tischtelefon oder UC Standard
			if ($this->tel_tischtel || $this->tel_ucstd) {
				$rules["tel_headset"][] = "required";
			}
		}

		return $rules;
	}

	public function attributes(): array
	{
		return [
			"anrede_id"              => "Anrede",
			"titel_id"               => "Titel",
			"vorname"                => "Vorname",
			"nachname"               => "Nachname",
			"vertragsbeginn"         => "Vertragsbeginn",
			"arbeitsort_id"          => "Arbeitsort",
			"unternehmenseinheit_id" => "Unternehmenseinheit",
			"abteilung_id"           => "Abteilung",
			"funktion_id"            => "Funktion",
			"abteilung2_id"          => "Zusätzliche Abteilung",
			"bezugsperson_id"        => "Bezugsperson",
			"vorlage_benutzer_id"    => "PC Berechtigungen übernehmen von",
			"mailendung"             => "E-Mail-Domain",
			"email"                  => "E-Mail",
			"kommentar"              => "Kommentar",
			"sap_rolle_id"     => "SAP Rolle",
			"tel_nr"           => "Telefonnummer",
			"tel_auswahl"           => "Telefonie",
			"tel_headset"           => "Headset",
			"raumbeschriftung" => "Raumbeschriftung",
		];
	}

	public function messages(): array
	{
		return [
			"abteilung2_id.required_if" => "Bitte eine zusätzliche Abteilung auswählen, wenn die Option aktiviert ist.",
		];
	}

	public function toArray(): array
	{
		$data = parent::toArray();

		if ($this->tel_status) 
		{
			if ($this->tel_auswahl === "neu") 
			{
				$data["tel_nr"] = null;
			} 
			elseif ($this->tel_nr) 
			{
				if (preg_match("/^\d{4}$/", $this->tel_nr)) 
				{
					$data["tel_nr"] = "+41 58 225 " . $this->tel_nr;
				} 
				else 
				{
					$data["tel_nr"] = $this->tel_nr;
				}
			}
		}
	
		if (!$this->tel_status) {
			$data["tel_auswahl"]    = null;
			$data["tel_nr"]         = null;
			$data["tel_tischtel"]   = false;
			$data["tel_mobiltel"]   = false;
			$data["tel_ucstd"]      = false;
			$data["tel_alarmierung"]= false;
			$data["tel_headset"]    = null;
		}

		return $data;
	}

    public function loadArbeitsorte(bool $all = false): void
    {
        $this->arbeitsorte = Arbeitsort::query()
            ->when(!$all, fn($q) => $q->where("enabled", true))
            ->orderBy("name")
            ->get(["id", "name"])
            ->toArray();
    }

    public function loadAlleArbeitsorte(): void
    {
        $this->arbeitsorte = Arbeitsort::orderBy("name")->get(["id", "name"])->toArray();
    }

    public function loadUnternehmenseinheiten(bool $all = false): void
    {
        if ($all || $this->neue_konstellation) 
		{
            $this->unternehmenseinheiten = Unternehmenseinheit::orderBy("name")->get(["id", "name"])->toArray();
            return;
        }

        if (!$this->arbeitsort_id) 
		{
            $this->unternehmenseinheiten = [];
            return;
        }

        $this->unternehmenseinheiten = Unternehmenseinheit::whereHas("konstellationen", function ($q) {
                $q->where("arbeitsort_id", $this->arbeitsort_id);
            })
            ->where("enabled", true)
            ->orderBy("name")
            ->get(["id", "name"])
            ->toArray();
    }

    public function loadAlleUnternehmenseinheiten(): void
    {
        $this->unternehmenseinheiten = Unternehmenseinheit::orderBy("name")->get(["id", "name"])->toArray();
    }

    public function loadAbteilungen(bool $all = false): void
    {
        if ($all || $this->neue_konstellation) 
		{
            $this->abteilungen = Abteilung::orderBy("name")->get(["id", "name"])->toArray();
            return;
        }

        if (!$this->arbeitsort_id || !$this->unternehmenseinheit_id) 
		{
            $this->abteilungen = [];
            return;
        }

        $this->abteilungen = Abteilung::whereHas("konstellationen", function ($q) {
                $q->where("arbeitsort_id", $this->arbeitsort_id)
                  ->where("unternehmenseinheit_id", $this->unternehmenseinheit_id);
            })
            ->where("enabled", true)
            ->orderBy("name")
            ->get(["id", "name"])
            ->toArray();
    }

    public function loadAlleAbteilungen(): void
    {
        $this->abteilungen = Abteilung::orderBy("name")->get(["id", "name"])->toArray();
    }

    public function loadFunktionen(bool $all = false): void
    {
        if ($all || $this->neue_konstellation) 
		{
            $this->funktionen = Funktion::orderBy("name")->get(["id", "name"])->toArray();
            return;
        }

        if (!$this->arbeitsort_id || !$this->unternehmenseinheit_id || !$this->abteilung_id) 
		{
            $this->funktionen = [];
            return;
        }

        $this->funktionen = Funktion::whereHas("konstellationen", function ($q) {
                $q->where("arbeitsort_id", $this->arbeitsort_id)
                  ->where("unternehmenseinheit_id", $this->unternehmenseinheit_id)
                  ->where("abteilung_id", $this->abteilung_id);
            })
            ->where("enabled", true)
            ->orderBy("name")
            ->get(["id", "name"])
            ->toArray();
    }

    public function loadAlleFunktionen(): void
    {
        $this->funktionen = Funktion::orderBy("name")->get(["id", "name"])->toArray();
    }

    public function loadAnreden(): void
    {
        $this->anreden = Anrede::where("enabled", true)
            ->orderBy("name")
            ->get(["id", "name"])
            ->toArray();
    }

    public function loadTitel(): void
    {
        $this->titel = Titel::where("enabled", true)
            ->orderBy("name")
            ->get(["id", "name"])
            ->toArray();
    }

    public function loadMailendungen(): void
    {
        $this->mailendungen = [
            ["id" => "waldhaus.ch", "name" => "waldhaus.ch"],
            ["id" => "kliniken-gr.ch", "name" => "kliniken-gr.ch"],
            ["id" => "example.org", "name" => "example.org"],
        ];
    }

	public function loadAdusers(?int $abteilungId = null): void
	{
		$query = AdUser::query()
			->with("funktion")
			->orderBy("display_name");

		if ($this->filter_mitarbeiter && $abteilungId) 
		{
			$query->where("abteilung_id", $abteilungId);
		}

		$this->adusers = $query->get()->map(function ($user) {
			return [
				"id" => $user->id,
				"display_name" => $user->funktion
					? $user->display_name . " (" . $user->funktion->name . ")"
					: $user->display_name,
			];
		})->toArray();
	}



	public function loadAdusersKalender(): void
	{
		$this->adusersKalender = AdUser::query()
			->with("funktion")
			->orderBy("display_name")
			->get()
			->map(fn($user) => [
				"id" => $user->id,
				"display_name" => Str::limit(
					$user->funktion
						? $user->display_name . " (" . $user->funktion->name . ")"
						: $user->display_name,
					40 // maximale Länge, danach "..."
				),
			])
			->toArray();
	}

	public function loadSapRollen(): void
	{
		$this->sapRollen = SapRolle::where("enabled", true)
			->orderBy("label")
			->get(["id", "label"])
			->toArray();
	}

	public function updatedFormNeueKonstellation(bool $value): void
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
			$this->form->loadArbeitsorte();

			if ($this->form->arbeitsort_id && !collect($this->form->arbeitsorte)->pluck("id")->contains($this->form->arbeitsort_id)) {
				$this->form->arbeitsort_id = null;
			}

			$this->form->loadUnternehmenseinheiten();
			
			if ($this->form->unternehmenseinheit_id && !collect($this->form->unternehmenseinheiten)->pluck("id")->contains($this->form->unternehmenseinheit_id)) 
			{
				$this->form->unternehmenseinheit_id = null;
			}

			$this->form->loadAbteilungen();
			
			if ($this->form->abteilung_id && !collect($this->form->abteilungen)->pluck("id")->contains($this->form->abteilung_id)) 
			{
				$this->form->abteilung_id = null;
			}

			$this->form->loadFunktionen();
			
			if ($this->form->funktion_id && !collect($this->form->funktionen)->pluck("id")->contains($this->form->funktion_id)) 
			{
				$this->form->funktion_id = null;
			}
		}

		// Kaskade aktualisieren
		$this->dispatch("select2-options", id: "arbeitsort_id", options: $this->form->arbeitsorte, value: $this->form->arbeitsort_id);
		$this->dispatch("select2-options", id: "unternehmenseinheit_id", options: $this->form->unternehmenseinheiten, value: $this->form->unternehmenseinheit_id);
		$this->dispatch("select2-options", id: "abteilung_id", options: $this->form->abteilungen, value: $this->form->abteilung_id);
		$this->dispatch("select2-options", id: "funktion_id", options: $this->form->funktionen, value: $this->form->funktion_id);
		$this->dispatch("select2-options", id: "abteilung2_id", options: $this->form->abteilungen, value: $this->form->abteilung2_id);

		// Mitarbeiter nach Filter-Status aktualisieren
		if ($this->form->filter_mitarbeiter && !$this->form->abteilung_id) 
		{
			$this->form->adusers = [];
		} 
		else 
		{
			$this->form->loadAdusers($this->form->filter_mitarbeiter ? $this->form->abteilung_id : null);
		}

		$this->dispatch("select2-options", id: "bezugsperson_id", options: $this->form->adusers, value: $this->form->bezugsperson_id);
		$this->dispatch("select2-options", id: "vorlage_benutzer_id", options: $this->form->adusers, value: $this->form->vorlage_benutzer_id);
	}


    public function updatedHasAbteilung2($value): void
    {
        if (!$value) 
		{
            $this->abteilung2_id = null;
            $this->dispatch("select2-options", id: "abteilung2_id", options: [], value: null);
        } 
		else 
		{
            $this->abteilung2_id = null;
            $this->dispatch("select2-options", id: "abteilung2_id", options: $this->abteilungen, value: null);
        }
    }

    public function loadAdusersForAbteilung(?int $abteilungId = null): void
    {
        $this->loadAdusers($abteilungId);
    }

	private function setStatus(string $field, bool $active): void
	{
		$current = (int) $this->{$field};

		if ($active) {
			// Wenn noch nicht erledigt (0), auf "in Bearbeitung" (1) setzen
			if ($current === 0) {
				$this->{$field} = 1;
			}
			// wenn bereits 1 oder 2, unverändert lassen
		} else {
			// Deaktiviert -> sicher auf 0
			$this->{$field} = 0;
		}
	}

	public function applyStatus(?Eroeffnung $existing = null): void
	{
		// KIS
		if ($this->kis_status || $this->is_lei) {
			$this->setStatus('status_kis', true);
		} else {
			$this->setStatus('status_kis', false);
			$this->kis_status = false;
			$this->is_lei     = false;
		}

		// SAP
		if ($this->sap_status && $this->sap_rolle_id) {
			$this->setStatus('status_sap', true);
			$this->setStatus('status_auftrag', true);
		} else {
			$this->setStatus('status_sap', false);
			$this->sap_status   = false;
			$this->sap_rolle_id = null;
		}

		// Leistungserbringer
		if ($this->is_lei) {
			$this->setStatus('status_auftrag', true);
		}

		// Telefonie
		if ($this->tel_status && $this->tel_auswahl) {
			$this->setStatus('status_tel', true);
		} else {
			$this->setStatus('status_tel', false);
			$this->tel_status       = false;
			$this->tel_auswahl      = null;
			$this->tel_nr           = null;
			$this->tel_tischtel     = false;
			$this->tel_mobiltel     = false;
			$this->tel_ucstd        = false;
			$this->tel_alarmierung  = false;
			$this->tel_headset      = null;
		}

		// Raumbeschriftung
		if ($this->raumbeschriftung_flag && $this->raumbeschriftung) {
			$this->setStatus('status_auftrag', true);
		} else {
			$this->raumbeschriftung_flag = false;
			$this->raumbeschriftung      = null;
		}

		// Schlüsselrechte Waldhaus
		if ($this->key_waldhaus && ($this->key_wh_badge || $this->key_wh_schluessel)) {
			$this->setStatus('status_auftrag', true);
		} else {
			$this->key_waldhaus     = false;
			$this->key_wh_badge     = false;
			$this->key_wh_schluessel = false;
		}

		// Schlüsselrechte Beverin
		if ($this->key_beverin && ($this->key_be_badge || $this->key_be_schluessel)) {
			$this->setStatus('status_auftrag', true);
		} else {
			$this->key_beverin     = false;
			$this->key_be_badge    = false;
			$this->key_be_schluessel = false;
		}

		// Schlüsselrechte Rothenbrunnen
		if ($this->key_rothenbr && ($this->key_rb_badge || $this->key_rb_schluessel)) {
			$this->setStatus('status_auftrag', true);
		} else {
			$this->key_rothenbr    = false;
			$this->key_rb_badge    = false;
			$this->key_rb_schluessel = false;
		}

		// Berufsbekleidung / Garderobe
		if ($this->berufskleider || $this->garderobe) {
			$this->setStatus('status_auftrag', true);
		} else {
			$this->berufskleider = false;
			$this->garderobe     = false;
		}

		// Falls gar nichts aktiv ist -> Auftrag auf 0
		if (
			!$this->sap_status &&
			!$this->is_lei &&
			!$this->raumbeschriftung_flag &&
			!$this->key_waldhaus &&
			!$this->key_beverin &&
			!$this->key_rothenbr &&
			!$this->berufskleider &&
			!$this->garderobe
		) {
			$this->setStatus('status_auftrag', false);
		}
	}


	public function fillFromModel(Eroeffnung $eroeffnung): void
	{
		$this->fill($eroeffnung->toArray());

		$this->has_abteilung2 = !empty($eroeffnung->abteilung2_id);

		// KIS
		$this->kis_status = (bool)$eroeffnung->status_kis;
		$this->is_lei     = (bool)$eroeffnung->is_lei;

		// SAP
		$this->sap_status   = (bool)$eroeffnung->status_sap;
		$this->sap_rolle_id = $eroeffnung->sap_rolle_id;

		// Telefonie
		$this->tel_status = (bool)$eroeffnung->status_tel;

		// Raumbeschriftung
		$this->raumbeschriftung_flag = !empty($eroeffnung->raumbeschriftung);

		// Schlüsselrechte Waldhaus
		$this->key_waldhaus = (bool)$eroeffnung->key_wh_badge || (bool)$eroeffnung->key_wh_schluessel;

		// Schlüsselrechte Beverin
		$this->key_beverin = (bool)$eroeffnung->key_be_badge || (bool)$eroeffnung->key_be_schluessel;

		// Schlüsselrechte Rothenbrunnen
		$this->key_rothenbr = (bool)$eroeffnung->key_rb_badge || (bool)$eroeffnung->key_rb_schluessel;

		// Berufsbekleidung / Garderobe
		$this->berufskleider = (bool)$eroeffnung->berufskleider;
		$this->garderobe     = (bool)$eroeffnung->garderobe;
		
		$this->kalender_berechtigungen = $eroeffnung->kalender_berechtigungen ?? [];
	}
}
