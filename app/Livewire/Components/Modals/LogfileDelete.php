<?php

namespace App\Livewire\Components\Modals;

use App\Livewire\Components\Modals\BaseModal;
use Illuminate\Support\Facades\File;
use App\Utils\Logging\Logger;

class LogfileDelete extends BaseModal
{
    public ?string $filename = null;

    protected function openWith(array $payload): bool
    {
        $this->filename = $payload["filename"] ?? null;

        if (! $this->filename || ! File::exists(storage_path("logs/{$this->filename}"))) 
		{
            $this->dispatch("open-modal", modal: "alert-modal", payload: [
                "message"  => "Das Logfile konnte nicht gefunden werden.",
                "headline" => "Fehler",
                "color"    => "bg-danger",
                "icon"     => "ri-close-circle-line",
            ]);
			
            return false;
        }

        $this->title      = "Logfile löschen";
        $this->size       = "md";
        $this->backdrop   = true;
        $this->position   = "centered";
        $this->scrollable = true;
        $this->headerBg   = "bg-danger";
        $this->headerText = "text-white";

        return true;
    }

	public function delete(): void
	{
		if (! $this->filename) 
		{
			return;
		}

		$path = storage_path("logs/{$this->filename}");

		if (! File::exists($path)) 
		{
			$this->closeModal();
			$this->dispatch("open-modal", modal: "alert-modal", payload: [
				"message"  => "Das Logfile {$this->filename} konnte nicht gefunden werden.",
				"headline" => "Fehler",
				"color"    => "bg-danger",
				"icon"     => "ri-close-circle-line",
			]);
			
			return;
		}

		File::delete($path);

		$user     = auth()->user();
		$username = $user?->username ?? $user?->name ?? "unbekannt";
		$fullname = $user?->fullname ?? trim(($user?->firstname . " " . $user?->lastname)) ?? $username;

		Logger::db("system", "info", "Logfile {$this->filename} gelöscht durch {$username}", [
			"username" => $username,
			"fullname" => $fullname,
			"file"     => $this->filename,
		]);

		$this->dispatch("logfile-deleted", filename: $this->filename);
		$this->closeModal();
	}


}
