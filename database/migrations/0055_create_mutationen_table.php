<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create("mutationen", function (Blueprint $table) {
            $table->id();

            // --- Basis-Infos ---
            $table->foreignId("owner_id")->nullable()->constrained("ad_users")->nullOnDelete();
            $table->date("vertragsbeginn");

            // --- Antragsteller/Bezug ---
            $table->foreignId("antragsteller_id")->nullable()->constrained("ad_users")->nullOnDelete();

            // --- Account-Daten ---
            $table->foreignId("ad_user_id")->nullable()->constrained("ad_users")->nullOnDelete();
            $table->string("mailendung")->nullable();
			$table->foreignId('vorlage_benutzer_id')->nullable()->constrained('ad_users')->nullOnDelete();
            $table->json("ad_gruppen")->nullable();

            // --- SAP-Zuordnungen ---
            $table->boolean("neue_konstellation")->default(false);
            $table->boolean("filter_mitarbeiter")->default(false);
			$table->string("vorname")->nullable();
			$table->string("nachname")->nullable();
            $table->foreignId("anrede_id")->nullable()->constrained("anreden")->nullOnDelete();
            $table->foreignId("titel_id")->nullable()->constrained("titel")->nullOnDelete();
            $table->foreignId("arbeitsort_id")->nullable()->constrained("arbeitsorte")->nullOnDelete();
            $table->foreignId("unternehmenseinheit_id")->nullable()->constrained("unternehmenseinheiten")->nullOnDelete();
            $table->foreignId("abteilung_id")->nullable()->constrained("abteilungen")->nullOnDelete();
            $table->foreignId("abteilung2_id")->nullable()->constrained("abteilungen")->nullOnDelete();
            $table->foreignId("funktion_id")->nullable()->constrained("funktionen")->nullOnDelete();

            // --- Telefonie ---
            $table->string("tel_nr")->nullable();
            $table->string("tel_auswahl")->nullable();
            $table->boolean("tel_tischtel")->default(false);
            $table->boolean("tel_mobiltel")->default(false);
            $table->boolean("tel_ucstd")->default(false);
            $table->boolean("tel_alarmierung")->default(false);
            $table->string("tel_headset")->nullable();

            // --- Keys & Ausstattung ---
            $table->boolean("is_lei")->default(false);
            $table->boolean("key_waldhaus")->default(false);
            $table->boolean("key_beverin")->default(false);
            $table->boolean("key_rothenbr")->default(false);
            $table->boolean("key_wh_badge")->default(false);
            $table->boolean("key_wh_schluessel")->default(false);
            $table->boolean("key_be_badge")->default(false);
            $table->boolean("key_be_schluessel")->default(false);
            $table->boolean("key_rb_badge")->default(false);
            $table->boolean("key_rb_schluessel")->default(false);
            $table->boolean("berufskleider")->default(false);
            $table->boolean("garderobe")->default(false);
			$table->boolean("buerowechsel")->default(false);

            // --- SAP ---
            $table->foreignId("sap_rolle_id")->nullable()->constrained("sap_rollen")->nullOnDelete();
            $table->boolean("sap_delete")->default(false);

            // --- Kommentarfelder ---
            $table->text("komm_lei")->nullable();
            $table->text("komm_berufskleider")->nullable();
            $table->text("komm_garderobe")->nullable();
            $table->text("komm_key")->nullable();
            $table->text("komm_buerowechsel")->nullable();

            // --- Status-Felder ---
            $table->tinyInteger("status_ad")->default(0);
			$table->tinyInteger("status_mail")->default(0);
            $table->tinyInteger("status_tel")->default(0);
            $table->tinyInteger("status_kis")->default(0);
			$table->tinyInteger("status_pep")->default(0);
            $table->tinyInteger("status_sap")->default(0);
            $table->tinyInteger("status_auftrag")->default(0);
            $table->tinyInteger("status_info")->default(1);

            // --- Meta ---
            $table->boolean("vorab_lizenzierung")->default(false);
            $table->json("kalender_berechtigungen")->nullable();
            $table->text("kommentar")->nullable();
            $table->string("ticket_nr")->nullable();
            $table->boolean("archiviert")->default(false);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists("mutationen");
    }
};
