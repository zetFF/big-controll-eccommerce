<?php

namespace App\Console\Commands;

use App\Services\BackupService;
use Illuminate\Console\Command;

class CreateBackupCommand extends Command
{
    protected $signature = 'backup:create {type=full : Type of backup (database/files/full)}';
    protected $description = 'Create a new backup';

    public function handle(BackupService $backupService)
    {
        $type = $this->argument('type');

        try {
            $this->info("Creating {$type} backup...");

            if ($type === 'database' || $type === 'full') {
                $backup = $backupService->createDatabaseBackup();
                $this->info("Database backup created: {$backup->path}");
            }

            if ($type === 'files' || $type === 'full') {
                $backup = $backupService->createFilesBackup();
                $this->info("Files backup created: {$backup->path}");
            }

            $this->info('Backup completed successfully!');
            return 0;
        } catch (\Exception $e) {
            $this->error('Backup failed: ' . $e->getMessage());
            return 1;
        }
    }
} 