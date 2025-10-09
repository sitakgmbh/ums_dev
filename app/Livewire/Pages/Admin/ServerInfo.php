<?php

namespace App\Livewire\Pages\Admin;

use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('layouts.app')]
class ServerInfo extends Component
{
    public array $infos = [];

public function mount()
{
    $this->infos = [
        [
            'name' => 'System',
            'updated' => now()->format('d.m.Y H:i'),
            'data' => [
                'Betriebssystem'   => php_uname(),
                'Server Software'  => $_SERVER['SERVER_SOFTWARE'] ?? 'unbekannt',
                'PHP Version'      => PHP_VERSION,
                'Laravel Version'  => app()->version(),
                'Environment'      => app()->environment(),
                'Timezone'         => config('app.timezone'),
            ],
        ],
        [
            'name' => 'Limits',
            'updated' => now()->format('d.m.Y H:i'),
            'data' => [
                'Memory Limit'        => ini_get('memory_limit'),
                'Max Execution Time'  => ini_get('max_execution_time') . ' Sekunden',
                'Upload Max Filesize' => ini_get('upload_max_filesize'),
                'Post Max Size'       => ini_get('post_max_size'),
            ],
        ],
        [
            'name' => 'Extensions',
            'updated' => now()->format('d.m.Y H:i'),
            'data' => [
                'Loaded Extensions' => implode(', ', get_loaded_extensions()),
            ],
        ],
    ];
}

    public function render()
    {
        return view('livewire.pages.admin.server-info')
			->layout("layouts.app", [
				"pageTitle" => "Server Informationen",
			]);        
    }
}
