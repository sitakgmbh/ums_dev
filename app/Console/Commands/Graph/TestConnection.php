<?php

namespace App\Console\Commands\Graph;

use Illuminate\Console\Command;
use Throwable;
use App\Services\Graph\UserService;

class TestConnection extends Command
{
    protected $signature = "graph:test-connection";
    protected $description = "Testet die Verbindung zu Microsoft Graph.";

    public function __construct(protected UserService $graphUsers)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $this->info("Starte Verbindungstest zu Microsoft Graph...");

        try 
		{
            $users = $this->graphUsers->listUsers();

            if (!empty($users)) 
			{
                $count = count($users);
                $this->info("Verbindung hergestellt – {$count} Benutzer gefunden");

                return self::SUCCESS;
            }

            $this->warn("Verbindung hergestellt – keine Benutzer gefunden");
            return self::SUCCESS;

        } 
		catch (Throwable $e) 
		{
            $this->error("Fehler bei der Verbindung: " . $e->getMessage());
            return self::FAILURE;
        }
    }
}
