<?php

namespace App\Console\Commands;

use App\Services\TaskSchedulingService;
use Illuminate\Console\Command;

class RunScheduledTasks extends Command
{
    protected $signature = 'tasks:run';
    protected $description = 'Run due scheduled tasks';

    public function handle(TaskSchedulingService $scheduler)
    {
        $this->info('Running scheduled tasks...');

        $tasks = $scheduler->getDueTasks();

        foreach ($tasks as $task) {
            $this->info("Running task: {$task->name}");

            try {
                $log = $scheduler->executeTask($task);
                
                if ($log->status === 'success') {
                    $this->info("Task completed successfully");
                } else {
                    $this->error("Task failed: {$log->error}");
                }
            } catch (\Exception $e) {
                $this->error("Task execution failed: {$e->getMessage()}");
            }
        }

        $this->info('Finished running scheduled tasks');
    }
} 