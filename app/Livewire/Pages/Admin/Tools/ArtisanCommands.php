<?php

namespace App\Livewire\Pages\Admin\Tools;

use Livewire\Component;
use Illuminate\Support\Facades\Artisan;
use Livewire\Attributes\Layout;

#[Layout('layouts.app')]
class ArtisanCommands extends Component
{
    public array $allowed = [
        'ad:sync-users',
		'sap:import',
        'test:do',
		'test:do-error',
    ];

    public bool $running = false;

public function run(string $command): void
{
    if (! in_array($command, $this->allowed, true)) {
        $this->dispatch('open-modal', modal: 'artisan-output-modal', payload: [
            'command' => $command,
            'output'  => "Command `{$command}` ist nicht erlaubt.",
        ]);
        return;
    }

    if ($this->running) {
        $this->dispatch('open-modal', modal: 'artisan-output-modal', payload: [
            'command' => $command,
            'output'  => "Es läuft bereits ein Command. Bitte warten.",
        ]);
        return;
    }

    $this->running = true;

    try {
        $start = microtime(true);
        $startedAt = now();

        Artisan::call($command);
        $output = Artisan::output();

        $end = microtime(true);
        $endedAt = now();
        $duration = round($end - $start, 2) . ' Sekunden';

        $this->dispatch('open-modal', modal: 'artisan-output-modal', payload: [
            'command'  => $command,
            'output'   => $output,
            'started'  => $startedAt->format('d.m.Y H:i:s'),
            'ended'    => $endedAt->format('d.m.Y H:i:s'),
            'duration' => $duration,
        ]);
    } catch (\Throwable $e) {
        $this->dispatch('open-modal', modal: 'alert-modal', payload: [
            'message'  => "Fehler beim Ausführen von {$command}: " . $e->getMessage(),
            'headline' => 'Artisan Fehler',
            'color'    => 'bg-danger',
            'icon'     => 'ri-close-circle-line',
        ]);
    } finally {
        $this->running = false;
    }
}


public function render()
{
    $all = Artisan::all();

    $commands = collect($this->allowed)
        ->mapWithKeys(fn($cmd) => [
            $cmd => [
                'name' => $cmd,
                'description' => $all[$cmd]->getDescription() ?? '',
            ]
        ])->toArray();

    return view('livewire.pages.admin.tools.artisan-commands', [
        'commands' => $commands,
    ])->layoutData([
        'pageTitle' => 'Artisan Befehle',
    ]);
}




}
