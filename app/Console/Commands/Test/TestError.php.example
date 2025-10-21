<?php

namespace App\Console\Commands\Test;

use Illuminate\Console\Command;
use App\Utils\Logging\Logger;

class TestError extends Command
{
    protected $signature = 'test:do-error';
    protected $description = 'Das ist ein Test-Befehl, der absichtlich einen Fehler wirft.';

    public function handle(): int
    {
        // Konsolenausgabe
        $this->info('Starte Test-Befehl (mit Fehler)...');

        // Logging über deine Logger-Utility
        Logger::debug('Test-Befehl gestartet', [
            'user' => auth()->user()->username ?? 'cli',
            'time' => now()->toDateTimeString(),
        ]);

        // künstliche Verzögerung
        sleep(2);

        // absichtlicher Fehler
        throw new \RuntimeException('Dies ist ein absichtlich ausgelöster Fehler im Test-Befehl.');

        // (wird nicht mehr erreicht)
        return Command::SUCCESS;
    }
}
