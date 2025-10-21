<?php

namespace App\Console\Commands\Test;

use Illuminate\Console\Command;
use App\Utils\Logging\Logger;

class Test extends Command
{
    protected $signature = 'test:do';
    protected $description = 'Das ist ein Test-Befehl.';

    public function handle(): int
    {
        // Konsolenausgabe
        $this->info('Test-Befehl wurde ausgeführt.');

        // Logging über deine Logger-Utility
        Logger::debug('Das ist eine Debug-Meldung aus test:do', [
            'user' => auth()->user()->username ?? 'cli',
            'time' => now()->toDateTimeString(),
        ]);

		sleep(5);

        // Erfolgscode zurückgeben
        return Command::SUCCESS;
    }
}
