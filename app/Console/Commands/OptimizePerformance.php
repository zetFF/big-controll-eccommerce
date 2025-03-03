<?php

namespace App\Console\Commands;

use App\Services\DatabaseOptimizationService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;

class OptimizePerformance extends Command
{
    protected $signature = 'optimize:performance';
    protected $description = 'Run various performance optimizations';

    public function handle(DatabaseOptimizationService $dbOptimizer)
    {
        $this->info('Starting performance optimization...');

        // Clear all caches
        $this->info('Clearing caches...');
        Artisan::call('cache:clear');
        Artisan::call('view:clear');
        Artisan::call('route:clear');
        Artisan::call('config:clear');

        // Optimize database
        $this->info('Optimizing database...');
        $results = $dbOptimizer->optimizeTables();
        
        foreach ($results as $table => $status) {
            $this->line("Table {$table}: {$status}");
        }

        // Rebuild caches
        $this->info('Rebuilding caches...');
        Artisan::call('config:cache');
        Artisan::call('route:cache');
        Artisan::call('view:cache');

        // Analyze queries
        $this->info('Analyzing query performance...');
        $analysis = $dbOptimizer->analyzeQueryPerformance();
        
        foreach ($analysis as $query) {
            if (!empty($query['suggestions'])) {
                $this->warn("Slow query detected: {$query['query']}");
                foreach ($query['suggestions'] as $suggestion) {
                    $this->line("- Suggestion: {$suggestion}");
                }
            }
        }

        $this->info('Performance optimization completed!');
    }
} 