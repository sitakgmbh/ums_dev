<?php

namespace App\Livewire\Forms;

use Livewire\Form;
use Illuminate\Support\Str;
use App\Models\Mutation;
use App\Models\AdUser;
use App\Models\Arbeitsort;
use App\Models\Unternehmenseinheit;
use App\Models\Abteilung;
use App\Models\Funktion;
use App\Models\Anrede;
use App\Models\Titel;
use App\Models\SapRolle;
use App\Utils\DateHelper;
use App\Livewire\Traits\MutationDropdownHandlers;

/**
 * Formular Mutation mit Validierung und Logik
 */
class MutationForm extends Form
{
    use MutationDropdownHandlers;

	public bool $isCreate = true;
    public bool $isReadonly = false;

    public bool $enable_arbeitsort = false;
    public bool $enable_unternehmenseinheit = false;
    public bool $enable_abteilung = false;
    public bool $enable_funktion = false;
    public bool $enable_vorlage = false;
    public bool $enable_anrede = false;
    public bool $enable_titel = false;
    public bool $enable_mailendung = false;

	public array $adusersForSelection = [];

    public ?int $ad_user_id = null;
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

	public ?string $vorname = null;
	public ?string $nachname = null;
    public ?int $anrede_id = null;
    public ?int $titel_id = null;
    public ?string $vertragsbeginn = null;

    public array $anreden = [];
    public array $titel = [];
    public array $mailendungen = [];
    public ?string $mailendung = null;

    public ?int $vorlage_benutzer_id = null;
    public array $adusers = [];

    public bool $neue_konstellation = false;
    public bool $filter_mitarbeiter = true;
    public ?string $kommentar = null;

    public bool $kis_status = false;
    public bool $sap_status = false;
	public bool $sap_delete = false;
    public bool $is_lei = false;
    public bool $tel_status = false;
    public bool $raumbeschriftung_flag = false;

    public array $sapRollen = [];
    public ?int $sap_rolle_id = null;

    public ?string $tel_auswahl = null;
    public ?string $tel_nr = null;
    public bool $tel_tischtel = false;
    public bool $tel_mobiltel = false;
    public bool $tel_ucstd = false;
    public bool $tel_alarmierung = false;
    public ?string $tel_headset = null;

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
    public bool $buerowechsel = false;

    public ?string $komm_lei = null;
    public ?string $komm_berufskleider = null;
    public ?string $komm_garderobe = null;
    public ?string $komm_buerowechsel = null;

    public bool $vorab_lizenzierung = false;
    public array $adusersKalender = [];

    public int $status_ad = 0;
	public int $status_mail = 0;
	public int $status_sap = 0;
    public int $status_tel = 0;
    public int $status_kis = 0;
    public int $status_auftrag = 0;

    public function rules(): array
    {
        $rules = [
			"ad_user_id" => ["required", "exists:ad_users,id"],
            "anrede_id" => ["nullable", "exists:anreden,id"],
            "titel_id" => ["nullable", "exists:titel,id"],
            "arbeitsort_id" => ["nullable", "exists:arbeitsorte,id"],
            "unternehmenseinheit_id" => ["nullable", "exists:unternehmenseinheiten,id"],
            "abteilung_id" => ["nullable", "exists:abteilungen,id"],
            "funktion_id" => ["nullable", "exists:funktionen,id"],
            "has_abteilung2" => ["boolean"],
            // "abteilung2_id" => ["required_if:has_abteilung2,true", "nullable", "exists:abteilungen,id"],
			"abteilung2_id" => ["required_if:has_abteilung2,true", "nullable", "exists:abteilungen,id"],
            "vorlage_benutzer_id" => ["nullable", "integer", "exists:ad_users,id"],
            "mailendung" => ["nullable", "string"],
            "neue_konstellation" => ["boolean"],
            "filter_mitarbeiter" => ["boolean"],
            "kommentar" => ["nullable", "string", "max:1000"],
            "sap_status" => ["boolean"],
			"sap_delete" => ["boolean"],
            "is_lei" => ["boolean"],
            "tel_status" => ["boolean"],
            "key_waldhaus" => ["boolean"],
            "key_beverin" => ["boolean"],
            "key_rothenbr" => ["boolean"],
            "tel_auswahl" => ["nullable", "string", "in:uebernehmen,neu,manuell"],
            "tel_nr" => ["nullable", "string", "max:50"],
            "tel_tischtel" => ["boolean"],
            "tel_mobiltel" => ["boolean"],
            "tel_ucstd" => ["boolean"],
            "tel_alarmierung" => ["boolean"],
            "tel_headset" => ["nullable", "string", "in:mono,stereo"],
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

		if ($this->enable_anrede) 
		{
			$rules["anrede_id"] = ["required", "exists:anreden,id"];
		}
		
		if ($this->enable_titel) 
		{
			$rules["titel_id"] = ["required", "exists:titel,id"];
		}
		
		if ($this->enable_arbeitsort) 
		{
			$rules["arbeitsort_id"] = ["required", "exists:arbeitsorte,id"];
		}
		
		if ($this->enable_unternehmenseinheit) 
		{
			$rules["unternehmenseinheit_id"] = ["required", "exists:unternehmenseinheiten,id"];
		}
		
		if ($this->enable_abteilung) 
		{
			$rules["abteilung_id"] = ["required", "exists:abteilungen,id"];
		}
		
		if ($this->enable_funktion) 
		{
			$rules["funktion_id"] = ["required", "exists:funktionen,id"];
		}
		
		if ($this->enable_mailendung) 
		{
			$rules["mailendung"] = ["required", "string"];
		}
		
		if ($this->enable_vorlage) 
		{
			$rules["vorlage_benutzer_id"] = ["required", "integer", "exists:ad_users,id"];
		}

        if ($this->sap_status) 
		{
            $rules["sap_rolle_id"] = ["required", "exists:sap_rollen,id"];
        }

        if ($this->is_lei) 
		{
            $rules["komm_lei"] = ["required", "string", "max:1000"];
        }
		
        if ($this->berufskleider) 
		{
            $rules["komm_berufskleider"] = ["required", "string", "max:1000"];
        }
		
        if ($this->garderobe) 
		{
            $rules["komm_garderobe"] = ["required", "string", "max:1000"];
        }
		
        if ($this->buerowechsel) 
		{
            $rules["komm_buerowechsel"] = ["required", "string", "max:1000"];
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

			if ($this->tel_tischtel || $this->tel_ucstd) 
			{
				$rules["tel_headset"][] = "required";
			}
		}

        return $rules;
    }

	public function attributes(): array
	{
		return [
			"ad_user_id"              => "Benutzer",
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
			"komm_lei" => "Kommentar SAP Leistungserbringer",
			"komm_buerowechsel" => "Kommentar Bürowechsel",
			"komm_berufskleider" => "Kommentar Berufskleider",
			"komm_garderobe" => "Kommentar Garderobe",
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
		else 
		{
            $data["tel_auswahl"] = null;
            $data["tel_nr"] = null;
            $data["tel_tischtel"] = false;
            $data["tel_mobiltel"] = false;
            $data["tel_ucstd"] = false;
            $data["tel_alarmierung"] = false;
            $data["tel_headset"] = null;
        }
		
		$this->clearDisabledFields($data);
        return $data;
    }

	public function loadArbeitsorte(?\App\Models\Mutation $context = null): void
	{
		$mutation = $context instanceof \App\Models\Mutation ? $context : null;

		$this->loadDropdown(
			\App\Models\Arbeitsort::class,
			$mutation?->arbeitsort_id,
			'arbeitsorte'
		);
	}

	public function loadUnternehmenseinheiten(?\App\Models\Mutation $context = null): void
	{
		$mutation = $context instanceof \App\Models\Mutation ? $context : null;

		$this->loadDropdown(
			\App\Models\Unternehmenseinheit::class,
			$mutation?->unternehmenseinheit_id,
			'unternehmenseinheiten',
			scope: fn($q) => $this->arbeitsort_id
				? $q->whereHas('konstellationen', fn($s) =>
					$s->where('arbeitsort_id', $this->arbeitsort_id))
				: $q
		);
	}

	public function loadAbteilungen(?\App\Models\Mutation $context = null): void
	{
		$mutation = $context instanceof \App\Models\Mutation ? $context : null;

		$this->loadDropdown(
			\App\Models\Abteilung::class,
			[$mutation?->abteilung_id, $mutation?->abteilung2_id],
			'abteilungen',
			scope: fn($q) => ($this->arbeitsort_id && $this->unternehmenseinheit_id)
				? $q->whereHas('konstellationen', fn($s) =>
					$s->where('arbeitsort_id', $this->arbeitsort_id)
					  ->where('unternehmenseinheit_id', $this->unternehmenseinheit_id))
				: $q
		);
	}

	public function loadFunktionen(?\App\Models\Mutation $context = null): void
	{
		$mutation = $context instanceof \App\Models\Mutation ? $context : null;

		$this->loadDropdown(
			\App\Models\Funktion::class,
			$mutation?->funktion_id,
			'funktionen',
			scope: function ($q) {
				if ($this->neue_konstellation) {
					return $q;
				}

				if ($this->arbeitsort_id && $this->unternehmenseinheit_id && $this->abteilung_id) {
					return $q->whereHas('konstellationen', function ($s) {
						$s->where('arbeitsort_id', $this->arbeitsort_id)
						  ->where('unternehmenseinheit_id', $this->unternehmenseinheit_id)
						  ->where('abteilung_id', $this->abteilung_id);
					});
				}

				return $q;
			}
		);
	}

	public function loadAnreden(?Mutation $mutation = null): void
	{
		$this->loadDropdown(\App\Models\Anrede::class, $mutation?->anrede_id, 'anreden');
	}

	public function loadTitel(?Mutation $mutation = null): void
	{
		$this->loadDropdown(\App\Models\Titel::class, $mutation?->titel_id, 'titel');
	}

	public function loadMailendungen(): void
	{
		$this->mailendungen = [
			["id" => "pdgr.ch", "name" => "pdgr.ch"],
			["id" => "mentalva.ch", "name" => "mentalva.ch"],
			["id" => "arbes.ch", "name" => "arbes.ch"],
		];
	}

	public function loadAdusers(\App\Models\Mutation|int|null $context = null): void
	{
		if (is_int($context)) {
			$this->adusers = \App\Models\AdUser::query()
				->with('funktion')
				->where('abteilung_id', $context)
				->where('is_existing', true)
				->where('is_enabled', true)
				->orderBy('display_name')
				->get()
				->map(fn($user) => [
					'id' => $user->id,
					'display_name' => \Illuminate\Support\Str::limit(
						$user->funktion
							? "{$user->display_name} ({$user->funktion->name})"
							: $user->display_name,
						40
					),
				])
				->toArray();
			return;
		}

		$extraIds = collect([
			$context instanceof \App\Models\Mutation ? $context->vorlage_benutzer_id : $this->vorlage_benutzer_id,
		])->filter()->unique()->values()->toArray();

		$this->loadAdUserDropdown($extraIds, 'adusers');
	}


	public function loadAdUser(\App\Models\Mutation|int|null $context = null): void
	{
		if (is_int($context)) 
		{
			$this->adusersForSelection = \App\Models\AdUser::query()
				->with('funktion')
				->where('abteilung_id', $context)
				->where('is_existing', true)
				->where('is_enabled', true)
				->orderBy('display_name')
				->get()
				->map(fn($user) => [
					'id' => $user->id,
					'display_name' => \Illuminate\Support\Str::limit(
						$user->funktion
							? "{$user->display_name} ({$user->funktion->name})"
							: $user->display_name,
						40
					),
				])
				->toArray();

			return;
		}

		$extraIds = $context instanceof \App\Models\Mutation ? [$context->ad_user_id] : [$this->ad_user_id];
		$extraIds = collect($extraIds)->filter()->unique()->values()->toArray();
		$this->loadAdUserDropdown($extraIds, 'adusersForSelection');
	}

	public function loadSapRollen(?Mutation $mutation = null): void
	{
		$this->loadDropdown(\App\Models\SapRolle::class, $mutation?->sap_rolle_id, 'sapRollen', 'label');
	}

	private function setStatus(string $field, bool $active): void
	{
		$current = (int) $this->{$field};

		if ($active) 
		{
			if ($current === 0) 
			{
				$this->{$field} = 1;
			}
		} 
		else 
		{
			$this->{$field} = 0;
		}
	}

	public function applyStatus(?Mutation $existing = null): void
	{
		// Vorlagen-Benutzer
		if ($this->vorlage_benutzer_id) 
		{
			$this->setStatus('status_ad', true);
		} 
		else 
		{
			$this->setStatus('status_ad', false);
		}

		// Mail
		if (!empty($this->vorname) || !empty($this->nachname) || !empty($this->mailendung)) 
		{
			$this->setStatus('status_mail', true);
		} 
		else 
		{
			$this->setStatus('status_mail', false);
		}

		// SAP
		if ($this->sap_status && $this->sap_rolle_id) 
		{
			$this->setStatus('status_sap', true);
			$this->setStatus('status_auftrag', true);
		} 
		else 
		{
			$this->setStatus('status_sap', false);
			$this->sap_status   = false;
			$this->sap_rolle_id = null;
		}

		// SAP-Benutzer löschen
		if ($this->sap_delete) 
		{
			// $this->setStatus('status_sap', true);
			$this->setStatus('status_auftrag', true);
		}

		// Leistungserbringer
		if ($this->is_lei && $this->komm_lei) 
		{
			$this->setStatus('status_auftrag', true);
		} 
		else 
		{
			$this->is_lei = false;
			$this->komm_lei = null;
		}

		// Telefonie
		if ($this->tel_status && $this->tel_auswahl) 
		{
			$this->setStatus('status_tel', true);
		} 
		else 
		{
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
		if ($this->raumbeschriftung_flag && $this->raumbeschriftung) 
		{
			$this->setStatus('status_auftrag', true);
		} 
		else 
		{
			$this->raumbeschriftung_flag = false;
			$this->raumbeschriftung      = null;
		}

		// Schlüsselrechte Waldhaus
		if ($this->key_waldhaus && ($this->key_wh_badge || $this->key_wh_schluessel)) 
		{
			$this->setStatus('status_auftrag', true);
		} 
		else 
		{
			$this->key_waldhaus      = false;
			$this->key_wh_badge      = false;
			$this->key_wh_schluessel = false;
		}

		// Schlüsselrechte Beverin
		if ($this->key_beverin && ($this->key_be_badge || $this->key_be_schluessel)) 
		{
			$this->setStatus('status_auftrag', true);
		} 
		else 
		{
			$this->key_beverin      = false;
			$this->key_be_badge     = false;
			$this->key_be_schluessel = false;
		}

		// Schlüsselrechte Rothenbrunnen
		if ($this->key_rothenbr && ($this->key_rb_badge || $this->key_rb_schluessel)) 
		{
			$this->setStatus('status_auftrag', true);
		} 
		else 
		{
			$this->key_rothenbr      = false;
			$this->key_rb_badge      = false;
			$this->key_rb_schluessel = false;
		}

		// Berufsbekleidung
		if ($this->berufskleider && $this->komm_berufskleider) 
		{
			$this->setStatus('status_auftrag', true);
		} 
		else 
		{
			$this->berufskleider = false;
			$this->komm_berufskleider = null;
		}

		// Garderobe
		if ($this->garderobe && $this->komm_garderobe) 
		{
			$this->setStatus('status_auftrag', true);
		} 
		else 
		{
			$this->garderobe = false;
			$this->komm_garderobe = null;
		}

		// Bürowechsel
		if ($this->buerowechsel && $this->komm_buerowechsel) 
		{
			$this->setStatus('status_auftrag', true);
		} 
		else 
		{
			$this->buerowechsel = false;
			$this->komm_buerowechsel = null;
		}

		// Auftrag nur wenn überhaupt etwas aktiv
		if (
			!$this->sap_status &&
			!$this->sap_delete &&
			!$this->is_lei &&
			!$this->raumbeschriftung_flag &&
			!$this->key_waldhaus &&
			!$this->key_beverin &&
			!$this->key_rothenbr &&
			!$this->berufskleider &&
			!$this->garderobe &&
			!$this->buerowechsel
		) {
			$this->setStatus('status_auftrag', false);
		}
	}

	public function fillFromModel(\App\Models\Mutation $mutation): void
	{
		$this->fill($mutation->toArray());
		$this->has_abteilung2 = !empty($mutation->abteilung2_id);
		$this->vertragsbeginn = $mutation->vertragsbeginn ? $mutation->vertragsbeginn->format('Y-m-d') : null;

		$hadArbeitsort = !empty($mutation->arbeitsort_id);
		$hadUE = !empty($mutation->unternehmenseinheit_id);
		$hadAbteilung = !empty($mutation->abteilung_id);
		$hadFunktion = !empty($mutation->funktion_id);
		$hadAnrede = !empty($mutation->anrede_id);
		$hadTitel = !empty($mutation->titel_id);
		$hadMailendung = !empty($mutation->mailendung);
		$hadVorlage = !empty($mutation->vorlage_benutzer_id);

		$user = $mutation->adUser()->with([
			'arbeitsort', 'unternehmenseinheit', 'abteilung', 'funktion', 'anrede', 'titel'
		])->first();

		if ($user) 
		{
			if (!$this->arbeitsort_id) 
			{
				$this->arbeitsort_id = $user->arbeitsort_id;
			}
			
			if (!$this->unternehmenseinheit_id) 
			{
				$this->unternehmenseinheit_id = $user->unternehmenseinheit_id;
			}
			
			if (!$this->abteilung_id) 
			{
				$this->abteilung_id = $user->abteilung_id;
			}
			
			if (!$this->funktion_id) 
			{
				$this->funktion_id = $user->funktion_id;
			}
			
			if (!$this->anrede_id) 
			{
				$this->anrede_id = $user->anrede_id;
			}
			
			if (!$this->titel_id) 
			{
				$this->titel_id = $user->titel_id;
			}
		}

		$this->enable_arbeitsort = $hadArbeitsort;
		$this->enable_unternehmenseinheit = $hadUE;
		$this->enable_abteilung = $hadAbteilung;
		$this->enable_funktion = $hadFunktion;
		$this->enable_anrede = $hadAnrede;
		$this->enable_titel = $hadTitel;
		$this->enable_mailendung = $hadMailendung;
		$this->enable_vorlage = $hadVorlage;

		$this->sap_status = (bool)$mutation->status_sap;
		$this->sap_rolle_id = $mutation->sap_rolle_id;
		$this->tel_status = (bool)$mutation->status_tel;
		$this->raumbeschriftung_flag = !empty($mutation->raumbeschriftung);
		$this->key_waldhaus = (bool)$mutation->key_wh_badge || (bool)$mutation->key_wh_schluessel;
		$this->key_beverin = (bool)$mutation->key_be_badge || (bool)$mutation->key_be_schluessel;
		$this->key_rothenbr = (bool)$mutation->key_rb_badge || (bool)$mutation->key_rb_schluessel;
		$this->berufskleider = (bool)$mutation->berufskleider;
		$this->garderobe = (bool)$mutation->garderobe;
		$this->buerowechsel = (bool)$mutation->buerowechsel;

		$this->komm_lei = $mutation->komm_lei;
		$this->komm_berufskleider = $mutation->komm_berufskleider;
		$this->komm_garderobe = $mutation->komm_garderobe;
		$this->komm_buerowechsel = $mutation->komm_buerowechsel;
	}

	private function clearDisabledFields(array &$data): void
	{
		if (!$this->enable_arbeitsort) 
		{
			$data["arbeitsort_id"] = null;
		}

		if (!$this->enable_unternehmenseinheit) 
		{
			$data["unternehmenseinheit_id"] = null;
		}

		if (!$this->enable_abteilung) 
		{
			$data["abteilung_id"] = null;

			// abteilung2_id nur löschen, wenn keine zusätzliche Abteilung aktiv ist
			if (!$this->has_abteilung2) 
			{
				$data["abteilung2_id"] = null;
			}
		}

		if (!$this->enable_funktion) 
		{
			$data["funktion_id"] = null;
		}

		if (!$this->enable_anrede) 
		{
			$data["anrede_id"] = null;
		}

		if (!$this->enable_titel) 
		{
			$data["titel_id"] = null;
		}

		if (!$this->enable_mailendung) 
		{
			$data["mailendung"] = null;
		}

		if (!$this->enable_vorlage) 
		{
			$data["vorlage_benutzer_id"] = null;
		}
	}
}
