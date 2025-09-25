<?php

namespace App\Livewire\Pages\Admin;

use Illuminate\Support\Facades\File;
use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Utils\Logging\Logger;
use Livewire\Attributes\On;

#[Layout('layouts.app')]
class Logfiles extends Component
{
    public array $files = [];
	public ?string $status = null;

    public function mount(): void
    {
        $this->loadFiles();
    }

    #[On('logfile-deleted')]
    public function refreshFiles(string $filename): void
    {
        $this->loadFiles();
        $this->status = "Datei {$filename} wurde erfolgreich gelöscht.";
    }

private function loadFiles(): void
{
    $logPath = storage_path('logs');

    if (!File::exists($logPath)) {
        $this->files = [];
        return;
    }

    $logFiles = File::files($logPath);

    $this->files = collect($logFiles)->map(function ($file) {
        try {
            return [
                'name'    => $file->getFilename(),
                'size'    => $this->formatSize($file->getSize()), // <-- hier nutzen
                'updated' => date('Y-m-d H:i:s', $file->getMTime()),
                'content' => File::get($file->getRealPath()),
            ];
        } catch (\Exception $e) {
            return null;
        }
    })->filter()->values()->toArray();
}

	public function download(string $filename)
	{
		$path = storage_path("logs/" . basename($filename));

		if (! File::exists($path)) {
			$this->dispatch('open-modal', modal: 'alert-modal', payload: [
				'message'  => "Das Logfile {$filename} konnte nicht gefunden werden.",
				'headline' => 'Fehler',
				'color'    => 'bg-danger',
				'icon'     => 'ri-close-circle-line',
			]);
			return;
		}

		$user     = auth()->user();
		$username = $user?->username ?? $user?->name ?? 'unbekannt'; // kurzer Login
		$fullname = $user?->fullname ?? trim(($user?->firstname . ' ' . $user?->lastname)) ?? $username;

		Logger::db('system', 'info', "Logfile {$filename} heruntergeladen durch {$username}", [
			'username' => $username,
			'fullname' => $fullname,
			'file'     => $filename,
		]);

		return response()->download($path);
	}


public function render()
{
    $logPath = storage_path('logs');
    $logFiles = File::files($logPath);

    $files = collect($logFiles)->map(fn($file) => [
        'name'    => $file->getFilename(),
        'size'    => $this->formatSize($file->getSize()), // <-- hier auch nutzen
        'updated' => date('Y-m-d H:i:s', $file->getMTime()),
        'content' => File::get($file->getRealPath()),
    ]);

    return view('livewire.pages.admin.logfiles', compact('files'))
        ->layoutData([
            'pageTitle' => 'Logfiles',
        ]);
}

private function formatSize(int $bytes): string
{
    if ($bytes < 1024) {
        return $bytes . ' B';
    }

    $units = ['KB', 'MB', 'GB', 'TB'];
    $factor = floor((strlen((string) $bytes) - 1) / 3);
    $size = $bytes / pow(1024, $factor);

    return sprintf('%.2f %s', $size, $units[$factor - 1]);
}


}
