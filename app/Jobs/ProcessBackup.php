<?php

namespace App\Jobs;

use App\Models\Backup;
use App\Services\BackupService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessBackup implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 3600; // 1 hour
    public $tries = 1;

    public function __construct(
        private Backup $backup
    ) {}

    public function handle(BackupService $backupService): void
    {
        $backupService->processBackup($this->backup);
    }

    public function failed(\Throwable $exception): void
    {
        $this->backup->update([
            'status' => 'failed',
            'metadata' => array_merge(
                $this->backup->metadata ?? [],
                ['error' => $exception->getMessage()]
            ),
        ]);

        // Notify admin about backup failure
        // Implementation depends on your notification system
    }
} 