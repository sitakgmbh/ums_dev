<?php

namespace App\Console\Commands\ActiveDirectory;

use Illuminate\Console\Command;
use App\Services\ActiveDirectory\UserSyncService;

class SyncUsers extends Command
{
    protected $signature = 'ad:sync-users';
    protected $description = 'Synchronisiert alle AD-Benutzer in die lokale Datenbank';

    protected UserSyncService $syncService;

    public function __construct(UserSyncService $syncService)
    {
        parent::__construct();
        $this->syncService = $syncService;
    }

    public function handle(): int
    {
        $this->info('Starte AD-Sync …');
        $this->syncService->sync();
        $this->info('AD-Sync abgeschlossen.');
        return 0;
    }
}
