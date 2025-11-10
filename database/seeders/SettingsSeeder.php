<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Setting;

class SettingsSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            [
                'key'         => 'debug_mode',
                'value'       => false,
                'type'        => 'bool',
                'name'        => 'Debug-Modus',
                'description' => 'Aktiviert erweiterte Protokollierung. Diese Option generiert viele Logs und sollte nicht permanent aktivert sein.',
            ],
            [
                'key'         => 'personalnummer_abgleich_excludes',
                'value'       => '',
                'type'        => 'string',
                'name'        => 'Excludes Personalnummerabgleich',
                'description' => 'Hinterlegte Personalnummern erzeugen keinen Incident. Trenne mehrere Einträge mit einem Komma.',
            ],
        ];

		foreach ($settings as $data) 
		{
			Setting::updateOrCreate(
				['key' => $data['key']],
				[
					'type' => $data['type'],
					'name' => $data['name'],
					'description' => $data['description'],
					'value' => Setting::where('key', $data['key'])->exists() ? Setting::where('key', $data['key'])->first()->value : $data['value'], // nur erstellen wenn noch nicht vorhanden
				]
			);
		}
    }
}
