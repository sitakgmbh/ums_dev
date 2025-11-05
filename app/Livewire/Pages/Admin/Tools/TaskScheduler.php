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
		"sap:sync",
		"mypdgr:sync",
		"eroeffnungen:assign-license",
        // "graph:test-connection",
        // "test:do",
        // "test:do-error",
    ];

    public array $tasks = [];
    public bool $running = false;

    public function mount(): void
    {
        $this->loadTasks();
    }

private function loadTasks(): void
{
    $consoleFile = base_path("routes/console.php");
    if (file_exists($consoleFile)) 
    {
        require $consoleFile;
    }
    
    $schedule = app(Schedule::class);
    $events = $schedule->events();
    $all = Artisan::all();
    
    $this->tasks = collect($events)
        ->map(function ($event) use ($all) {
            $commandName = $this->extractCommandName($event->command);
            $nextRun = optional($event->nextRunDate(now()))->format("Y-m-d H:i:s");
            
            return [
                "command" => $commandName,
                "description" => isset($all[$commandName]) 
                    ? $all[$commandName]->getDescription() 
                    : "Command nicht gefunden",
                "nextRun" => $nextRun ? \Carbon\Carbon::parse($nextRun)->format("d.m.Y H:i:s") : "Unbekannt",
                "nextRunSort" => $nextRun ?: "9999-12-31 23:59:59", // Für Sortierung
                "expression" => $event->expression,
                "interval" => $this->humanReadableInterval($event->expression),
            ];
        })
        ->sortBy('nextRunSort') // Sortieren nach nächster Ausführung
        ->values()
        ->toArray();
}

private function humanReadableInterval(string $expression): string
{
    $parts = explode(' ', $expression);
    if (count($parts) !== 5) return $expression;
    
    [$minute, $hour, $day, $month, $weekday] = $parts;
    
    // Jede Minute
    if ($expression === '* * * * *') {
        return 'Jede Minute';
    }
    
    // Stündlich
    if ($minute === '0' && $hour === '*' && $day === '*' && $month === '*' && $weekday === '*') {
        return 'Stündlich';
    }
    
    // Alle X Stunden
    if (preg_match('/\*\/(\d+)/', $hour, $matches) && $minute === '0' && $day === '*' && $month === '*' && $weekday === '*') {
        return "Alle {$matches[1]} Stunden";
    }
    
    // Täglich um HH:MM
    if ($day === '*' && $month === '*' && $weekday === '*' && is_numeric($hour) && is_numeric($minute)) {
        $time = sprintf('%02d:%02d', $hour, $minute);
        
        if ($time === '00:00') {
            return 'Täglich um Mitternacht';
        }
        
        return "Täglich um {$time} Uhr";
    }
    
    // Wöchentlich
    if ($day === '*' && $month === '*' && $weekday !== '*' && is_numeric($hour) && is_numeric($minute)) {
        $days = ['Sonntag', 'Montag', 'Dienstag', 'Mittwoch', 'Donnerstag', 'Freitag', 'Samstag'];
        $dayName = $days[$weekday] ?? $weekday;
        $time = sprintf('%02d:%02d', $hour, $minute);
        return "Wöchentlich am {$dayName} um {$time} Uhr";
    }
    
    // Monatlich
    if (is_numeric($day) && $month === '*' && $weekday === '*' && is_numeric($hour) && is_numeric($minute)) {
        $time = sprintf('%02d:%02d', $hour, $minute);
        return "Monatlich am {$day}. Tag um {$time} Uhr";
    }
    
    // Fallback
    return $expression;
}


	private function parseCronExpression(string $expression): string
	{
		$parts = explode(' ', $expression);
		if (count($parts) !== 5) return $expression;
		
		[$minute, $hour, $day, $month, $weekday] = $parts;
		
		// Täglich um HH:MM
		if ($day === '*' && $month === '*' && $weekday === '*' && $hour !== '*' && $minute !== '*') {
			return sprintf('Täglich um %s:%s Uhr', str_pad($hour, 2, '0', STR_PAD_LEFT), str_pad($minute, 2, '0', STR_PAD_LEFT));
		}
		
		return $expression; // Fallback
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
    
    // Commands in der Reihenfolge des Arrays (nicht sortieren)
    $commands = collect($this->allowed)
        ->mapWithKeys(fn($cmd) => [
            $cmd => [
                "name" => $cmd,
                "description" => isset($all[$cmd]) ? $all[$cmd]->getDescription() : "Command nicht gefunden",
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
