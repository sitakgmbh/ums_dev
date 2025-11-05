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
                'description' => 'Aktiviert erweiterte Protokollierung. Im Debug-Modus werden zusätzliche Details in den Logdateien gespeichert, um die Analyse und Fehlersuche zu erleichtern. Sollte nur in Entwicklungs- oder Testumgebungen verwendet werden.',
            ],
            [
                'key'         => 'personalnummer_abgleich_excludes',
                'value'       => '',
                'type'        => 'string',
                'name'        => 'Excludes Personalnummerabgleich',
                'description' => 'Hinterlegte Personalnummern erzeugen kein Admin-Ticket. Beispiel: 13579,24680 (mit Komma trennen)',
            ],
			// Azure
			[
				'key'         => 'azure_tenant_id',
				'value'       => '',
				'type'        => 'string',
				'name'        => 'Tenant ID',
				'description' => 'Microsoft Azure Tenant ID',
			],
			[
				'key'         => 'azure_client_id',
				'value'       => '',
				'type'        => 'string',
				'name'        => 'Client ID',
				'description' => 'Microsoft Azure Application Client ID',
			],
			[
				'key'         => 'azure_client_secret',
				'value'       => '',
				'type'        => 'password',
				'name'        => 'Client Secret',
				'description' => 'Microsoft Azure Application Client Secret',
			],
			// Otobo
			[
				'key'         => 'otobo_url',
				'value'       => '',
				'type'        => 'string',
				'name'        => 'Webservice URL',
				'description' => 'Otobo Webservice URL',
			],
			[
				'key'         => 'otobo_username',
				'value'       => '',
				'type'        => 'string',
				'name'        => 'Benutzername',
				'description' => 'Benutzername Otobo Webservice',
			],
			[
				'key'         => 'otobo_password',
				'value'       => '',
				'type'        => 'password',
				'name'        => 'Passwort',
				'description' => 'Passwort Otobo Webservice',
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
