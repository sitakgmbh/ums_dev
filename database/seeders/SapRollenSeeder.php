<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SapRollenSeeder extends Seeder
{
    public function run(): void
    {
        DB::table("sap_rollen")->insert([
            ["id" => 2, "name" => "Z_PSY_VERWALTUNGSASSISTENT", "enabled" => true, "label" => "Verwaltungsassistent"],
            ["id" => 3, "name" => "Z_PSY_FINANZEN", "enabled" => true, "label" => "Finanzen"],
            ["id" => 4, "name" => "Z_PSY_KUNDENADMINISTRATION", "enabled" => true, "label" => "Kundenadministration"],
            ["id" => 5, "name" => "Z_PSY_CONTROLLING", "enabled" => true, "label" => "Controlling"],
            ["id" => 6, "name" => "Z_PSY_IT", "enabled" => true, "label" => "ICT"],
            ["id" => 7, "name" => "Z_PSY_LEITUNG_INFRASTRUKTUR", "enabled" => true, "label" => "Leitung Infrastruktur"],
            ["id" => 8, "name" => "Z_PSY_INFRASTRUKTUR", "enabled" => true, "label" => "Infrastruktur"],
            ["id" => 9, "name" => "Z_PSY_HOTELLERIE", "enabled" => true, "label" => "Hotellerie"],
            ["id" => 10, "name" => "Z_PSY_BESCHAFFUNG", "enabled" => true, "label" => "Apotheke"],
            ["id" => 11, "name" => "Z_PSY_PERSONALDIENST", "enabled" => true, "label" => "Personaldienst (Business Partner und Services)"],
            ["id" => 13, "name" => "Z_PSY_SEKRETARIAT_HZ", "enabled" => true, "label" => "Sekretariat Heimzentren"],
            ["id" => 14, "name" => "Z_PSY_ARBES", "enabled" => true, "label" => "Arbes"],
            ["id" => 15, "name" => "Z_PSY_GRUPPE_HZ", "enabled" => true, "label" => "Gruppe Heimzentren"],
            ["id" => 16, "name" => "Z_PSY_MEDIZINISCH_SEKRETARIAT", "enabled" => true, "label" => "Sekretariat"],
            ["id" => 18, "name" => "Z_PSY_STATIONEN", "enabled" => true, "label" => "Stationen"],
            ["id" => 19, "name" => "Z_PSY_HEGEBE", "enabled" => true, "label" => "HeGeBe"],
            ["id" => 20, "name" => "Z_PSY_TAGESKLINIKEN_THERAPIEN", "enabled" => true, "label" => "Tageskliniken Therapien"],
            ["id" => 22, "name" => "Z_PSY_PERS_ENTWICKL_BERATUNG", "enabled" => true, "label" => "HR Entwicklung"],
            ["id" => 23, "name" => "Z_PSY_AKI_SEKRETARIAT", "enabled" => true, "label" => "AKI"],
            ["id" => 24, "name" => "Z_PSY_MED_CONTROLLING", "enabled" => true, "label" => "Kodierung / med. Controlling"],
        ]);
    }
}
