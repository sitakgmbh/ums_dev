<?php

namespace App\Livewire\Pages\Admin\Tools;

use Livewire\Component;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Console\Scheduling\Schedule;
use Livewire\Attributes\Layout;

#[Layout("layouts.app")]
class TaskScheduler extends Component
{
    public array $allowed = [
        "ad:sync-users",
        "sap:import",
        "graph:test-connection",
        "test:do",
        "test:do-error",
    ];

    public array $tasks = [];
    public bool $running = false;

    public function mount(): void
    {
        $this->loadTasks();
    }

    private function loadTasks(): void
    {
        // routes/console.php manuell einbinden
        $consoleFile = base_path("routes/console.php");

        if (file_exists($consoleFile)) 
		{
            require $consoleFile;
        }

        $schedule = app(Schedule::class);
        $events = $schedule->events();

        $this->tasks = collect($events)->map(function ($event) {
            return [
                "command"  => $this->extractCommandName($event->command),
                "nextRun"  => optional($event->nextRunDate(now()))?->format("d.m.Y H:i:s") ?? "Unbekannt",
            ];
        })->toArray();
    }

    private function extractCommandName(string $command): string
    {
        // Beispiel: "C:\xampp\php\php.EXE" "artisan" ad:sync-users
        if (preg_match('/artisan"?\s+([^\s]+)/', $command, $matches)) 
		{
            return $matches[1];
        }
        return $command;
    }

    public function run(string $command): void
    {
        if (! in_array($command, $this->allowed, true)) 
		{
            $this->dispatch("open-modal", modal: "components.modals.artisan-output", payload: [
                "command" => $command,
                "output"  => "Command `{$command}` ist nicht erlaubt.",
            ]);
            return;
        }

        if ($this->running) 
		{
            $this->dispatch("open-modal", modal: "components.modals.artisan-output", payload: [
                "command" => $command,
                "output"  => "Es läuft bereits ein Command. Bitte warten.",
            ]);
            return;
        }

        $this->running = true;

        try 
		{
            $start = microtime(true);
            $startedAt = now();

            Artisan::call($command);
            $output = Artisan::output();

            $duration = round(microtime(true) - $start, 2) . " Sekunden";

            $this->dispatch("open-modal", modal: "components.modals.artisan-output", payload: [
                "command"  => $command,
                "output"   => $output,
                "started"  => $startedAt->format("d.m.Y H:i:s"),
                "duration" => $duration,
            ]);
        } 
		catch (\Throwable $e) 
		{
            $this->dispatch("open-modal", modal: "components.modals.artisan-output", payload: [
                "command" => $command,
                "output"  => "Fehler beim Ausführen von {$command}: " . $e->getMessage(),
            ]);
        } 
		finally 
		{
            $this->running = false;
        }
    }

    public function render()
    {
        $all = Artisan::all();

        $commands = collect($this->allowed)
            ->mapWithKeys(fn($cmd) => [
                $cmd => [
                    "name" => $cmd,
                    "description" => $all[$cmd]->getDescription() ?? "",
                ]
            ])->toArray();

        return view("livewire.pages.admin.tools.task-scheduler", [
            "commands" => $commands,
            "tasks"    => $this->tasks,
        ])->layoutData([
            "pageTitle" => "Aufgabenplanung",
        ]);
    }
}
