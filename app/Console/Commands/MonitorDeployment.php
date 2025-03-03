<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MonitorDeployment extends Command
{
    protected $signature = 'deploy:monitor';
    protected $description = 'Monitor deployment status and health';

    public function handle()
    {
        $this->info('Monitoring deployment...');

        try {
            // Check application health
            $response = Http::get(config('app.url') . '/api/v1/health');
            
            if (!$response->successful()) {
                throw new \Exception('Health check failed');
            }

            // Check queue workers
            $queueSize = \Queue::size();
            if ($queueSize > 1000) {
                $this->warn("Large queue size detected: {$queueSize} jobs");
            }

            // Check cache
            if (!\Cache::has('app_version')) {
                \Cache::put('app_version', config('app.version'), 3600);
            }

            // Check database
            \DB::connection()->getPdo();

            $this->info('All systems operational!');
            
        } catch (\Exception $e) {
            Log::error('Deployment monitoring failed: ' . $e->getMessage());
            $this->error('Monitoring failed: ' . $e->getMessage());
            
            // Notify admin
            \Notification::route('mail', config('app.admin_email'))
                ->notify(new \App\Notifications\DeploymentFailed($e->getMessage()));
        }
    }
} 