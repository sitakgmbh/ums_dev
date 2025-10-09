<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
