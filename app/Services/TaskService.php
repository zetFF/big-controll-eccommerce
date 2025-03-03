<?php

namespace App\Services;

use App\Models\Task;
use App\Models\TaskLog;
use Carbon\Carbon;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Throwable;

class TaskService
{
    public function schedule(Task $task): void
    {
        try {
            $task->update([
                'status' => 'running',
                'last_run_at' => now()
            ]);

            $log = TaskLog::create([
                'task_id' => $task->id,
                'status' => 'running',
                'started_at' => now()
            ]);

            $startTime = microtime(true);
            $output = [];
            $exitCode = Artisan::call($task->command, [], $output);

            $duration = microtime(true) - $startTime;
            $status = $exitCode === 0 ? 'success' : 'failed';

            $log->update([
                'status' => $status,
                'output' => implode("\n", $output),
                'completed_at' => now(),
                'duration' => $duration,
                'error' => $exitCode !== 0 ? 'Command failed with exit code ' . $exitCode : null
            ]);

            $this->updateNextRunTime($task);

        } catch (Throwable $e) {
            Log::error('Task execution failed', [
                'task' => $task->name,
                'error' => $e->getMessage()
            ]);

            $task->update(['status' => 'failed']);
            $log->update([
                'status' => 'failed',
                'completed_at' => now(),
                'duration' => microtime(true) - $startTime,
                'error' => $e->getMessage()
            ]);
        }
    }

    public function updateNextRunTime(Task $task): void
    {
        // Parse cron expression and calculate next run time
        $cron = new \Cron\CronExpression($task->schedule);
        $nextRun = $cron->getNextRunDate(now(), 0, false, $task->timezone);

        $task->update([
            'status' => 'active',
            'next_run_at' => $nextRun
        ]);
    }

    public function getTaskStats(Task $task): array
    {
        $logs = $task->logs();

        return [
            'total_runs' => $logs->count(),
            'successful_runs' => $logs->successful()->count(),
            'failed_runs' => $logs->failed()->count(),
            'average_duration' => $logs->avg('duration'),
            'last_success' => $logs->successful()->latest()->first()?->completed_at,
            'last_failure' => $logs->failed()->latest()->first()?->completed_at
        ];
    }

    public function validateCronExpression(string $expression): bool
    {
        try {
            new \Cron\CronExpression($expression);
            return true;
        } catch (Throwable $e) {
            return false;
        }
    }
} 