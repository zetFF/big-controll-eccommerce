<?php

namespace App\Services;

use App\Models\Backup;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use ZipArchive;
use Carbon\Carbon;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Process\Process;

class BackupService
{
    private string $backupPath;
    private string $tempPath;

    public function __construct()
    {
        $this->backupPath = storage_path('app/backups');
        $this->tempPath = storage_path('app/temp');

        if (!File::exists($this->backupPath)) {
            File::makeDirectory($this->backupPath, 0755, true);
        }

        if (!File::exists($this->tempPath)) {
            File::makeDirectory($this->tempPath, 0755, true);
        }
    }

    public function createBackup(string $type, array $options = []): Backup
    {
        $backup = Backup::create([
            'name' => $this->generateBackupName($type),
            'type' => $type,
            'status' => 'pending',
            'created_by' => auth()->id(),
            'started_at' => now(),
            'metadata' => $options,
        ]);

        dispatch(new \App\Jobs\ProcessBackup($backup));

        return $backup;
    }

    public function processBackup(Backup $backup): void
    {
        try {
            $backup->update(['status' => 'in_progress']);

            $path = match ($backup->type) {
                'full' => $this->createFullBackup(),
                'database' => $this->createDatabaseBackup(),
                'files' => $this->createFilesBackup(),
                'custom' => $this->createCustomBackup($backup->metadata),
                default => throw new \Exception('Invalid backup type'),
            };

            $size = File::size($path);

            $backup->update([
                'status' => 'completed',
                'path' => $path,
                'size' => $size,
                'disk' => 'local',
                'completed_at' => now(),
            ]);

            // Clean up old backups
            $this->cleanOldBackups();

        } catch (\Exception $e) {
            $backup->update([
                'status' => 'failed',
                'metadata' => array_merge(
                    $backup->metadata ?? [],
                    ['error' => $e->getMessage()]
                ),
            ]);

            throw $e;
        }
    }

    public function restoreBackup(Backup $backup): void
    {
        if ($backup->status !== 'completed') {
            throw new \Exception('Cannot restore incomplete backup');
        }

        match ($backup->type) {
            'full' => $this->restoreFullBackup($backup),
            'database' => $this->restoreDatabaseBackup($backup),
            'files' => $this->restoreFilesBackup($backup),
            'custom' => $this->restoreCustomBackup($backup),
            default => throw new \Exception('Invalid backup type'),
        };
    }

    private function createFullBackup(): string
    {
        $filename = $this->generateFileName('full');
        $tempPath = $this->tempPath . '/' . $filename;

        $zip = new ZipArchive();
        $zip->open($tempPath, ZipArchive::CREATE | ZipArchive::OVERWRITE);

        // Add database dump
        $databasePath = $this->createDatabaseBackup();
        $zip->addFile($databasePath, 'database.sql');

        // Add files
        $this->addFilesToZip($zip, storage_path('app/public'), 'storage');
        $this->addFilesToZip($zip, base_path('uploads'), 'uploads');

        $zip->close();

        // Move to final location
        $finalPath = $this->backupPath . '/' . $filename;
        File::move($tempPath, $finalPath);

        // Cleanup
        File::delete($databasePath);

        return $finalPath;
    }

    public function createDatabaseBackup(): Backup
    {
        $backup = Backup::create([
            'name' => 'Database Backup ' . now()->format('Y-m-d H:i:s'),
            'disk' => 'backups',
            'path' => 'database/backup-' . now()->format('Y-m-d-His') . '.sql',
            'type' => 'database',
            'status' => 'pending',
            'created_by' => auth()->id(),
            'size' => 0
        ]);

        try {
            $path = storage_path('app/backups/' . $backup->path);
            $directory = dirname($path);
            if (!is_dir($directory)) {
                mkdir($directory, 0755, true);
            }

            $command = sprintf(
                'mysqldump -h %s -u %s -p%s %s > %s',
                config('database.connections.mysql.host'),
                config('database.connections.mysql.username'),
                config('database.connections.mysql.password'),
                config('database.connections.mysql.database'),
                $path
            );

            $process = Process::fromShellCommandline($command);
            $process->run();

            if (!$process->isSuccessful()) {
                throw new \Exception('Database backup failed: ' . $process->getErrorOutput());
            }

            $backup->update([
                'status' => 'completed',
                'size' => filesize($path),
                'completed_at' => now(),
                'metadata' => [
                    'tables' => $this->getDatabaseTables(),
                    'total_records' => $this->getTotalRecords()
                ]
            ]);

            return $backup;
        } catch (\Exception $e) {
            $backup->update(['status' => 'failed']);
            Log::error('Backup failed: ' . $e->getMessage());
            throw $e;
        }
    }

    public function createFilesBackup(array $directories = []): Backup
    {
        $backup = Backup::create([
            'name' => 'Files Backup ' . now()->format('Y-m-d H:i:s'),
            'disk' => 'backups',
            'path' => 'files/backup-' . now()->format('Y-m-d-His') . '.zip',
            'type' => 'files',
            'status' => 'pending',
            'created_by' => auth()->id(),
            'size' => 0
        ]);

        try {
            $zip = new ZipArchive();
            $path = storage_path('app/backups/' . $backup->path);
            $directory = dirname($path);
            
            if (!is_dir($directory)) {
                mkdir($directory, 0755, true);
            }

            if ($zip->open($path, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
                throw new \Exception("Cannot create zip file");
            }

            $directories = $directories ?: [
                storage_path('app/public'),
                public_path('uploads')
            ];

            foreach ($directories as $directory) {
                if (!is_dir($directory)) continue;

                $files = new \RecursiveIteratorIterator(
                    new \RecursiveDirectoryIterator($directory),
                    \RecursiveIteratorIterator::LEAVES_ONLY
                );

                foreach ($files as $file) {
                    if (!$file->isFile()) continue;

                    $filePath = $file->getRealPath();
                    $relativePath = substr($filePath, strlen(base_path()) + 1);

                    $zip->addFile($filePath, $relativePath);
                }
            }

            $zip->close();

            $backup->update([
                'status' => 'completed',
                'size' => filesize($path),
                'completed_at' => now(),
                'metadata' => [
                    'directories' => $directories,
                    'total_files' => $this->countFiles($directories)
                ]
            ]);

            return $backup;
        } catch (\Exception $e) {
            $backup->update(['status' => 'failed']);
            Log::error('Backup failed: ' . $e->getMessage());
            throw $e;
        }
    }

    private function addFilesToZip(ZipArchive $zip, string $path, string $relativePath = ''): void
    {
        $files = File::allFiles($path);

        foreach ($files as $file) {
            $zip->addFile(
                $file->getRealPath(),
                $relativePath . '/' . $file->getRelativePathname()
            );
        }
    }

    private function cleanOldBackups(): void
    {
        $maxBackups = config('backup.max_backups', 5);
        
        Backup::where('status', 'completed')
            ->orderBy('created_at', 'desc')
            ->skip($maxBackups)
            ->take(PHP_INT_MAX)
            ->get()
            ->each(function ($backup) {
                if (File::exists($backup->path)) {
                    File::delete($backup->path);
                }
                $backup->delete();
            });
    }

    private function generateBackupName(string $type): string
    {
        return sprintf(
            'backup_%s_%s',
            $type,
            Carbon::now()->format('Y-m-d_H-i-s')
        );
    }

    private function generateFileName(string $type): string
    {
        return sprintf(
            '%s.zip',
            $this->generateBackupName($type)
        );
    }

    protected function getDatabaseTables(): array
    {
        return DB::select('SHOW TABLES');
    }

    protected function getTotalRecords(): int
    {
        $total = 0;
        $tables = $this->getDatabaseTables();

        foreach ($tables as $table) {
            $tableName = array_values((array) $table)[0];
            $total += DB::table($tableName)->count();
        }

        return $total;
    }

    protected function countFiles(array $directories): int
    {
        $total = 0;

        foreach ($directories as $directory) {
            if (!is_dir($directory)) continue;

            $files = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($directory),
                \RecursiveIteratorIterator::LEAVES_ONLY
            );

            foreach ($files as $file) {
                if ($file->isFile()) {
                    $total++;
                }
            }
        }

        return $total;
    }
}