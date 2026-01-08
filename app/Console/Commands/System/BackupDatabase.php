<?php

namespace App\Console\Commands\System;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;
use App\Utils\Logging\Logger;

class BackupDatabase extends Command
{
    protected $signature = 'db:backup';

    protected $description = 'Sichert die Datenbank.';

    public function handle()
    {
        $database = config('database.connections.mysql.database');
        $username = config('database.connections.mysql.username');
        $password = config('database.connections.mysql.password');
        $host = config('database.connections.mysql.host');
        $port = config('database.connections.mysql.port', 3306);

        $date = date('Ymd_His');
        $file = "backup/database/{$database}_{$date}.sql";

        $cleanupDays = 14;
        $basePath = 'backup/database';
        $now = time();
        $deletedFiles = [];

        $dumpCommand = [
            'mysqldump',
            "-h{$host}",
            "-P{$port}",
            "-u{$username}",
            "--password={$password}",
            $database,
        ];

        $this->info("Erstelle Backup: {$file}");

        $process = Process::fromShellCommandline(implode(' ', $dumpCommand));
        $startedAt = microtime(true);

        try 
		{
            $process->run();

            if (!$process->isSuccessful()) 
			{
                throw new ProcessFailedException($process);
            }

            Storage::disk('local')->put($file, $process->getOutput());
            $durationSec = round(microtime(true) - $startedAt, 3);

            Logger::db('system', 'info', 'Datenbank-Backup erfolgreich', [
                'status' => 'success',
                'file' => $file,
                'database' => $database,
                'duration_s' => $durationSec,
                'size_bytes' => Storage::disk('local')->size($file) ?? null,
            ]);

            $this->info("Backup gespeichert unter storage/app/{$file}");
            $this->info("Starte Bereinigung alter Backup-Dateien...");

            foreach (Storage::disk('local')->files($basePath) as $oldBackup) 
			{
                $modified = Storage::disk('local')->lastModified($oldBackup);

                if ($modified !== false && ($now - $modified) > ($cleanupDays * 86400)) 
				{
                    Storage::disk('local')->delete($oldBackup);
                    $deletedFiles[] = $oldBackup;
                }
            }

            if (!empty($deletedFiles)) 
			{
                Logger::db('system', 'info', 'Alte Datenbank-Backups gelöscht', [
                    'count' => count($deletedFiles),
                    'files' => $deletedFiles,
                    'older_than_days' => $cleanupDays,
                ]);
            }

            return Command::SUCCESS;

        }
		catch (\Throwable $e) 
		{

            $durationSec = round(microtime(true) - $startedAt, 3);

            Logger::db('system', 'error', 'Datenbank-Backup fehlgeschlagen', [
                'status' => 'failed',
                'database' => $database,
                'duration_s' => $durationSec,
                'error' => $e->getMessage(),
                'exit_code' => $process->getExitCode(),
            ]);

            $this->error("Backup fehlgeschlagen: {$e->getMessage()}");

            return Command::FAILURE;
        }
    }
}
