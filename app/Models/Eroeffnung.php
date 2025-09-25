<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Eroeffnung extends Model
{
    protected $table = 'eroeffnungen';

    protected $fillable = [
        'owner',
        'vorname',
        'nachname',
        'vertragsbeginn',
        'wiedereintritt',
        'antragsteller_id',
        'bezugsperson_id',
        'vorlage_benutzer_id',
        'neue_konstellation',
        'filter_mitarbeiter',
        'anrede_id',
        'titel_id',
        'arbeitsort_id',
        'unternehmenseinheit_id',
        'abteilung_id',
        'abteilung2_id',
        'funktion_id',
        'sap_rolle_id',
        'benutzername',
        'email',
        'mailendung',
        'ad_gruppen',
        'passwort',
        'tel_nr',
        'tel_auswahl',
        'tel_tischtel',
        'tel_mobiltel',
        'tel_ucstd',
        'tel_alarmierung',
        'tel_headset',
        'is_lei',
        'key_waldhaus',
        'key_beverin',
        'key_rothenbr',
        'key_wh_badge',
        'key_wh_schluessel',
        'key_be_badge',
        'key_be_schluessel',
        'key_rb_badge',
        'key_rb_schluessel',
        'berufskleider',
        'garderobe',
        'raumbeschriftung',
        'status_ad',
        'status_tel',
        'status_pep',
        'status_kis',
        'status_sap',
        'status_auftrag',
        'status_info',
        'kommentar',
        'ticket_nr',
        'archiviert',
    ];

    protected $casts = [
        'vertragsbeginn' => 'date',
        'wiedereintritt' => 'boolean',
        'neue_konstellation' => 'boolean',
        'filter_mitarbeiter' => 'boolean',
        'ad_gruppen' => 'array',
        'tel_tischtel' => 'boolean',
        'tel_mobiltel' => 'boolean',
        'tel_ucstd' => 'boolean',
        'tel_alarmierung' => 'boolean',
        'is_lei' => 'boolean',
        'key_waldhaus' => 'boolean',
        'key_beverin' => 'boolean',
        'key_rothenbr' => 'boolean',
        'key_wh_badge' => 'boolean',
        'key_wh_schluessel' => 'boolean',
        'key_be_badge' => 'boolean',
        'key_be_schluessel' => 'boolean',
        'key_rb_badge' => 'boolean',
        'key_rb_schluessel' => 'boolean',
        'berufskleider' => 'boolean',
        'garderobe' => 'boolean',
        'archiviert' => 'boolean',
    ];

    // --- Relations ---
    public function sapRolle()
    {
        return $this->belongsTo(SapRolle::class, 'sap_rolle_id');
    }

    public function antragsteller()
    {
        return $this->belongsTo(AdUser::class, 'antragsteller_id');
    }

    public function bezugsperson()
    {
        return $this->belongsTo(AdUser::class, 'bezugsperson_id');
    }

    public function vorlageBenutzer()
    {
        return $this->belongsTo(AdUser::class, 'vorlage_benutzer_id');
    }

    public function arbeitsort()
    {
        return $this->belongsTo(Arbeitsort::class, 'arbeitsort_id');
    }

    public function unternehmenseinheit()
    {
        return $this->belongsTo(Unternehmenseinheit::class, 'unternehmenseinheit_id');
    }

    public function abteilung()
    {
        return $this->belongsTo(Abteilung::class, 'abteilung_id');
    }

    public function abteilung2()
    {
        return $this->belongsTo(Abteilung::class, 'abteilung2_id');
    }

    public function funktion()
    {
        return $this->belongsTo(Funktion::class, 'funktion_id');
    }

    public function anrede()
    {
        return $this->belongsTo(Anrede::class, 'anrede_id');
    }

    public function titel()
    {
        return $this->belongsTo(Titel::class, 'titel_id');
    }
}
