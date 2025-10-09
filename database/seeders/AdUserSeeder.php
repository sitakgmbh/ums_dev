<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AdUser;
use App\Models\Konstellation;
use Illuminate\Support\Str;

class AdUserSeeder extends Seeder
{
    public function run(): void
    {
        // Prüfen, ob es gültige Konstellationen gibt
        $konstellationen = Konstellation::with(['arbeitsort', 'unternehmenseinheit', 'abteilung', 'funktion'])
            ->where('enabled', true)
            ->get();

        if ($konstellationen->isEmpty()) {
            $this->command->warn('Keine gültigen Konstellationen gefunden – Seeder übersprungen.');
            return;
        }

        // Dummy-Daten
        $vornamen = ['Anna', 'Peter', 'Lukas', 'Sarah', 'Thomas', 'Julia', 'Markus', 'Nina', 'David', 'Laura'];
        $nachnamen = ['Muster', 'Schmidt', 'Huber', 'Keller', 'Graf', 'Fischer', 'Weber', 'Koch', 'Meier', 'Zimmermann'];
        $domains = ['sitak.ch'];

        foreach (range(1, 10) as $i) {
            $vorname = $vornamen[$i - 1];
            $nachname = $nachnamen[$i - 1];
            $username = Str::lower($vorname . '.' . $nachname);
            $email = "{$username}@{$domains[array_rand($domains)]}";

            // Zufällige Konstellation ziehen
            $k = $konstellationen->random();

            // Ein paar Nutzer bekommen Alias-Adressen
            $aliases = ["SMTP:{$email}"];
            if ($i % 3 === 0) {
                $aliases[] = "smtp:{$vorname}.alias{$i}@example.org";
            }

            AdUser::create([
                'sid' => Str::uuid(),
                'guid' => Str::uuid(),
                'username' => $username,
                'firstname' => $vorname,
                'lastname' => $nachname,
                'display_name' => "{$vorname} {$nachname}",
                'email' => $email,
                'is_enabled' => true,
                'is_existing' => true,
                'password_never_expires' => false,
                'proxy_addresses' => $aliases,
                'member_of' => [],
                'funktion_id' => $k->funktion_id,
                'abteilung_id' => $k->abteilung_id,
                'arbeitsort_id' => $k->arbeitsort_id,
                'unternehmenseinheit_id' => $k->unternehmenseinheit_id,
                'anrede_id' => rand(1, 2), // 1=Herr, 2=Frau o.ä.
            ]);
        }

        $this->command->info('AD-Benutzer erfolgreich angelegt.');
    }
}
