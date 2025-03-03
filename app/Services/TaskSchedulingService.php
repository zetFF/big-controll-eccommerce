<?php

namespace App\Services;

use App\Models\Task;
use App\Models\TaskLog;
use Carbon\Carbon;
use Illuminate\Support\Facades\Artisan;
use Cron\CronExpression;

class TaskSchedulingService
{
    public function scheduleTask(array $data): Task
    {
        $task = Task::create(array_merge($data, [
            'status' => 'active',
            'created_by' => auth()->id(),
            'next_run_at' => $this->calculateNextRun($data['schedule']),
        ]));

        return $task;
    }

    public function executeTask(Task $task): TaskLog
    {
        if ($task->status === 'running' && !$task->overlap) {
            throw new \Exception('Task is already running');
        }

        $task->update(['status' => 'running']);

        $log = TaskLog::create([
            'task_id' => $task->id,
            'started_at' => now(),
        ]);

        try {
            $startTime = microtime(true);
            
            if (str_contains($task->command, 'artisan')) {
                $command = str_replace('php artisan ', '', $task->command);
                Artisan::call($command);
                $output = Artisan::output();
            } else {
                $output = shell_exec($task->command);
            }

            $runtime = microtime(true) - $startTime;

            $log->update([
                'status' => 'success',
                'output' => $output,
                'completed_at' => now(),
                'runtime' => $runtime,
            ]);

            $task->update([
                'status' => 'active',
                'last_run_at' => now(),
                'next_run_at' => $this->calculateNextRun($task->schedule),
            ]);

        } catch (\Exception $e) {
            $log->update([
                'status' => 'failed',
                'error' => $e->getMessage(),
                'completed_at' => now(),
            ]);

            $task->update([
                'status' => 'failed',
                'last_run_at' => now(),
            ]);

            throw $e;
        }

        return $log;
    }

    public function getDueTasks(): array
    {
        return Task::where('status', 'active')
            ->where('next_run_at', '<=', now())
            ->get()
            ->all();
    }

    private function calculateNextRun(array $schedule): Carbon
    {
        $expression = new CronExpression($this->arrayToCron($schedule));
        return Carbon::instance($expression->getNextRunDate());
    }

    private function arrayToCron(array $schedule): string
    {
        return sprintf(
            '%s %s %s %s %s',
            $schedule['minute'] ?? '*',
            $schedule['hour'] ?? '*',
            $schedule['day'] ?? '*',
            $schedule['month'] ?? '*',
            $schedule['weekday'] ?? '*'
        );
    }
} 