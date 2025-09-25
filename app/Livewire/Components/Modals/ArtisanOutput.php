<?php

namespace App\Livewire\Components\Modals;

use App\Livewire\Components\Modals\BaseModal;

class ArtisanOutput extends BaseModal
{
    public string $command = '';
    public string $output = '';

    // hier ergänzt
    public ?string $started = null;
    public ?string $ended   = null;
    public ?string $duration = null;

    protected function openWith(array $payload): bool
    {
        $this->command  = $payload['command']  ?? '';
        $this->output   = $payload['output']   ?? '';
        $this->started  = $payload['started']  ?? null;
        $this->ended    = $payload['ended']    ?? null;
        $this->duration = $payload['duration'] ?? null;

        $this->title      = "Artisan Output";
        $this->size       = 'lg';
        $this->headerBg   = 'bg-primary';
        $this->headerText = 'text-white';
        $this->backdrop   = false;
        $this->position   = 'centered';
        $this->scrollable = true;

        return true;
    }
}
