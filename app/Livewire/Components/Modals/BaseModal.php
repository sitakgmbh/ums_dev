<?php

namespace App\Livewire\Components\Modals;

use Livewire\Component;
use Livewire\Attributes\On;

abstract class BaseModal extends Component
{
    public string $title = "";
    public string $size = "md";
    public bool $backdrop = false;

    public string $position = "centered";
    public bool $scrollable = true;
    public string $headerBg = "";
    public string $headerText = "";

    // Modal-ID automatisch aus Klassennamen.
	protected function getModalId(): string
	{
		return static::getName(); 
		// ergibt "components.modals.eroeffnungen.wiedereintritt"
	}

    #[On("open-modal")]
    public function handleOpen(string $modal, array $payload = []): void
    {
        if ($modal !== $this->getModalId()) 
		{
            return;
        }

		// openWith() gibt true zurück, wenn das Modal geöffnet werden soll
		if ($this->openWith($payload) !== false) 
		{
			$this->openModal();
		}
    }

	// Kann false zurückgeben, wenn kein Modal geöffnet werden soll
	protected function openWith(array $payload): bool
	{
		return true;
	}

    public function openModal(): void
    {
        $this->dispatch(
            "show-bs-modal",
            id: $this->getModalId(),
            backdrop: $this->backdrop ? "static" : true,
            keyboard: $this->backdrop ? false : true
        );
    }

    public function closeModal(): void
    {
        $this->dispatch("hide-bs-modal", id: $this->getModalId());
    }
}
