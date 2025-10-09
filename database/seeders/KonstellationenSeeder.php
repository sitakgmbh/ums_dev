<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Arbeitsort;
use App\Models\Unternehmenseinheit;
use App\Models\Abteilung;
use App\Models\Funktion;
use App\Models\Konstellation;

class StammdatenSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('🔧 Starte Stammdaten-Seeding ...');

        DB::transaction(function () {
            $this->seedArbeitsorte();
            $this->seedUnternehmenseinheiten();
            $this->seedAbteilungen();
            $this->seedFunktionen();
            $this->seedKonstellationen();
        });

        $this->command->info('✅ Stammdaten erfolgreich angelegt.');
    }

    private function seedArbeitsorte(): void
    {
        $daten = [
            ['name' => 'Klinik Waldhaus'],
            ['name' => 'Klinik Beverin'],
            ['name' => 'Klinik Rothenbrunnen'],
            ['name' => 'Klinik Chur West'],
            ['name' => 'Klinik Davos'],
        ];

        Arbeitsort::truncate();
        Arbeitsort::insert(array_map(fn($d) => [
            ...$d,
            'enabled' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ], $daten));

        $this->command->info('🏥 Arbeitsorte: ' . count($daten));
    }

    private function seedUnternehmenseinheiten(): void
    {
        $daten = [
            ['name' => 'Verwaltung'],
            ['name' => 'Pflege'],
            ['name' => 'Therapie'],
            ['name' => 'ICT'],
            ['name' => 'Infrastruktur'],
            ['name' => 'Küche & Gastronomie'],
        ];

        Unternehmenseinheit::truncate();
        Unternehmenseinheit::insert(array_map(fn($d) => [
            ...$d,
            'enabled' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ], $daten));

        $this->command->info('🏢 Unternehmenseinheiten: ' . count($daten));
    }

    private function seedAbteilungen(): void
    {
        $daten = [
            ['name' => 'Administration'],
            ['name' => 'Rechnungswesen'],
            ['name' => 'Pflegezentrum A'],
            ['name' => 'Pflegezentrum B'],
            ['name' => 'Therapiezentrum'],
            ['name' => 'ICT Support'],
            ['name' => 'ICT Infrastruktur'],
            ['name' => 'Hausdienst'],
            ['name' => 'Technik'],
            ['name' => 'Küche'],
        ];

        Abteilung::truncate();
        Abteilung::insert(array_map(fn($d) => [
            ...$d,
            'enabled' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ], $daten));

        $this->command->info('📂 Abteilungen: ' . count($daten));
    }

    private function seedFunktionen(): void
    {
        $daten = [
            ['name' => 'Pflegefachperson HF'],
            ['name' => 'Stationsleitung'],
            ['name' => 'IT Supporter'],
            ['name' => 'IT Systemadministrator'],
            ['name' => 'Therapeut'],
            ['name' => 'Verwaltungsassistent'],
            ['name' => 'Koch EFZ'],
            ['name' => 'Techniker'],
            ['name' => 'Raumpflegerin'],
            ['name' => 'Leitung Pflege'],
            ['name' => 'Psychologe'],
        ];

        Funktion::truncate();
        Funktion::insert(array_map(fn($d) => [
            ...$d,
            'enabled' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ], $daten));

        $this->command->info('🧑‍💼 Funktionen: ' . count($daten));
    }

    private function seedKonstellationen(): void
    {
        $arbeitsorte = Arbeitsort::all();
        $einheiten   = Unternehmenseinheit::all();
        $abteilungen = Abteilung::all();
        $funktionen  = Funktion::all();

        if (
            $arbeitsorte->isEmpty() ||
            $einheiten->isEmpty() ||
            $abteilungen->isEmpty() ||
            $funktionen->isEmpty()
        ) {
            $this->command->warn('⚠️ Basisdaten fehlen – keine Konstellationen erstellt.');
            return;
        }

        Konstellation::truncate();

        $kombinationen = [];

        foreach ($arbeitsorte as $ort) {
            foreach ($einheiten->random(min(3, $einheiten->count())) as $einheit) {
                foreach ($abteilungen->random(min(4, $abteilungen->count())) as $abt) {
                    foreach ($funktionen->random(min(2, $funktionen->count())) as $fun) {
                        $kombinationen[] = [
                            'arbeitsort_id' => $ort->id,
                            'unternehmenseinheit_id' => $einheit->id,
                            'abteilung_id' => $abt->id,
                            'funktion_id' => $fun->id,
                            'enabled' => rand(1, 10) > 1, // ca. 90 % aktiviert
                            'created_at' => now(),
                            'updated_at' => now(),
                        ];
                    }
                }
            }
        }

        $kombinationen = collect($kombinationen)->shuffle()->take(150)->values();

        DB::table('konstellationen')->insert($kombinationen->toArray());

        $this->command->info('🧩 Konstellationen: ' . $kombinationen->count());
    }
}
