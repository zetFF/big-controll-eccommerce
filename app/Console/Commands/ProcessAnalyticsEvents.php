<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\DB;

class ProcessAnalyticsEvents extends Command
{
    protected $signature = 'analytics:process-events';
    protected $description = 'Process analytics events from Redis queue';

    public function handle()
    {
        $this->info('Processing analytics events...');

        while ($event = Redis::lpop('analytics:events')) {
            try {
                $data = json_decode($event, true);
                
                DB::table('analytics_events')->insert([
                    'event' => $data['event'],
                    'properties' => json_encode($data['properties']),
                    'user_id' => $data['user_id'],
                    'ip' => $data['ip'],
                    'user_agent' => $data['user_agent'],
                    'created_at' => $data['timestamp'],
                ]);
            } catch (\Exception $e) {
                $this->error("Failed to process event: {$e->getMessage()}");
                continue;
            }
        }

        $this->info('Finished processing events.');
    }
} 