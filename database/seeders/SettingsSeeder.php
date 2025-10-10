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
