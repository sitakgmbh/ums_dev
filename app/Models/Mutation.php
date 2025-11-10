<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *     schema="Mutation",
 *     type="object",
 *     title="Mutation",
 *     description="Mutation",
 *     
 *     @OA\Property(property="id", type="integer", example=647),
 *     @OA\Property(property="owner_id", type="integer", nullable=true, example=467),
 *     @OA\Property(property="vertragsbeginn", type="string", format="date", example="yyyy-mm-dd"),
 *     @OA\Property(property="antragsteller_id", type="integer", nullable=true, example=5),
 *     @OA\Property(property="vorlage_benutzer_id", type="integer", nullable=true, example=12),
 *     @OA\Property(property="neue_konstellation", type="boolean", example=false),
 *     @OA\Property(property="filter_mitarbeiter", type="boolean", example=true),
 *     @OA\Property(property="vorname", type="string", example="Max"),
 *     @OA\Property(property="nachname", type="string", example="Mustermann"),
 *     @OA\Property(property="anrede_id", type="integer", nullable=true, example=1),
 *     @OA\Property(property="titel_id", type="integer", nullable=true, example=2),
 *     @OA\Property(property="arbeitsort_id", type="integer", nullable=true, example=3),
 *     @OA\Property(property="unternehmenseinheit_id", type="integer", nullable=true, example=4),
 *     @OA\Property(property="abteilung_id", type="integer", nullable=true, example=5),
 *     @OA\Property(property="abteilung2_id", type="integer", nullable=true, example=6),
 *     @OA\Property(property="funktion_id", type="integer", nullable=true, example=7),
 *     @OA\Property(property="vorname_old", type="string", example="Max"),
 *     @OA\Property(property="nachname_old", type="string", example="Mustermann"),
 *     @OA\Property(property="anrede_id_old", type="integer", nullable=true, example=1),
 *     @OA\Property(property="titel_id_old", type="integer", nullable=true, example=2),
 *     @OA\Property(property="arbeitsort_id_old", type="integer", nullable=true, example=3),
 *     @OA\Property(property="unternehmenseinheit_id_old", type="integer", nullable=true, example=4),
 *     @OA\Property(property="abteilung_id_old", type="integer", nullable=true, example=5),
 *     @OA\Property(property="funktion_id_old", type="integer", nullable=true, example=7),
 *     @OA\Property(property="ad_user_id", type="integer", example=53),
 *     @OA\Property(property="mailendung", type="string", example="@domain.tld"),
 *     @OA\Property(property="ad_gruppen", type="array", @OA\Items(type="string"), example={"GRP_IT", "GRP_SUPPORT"}),
 *     @OA\Property(property="tel_nr", type="string", nullable=true, example="081 123 45 67"),
 *     @OA\Property(property="tel_auswahl", type="string", nullable=true, example="Tischtelefon"),
 *     @OA\Property(property="tel_tischtel", type="boolean", example=true),
 *     @OA\Property(property="tel_mobiltel", type="boolean", example=false),
 *     @OA\Property(property="tel_ucstd", type="boolean", example=false),
 *     @OA\Property(property="tel_alarmierung", type="boolean", example=true),
 *     @OA\Property(property="tel_headset", type="boolean", example=false),
 *     @OA\Property(property="is_lei", type="boolean", example=false, description="Leistungserbringer"),
 *     @OA\Property(property="key_waldhaus", type="boolean", example=false),
 *     @OA\Property(property="key_beverin", type="boolean", example=false),
 *     @OA\Property(property="key_rothenbr", type="boolean", example=false),
 *     @OA\Property(property="key_wh_badge", type="boolean", example=false),
 *     @OA\Property(property="key_wh_schluessel", type="boolean", example=false),
 *     @OA\Property(property="key_be_badge", type="boolean", example=false),
 *     @OA\Property(property="key_be_schluessel", type="boolean", example=false),
 *     @OA\Property(property="key_rb_badge", type="boolean", example=false),
 *     @OA\Property(property="key_rb_schluessel", type="boolean", example=false),
 *     @OA\Property(property="berufskleider", type="boolean", example=false),
 *     @OA\Property(property="garderobe", type="boolean", example=false),
 *     @OA\Property(property="buerowechsel", type="boolean", example=false),
 *     @OA\Property(property="sap_rolle_id", type="integer", nullable=true, example=8),
 *     @OA\Property(property="sap_delete", type="boolean", example=false),
 *     @OA\Property(property="komm_lei", type="string", nullable=true, example="Leistungsänderung"),
 *     @OA\Property(property="komm_berufskleider", type="string", nullable=true, example="Benötigt neue Berufskleider"),
 *     @OA\Property(property="komm_garderobe", type="string", nullable=true, example="Garderobenplatz wechseln"),
 *     @OA\Property(property="komm_key", type="string", nullable=true, example="Neuer Schlüssel für Gebäude X"),
 *     @OA\Property(property="komm_buerowechsel", type="string", nullable=true, example="Umzug in Raum 123"),
 *     @OA\Property(property="status_ad", type="integer", example=2),
 *     @OA\Property(property="status_mail", type="integer", example=1),
 *     @OA\Property(property="status_tel", type="integer", example=0),
 *     @OA\Property(property="status_kis", type="integer", example=0),
 *     @OA\Property(property="status_pep", type="integer", example=0),
 *     @OA\Property(property="status_sap", type="integer", example=0),
 *     @OA\Property(property="status_auftrag", type="integer", example=0),
 *     @OA\Property(property="status_info", type="integer", example=0),
 *     @OA\Property(property="vorab_lizenzierung", type="boolean", example=false),
 *     @OA\Property(property="kalender_berechtigungen", type="array", @OA\Items(type="string"), example={"chef@example.ch","team@example.ch"}),
 *     @OA\Property(property="kommentar", type="string", nullable=true, example="Benötigt ab 01.10. neuen Arbeitsplatz."),
 *     @OA\Property(property="ticket_nr", type="string", nullable=true, example="TCK-2025-00123"),
 *     @OA\Property(property="archiviert", type="boolean", example=false),
 *     
 *     @OA\Property(property="created_at", type="string", format="date-time", example="2025-10-21T10:00:00Z"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", example="2025-10-21T10:10:00Z")
 * )
 */

class Mutation extends Model
{
    use HasFactory;

    protected $table = "mutationen";

    protected $fillable = [
        "owner_id",
        "vertragsbeginn",
        "antragsteller_id",
        "vorlage_benutzer_id",
        "neue_konstellation",
        "filter_mitarbeiter",
        "vorname",
		"nachname",
		"anrede_id",
        "titel_id",
        "arbeitsort_id",
        "unternehmenseinheit_id",
        "abteilung_id",
        "abteilung2_id",
        "funktion_id",
        "vorname_old",
		"nachname_old",
		"anrede_id_old",
		"titel_id_old",
		"arbeitsort_id_old",
		"unternehmenseinheit_id_old",
		"abteilung_id_old",
		"funktion_id_old",
		"funktion_id",
        "ad_user_id",
        "mailendung",
        "ad_gruppen",
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
        "buerowechsel",
        "sap_rolle_id",
        "sap_delete",
        "komm_lei",
        "komm_berufskleider",
        "komm_garderobe",
        "komm_key",
        "komm_buerowechsel",
        "status_ad",
		"status_mail",
        "status_tel",
        "status_kis",
        "status_pep",
        "status_sap",
        "status_auftrag",
        "status_info",
        "vorab_lizenzierung",
        "kalender_berechtigungen",
        "kommentar",
        "ticket_nr",
        "archiviert",
    ];

    protected $casts = [
        "vertragsbeginn"         => "date:Y-m-d",
        "neue_konstellation"     => "boolean",
        "filter_mitarbeiter"     => "boolean",
        "ad_gruppen"             => "array",
        "tel_tischtel"           => "boolean",
        "tel_mobiltel"           => "boolean",
        "tel_ucstd"              => "boolean",
        "tel_alarmierung"        => "boolean",
        "is_lei"                 => "boolean",
        "key_waldhaus"           => "boolean",
        "key_beverin"            => "boolean",
        "key_rothenbr"           => "boolean",
        "key_wh_badge"           => "boolean",
        "key_wh_schluessel"      => "boolean",
        "key_be_badge"           => "boolean",
        "key_be_schluessel"      => "boolean",
        "key_rb_badge"           => "boolean",
        "key_rb_schluessel"      => "boolean",
        "berufskleider"          => "boolean",
        "garderobe"              => "boolean",
		"buerowechsel"           => "boolean",
        "sap_delete"             => "boolean",
        "vorab_lizenzierung"     => "boolean",
        "kalender_berechtigungen"=> "array",
        "archiviert"             => "boolean",
    ];

	protected bool $suppressObserver = false;

	public function suppressObserver(bool $value = true): static
	{
		$this->suppressObserver = $value;
		return $this;
	}

	public function shouldSuppressObserver(): bool
	{
		return $this->suppressObserver;
	}

    /*
     |--------------------------------------------------------------------------
     | Beziehungen
     |--------------------------------------------------------------------------
     */

    public function owner()
    {
        return $this->belongsTo(AdUser::class, "owner_id");
    }

    public function antragsteller()
    {
        return $this->belongsTo(AdUser::class, "antragsteller_id");
    }

    public function bezugsperson()
    {
        return $this->belongsTo(AdUser::class, "bezugsperson_id");
    }

	public function arbeitsort()
	{
		return $this->belongsTo(Arbeitsort::class, "arbeitsort_id");
	}

	public function arbeitsortOld()
	{
		return $this->belongsTo(Arbeitsort::class, "arbeitsort_id_old");
	}

	public function unternehmenseinheit()
	{
		return $this->belongsTo(Unternehmenseinheit::class, "unternehmenseinheit_id");
	}

	public function unternehmenseinheitOld()
	{
		return $this->belongsTo(Unternehmenseinheit::class, "unternehmenseinheit_id_old");
	}

	public function abteilung()
	{
		return $this->belongsTo(Abteilung::class, "abteilung_id");
	}

	public function abteilungOld()
	{
		return $this->belongsTo(Abteilung::class, "abteilung_id_old");
	}

	public function abteilung2()
	{
		return $this->belongsTo(Abteilung::class, "abteilung2_id");
	}

	public function abteilung2Old()
	{
		return $this->belongsTo(Abteilung::class, "abteilung2_id_old");
	}

	public function funktion()
	{
		return $this->belongsTo(Funktion::class, "funktion_id");
	}

	public function funktionOld()
	{
		return $this->belongsTo(Funktion::class, "funktion_id_old");
	}

	public function anrede()
	{
		return $this->belongsTo(Anrede::class, "anrede_id");
	}

	public function anredeOld()
	{
		return $this->belongsTo(Anrede::class, "anrede_id_old");
	}

	public function titel()
	{
		return $this->belongsTo(Titel::class, "titel_id");
	}

	public function titelOld()
	{
		return $this->belongsTo(Titel::class, "titel_id_old");
	}

    public function sapRolle()
    {
        return $this->belongsTo(SapRolle::class, "sap_rolle_id");
    }

    public function adUser()
    {
        return $this->belongsTo(AdUser::class, "ad_user_id");
    }
	
    public function vorlageBenutzer()
    {
        return $this->belongsTo(AdUser::class, "vorlage_benutzer_id");
    }

	public function getStatusAttribute(): int
	{
		if ($this->status_info === 2) return 3; // 3 = Abgeschlossen
		$max = max($this->status_ad, $this->status_tel, $this->status_pep, $this->status_kis, $this->status_sap, $this->status_auftrag);
		return $max > 1 ? 2 : 1; // grösser als 1 = Bearbeitung sonst Neu
	}

}
