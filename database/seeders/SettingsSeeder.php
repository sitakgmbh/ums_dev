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
                'name'        => 'Debug Modus',
                'description' => 'Aktiviert Debug-Ausgaben und detaillierte Fehlermeldungen. Nur in Entwicklungsumgebungen verwenden.',
            ],
        ];

		foreach ($settings as $data) {
			$setting = Setting::firstOrNew(['key' => $data['key']]);
			$setting->type = $data['type'];
			$setting->name = $data['name'];
			$setting->description = $data['description'];
			$setting->value = $data['value'];
			$setting->save();
		}

    }
}
