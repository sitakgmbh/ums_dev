<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *     schema="Eroeffnung",
 *     type="object",
 *     title="Eröffnung",
 *     description="Antrag",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="owner_id", type="integer", nullable=true, example=12),
 *     @OA\Property(property="vorname", type="string", example="Max"),
 *     @OA\Property(property="nachname", type="string", example="Mustermann"),
 *     @OA\Property(property="vertragsbeginn", type="string", format="date", example="2025-10-01"),
 *     @OA\Property(property="wiedereintritt", type="boolean", example=false),
 *     @OA\Property(property="antragsteller_id", type="integer", nullable=true, example=5),
 *     @OA\Property(property="bezugsperson_id", type="integer", nullable=true, example=7),
 *     @OA\Property(property="vorlage_benutzer_id", type="integer", nullable=true, example=9),
 *     @OA\Property(property="neue_konstellation", type="boolean", example=true),
 *     @OA\Property(property="filter_mitarbeiter", type="boolean", example=false),
 *     @OA\Property(property="anrede_id", type="integer", nullable=true, example=1),
 *     @OA\Property(property="titel_id", type="integer", nullable=true, example=2),
 *     @OA\Property(property="arbeitsort_id", type="integer", nullable=true, example=3),
 *     @OA\Property(property="unternehmenseinheit_id", type="integer", nullable=true, example=4),
 *     @OA\Property(property="abteilung_id", type="integer", nullable=true, example=5),
 *     @OA\Property(property="abteilung2_id", type="integer", nullable=true, example=6),
 *     @OA\Property(property="funktion_id", type="integer", nullable=true, example=7),
 *     @OA\Property(property="sap_rolle_id", type="integer", nullable=true, example=2),
 *     @OA\Property(property="benutzername", type="string", example="mamust"),
 *     @OA\Property(property="email", type="string", format="email", example="max.mustermann@example.com"),
 *     @OA\Property(property="mailendung", type="string", example="@pdgr.ch"),
 *     @OA\Property(property="ad_gruppen", type="array", @OA\Items(type="string"), example={"gruppe1","gruppe2"}),
 *     @OA\Property(property="passwort", type="string", example="geheim123"),
 *     @OA\Property(property="tel_nr", type="string", nullable=true, example="+41 81 123 45 67"),
 *     @OA\Property(property="tel_auswahl", type="string", nullable=true, example="neu"),
 *     @OA\Property(property="tel_tischtel", type="boolean", example=true),
 *     @OA\Property(property="tel_mobiltel", type="boolean", example=false),
 *     @OA\Property(property="tel_ucstd", type="boolean", example=false),
 *     @OA\Property(property="tel_alarmierung", type="boolean", example=false),
 *     @OA\Property(property="tel_headset", type="string", nullable=true, example="mono"),
 *     @OA\Property(property="is_lei", type="boolean", example=false),
 *     @OA\Property(property="key_waldhaus", type="boolean", example=false),
 *     @OA\Property(property="key_beverin", type="boolean", example=false),
 *     @OA\Property(property="key_rothenbr", type="boolean", example=false),
 *     @OA\Property(property="key_wh_badge", type="boolean", example=false),
 *     @OA\Property(property="key_wh_schluessel", type="boolean", example=false),
 *     @OA\Property(property="key_be_badge", type="boolean", example=false),
 *     @OA\Property(property="key_be_schluessel", type="boolean", example=false),
 *     @OA\Property(property="key_rb_badge", type="boolean", example=false),
 *     @OA\Property(property="key_rb_schluessel", type="boolean", example=false),
 *     @OA\Property(property="berufskleider", type="boolean", example=true),
 *     @OA\Property(property="garderobe", type="boolean", example=false),
 *     @OA\Property(property="raumbeschriftung", type="boolean", example=false),
 *     @OA\Property(property="status_ad", type="integer", example=1),
 *     @OA\Property(property="status_tel", type="integer", example=0),
 *     @OA\Property(property="status_pep", type="integer", example=0),
 *     @OA\Property(property="status_kis", type="integer", example=0),
 *     @OA\Property(property="status_sap", type="integer", example=0),
 *     @OA\Property(property="status_auftrag", type="integer", example=0),
 *     @OA\Property(property="status_info", type="integer", example=0),
 *     @OA\Property(property="kommentar", type="string", nullable=true, example="Neuer Mitarbeiter ab Oktober"),
 *     @OA\Property(property="ticket_nr", type="string", nullable=true, example="OTOBOT-12345"),
 *     @OA\Property(property="vorab_lizenzierung", type="boolean", example=false),
 *     @OA\Property(property="kalender_berechtigungen", type="array", @OA\Items(type="string"), example={"kalender1","kalender2"}),
 *     @OA\Property(property="archiviert", type="boolean", example=false),
 *     @OA\Property(property="gesamtstatus", type="integer", readOnly=true, example=1),
 *     @OA\Property(property="is_editable", type="boolean", readOnly=true, example=true),
 *     @OA\Property(property="created_at", type="string", format="date-time", example="2025-09-22T12:34:56Z"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", example="2025-09-22T12:34:56Z")
 * )
 */
class Eroeffnung extends Model
{
    protected $table = "eroeffnungen";

    protected $fillable = [
        "owner_id",
        "vorname",
        "nachname",
        "vertragsbeginn",
        "wiedereintritt",
        "antragsteller_id",
        "bezugsperson_id",
        "vorlage_benutzer_id",
        "neue_konstellation",
        "filter_mitarbeiter",
        "anrede_id",
        "titel_id",
        "arbeitsort_id",
        "unternehmenseinheit_id",
        "abteilung_id",
        "abteilung2_id",
        "funktion_id",
        "sap_rolle_id",
        "benutzername",
        "email",
        "mailendung",
        "ad_gruppen",
        "passwort",
        "tel_nr",
        "tel_auswahl",
        "tel_tischtel",
        "tel_mobiltel",
        "tel_ucstd",
        "tel_alarmierung",
        "tel_headset",
        "is_lei",
        "key_waldhaus",
        "key_beverin",
        "key_rothenbr",
        "key_wh_badge",
        "key_wh_schluessel",
        "key_be_badge",
        "key_be_schluessel",
        "key_rb_badge",
        "key_rb_schluessel",
        "berufskleider",
        "garderobe",
        "raumbeschriftung",
        "status_ad",
        "status_tel",
        "status_pep",
        "status_kis",
        "status_sap",
        "status_auftrag",
        "status_info",
        "kommentar",
        "ticket_nr",
		"vorab_lizenzierung",
        "kalender_berechtigungen",
        "archiviert",
    ];

    protected $casts = [
		"vertragsbeginn" => "date:Y-m-d",
        "wiedereintritt" => "boolean",
        "neue_konstellation" => "boolean",
        "filter_mitarbeiter" => "boolean",
        "ad_gruppen" => "array",
        "tel_tischtel" => "boolean",
        "tel_mobiltel" => "boolean",
        "tel_ucstd" => "boolean",
        "tel_alarmierung" => "boolean",
        "is_lei" => "boolean",
        "key_waldhaus" => "boolean",
        "key_beverin" => "boolean",
        "key_rothenbr" => "boolean",
        "key_wh_badge" => "boolean",
        "key_wh_schluessel" => "boolean",
        "key_be_badge" => "boolean",
        "key_be_schluessel" => "boolean",
        "key_rb_badge" => "boolean",
        "key_rb_schluessel" => "boolean",
        "berufskleider" => "boolean",
        "garderobe" => "boolean",
		"vorab_lizenzierung" => "boolean",
		"kalender_berechtigungen" => "array",
        "archiviert" => "boolean",
    ];

	protected $attributes = [
		"kalender_berechtigungen" => "[]",
	];

    public function sapRolle()
    {
        return $this->belongsTo(SapRolle::class, "sap_rolle_id");
    }

    public function antragsteller()
    {
        return $this->belongsTo(AdUser::class, "antragsteller_id");
    }

    public function bezugsperson()
    {
        return $this->belongsTo(AdUser::class, "bezugsperson_id");
    }

    public function vorlageBenutzer()
    {
        return $this->belongsTo(AdUser::class, "vorlage_benutzer_id");
    }

    public function arbeitsort()
    {
        return $this->belongsTo(Arbeitsort::class, "arbeitsort_id");
    }

    public function unternehmenseinheit()
    {
        return $this->belongsTo(Unternehmenseinheit::class, "unternehmenseinheit_id");
    }

    public function abteilung()
    {
        return $this->belongsTo(Abteilung::class, "abteilung_id");
    }

    public function abteilung2()
    {
        return $this->belongsTo(Abteilung::class, "abteilung2_id");
    }

    public function funktion()
    {
        return $this->belongsTo(Funktion::class, "funktion_id");
    }

    public function anrede()
    {
        return $this->belongsTo(Anrede::class, "anrede_id");
    }

    public function titel()
    {
        return $this->belongsTo(Titel::class, "titel_id");
    }

	public function getStatusAttribute(): int
	{
		if ($this->status_info === 2) return 3; // 3 = Abgeschlossen
		$max = max($this->status_ad, $this->status_tel, $this->status_pep, $this->status_kis, $this->status_sap, $this->status_auftrag);
		return $max > 1 ? 2 : 1; // grösser als 1 = Bearbeitung sonst Neu
	}

	public function owner()
	{
		return $this->belongsTo(\App\Models\AdUser::class, 'owner_id');
	}

	public function adUser()
	{
		return $this->belongsTo(\App\Models\AdUser::class, 'ad_user_id');
	}
}
