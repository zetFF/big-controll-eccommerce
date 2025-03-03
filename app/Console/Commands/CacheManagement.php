<?php

namespace App\Console\Commands;

use App\Services\CacheService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class CacheManagement extends Command
{
    protected $signature = 'cache:manage 
                          {action : Action to perform (warmup/clear/stats)}
                          {--tag= : Specific cache tag to clear}';

    protected $description = 'Manage application cache';

    public function handle(CacheService $cacheService)
    {
        $action = $this->argument('action');
        $tag = $this->option('tag');

        switch ($action) {
            case 'warmup':
                $this->warmupCache($cacheService);
                break;
            case 'clear':
                $this->clearCache($tag);
                break;
            case 'stats':
                $this->showStats($cacheService);
                break;
            default:
                $this->error('Invalid action specified');
        }
    }

    private function warmupCache(CacheService $cacheService): void
    {
        $this->info('Warming up cache...');

        $cacheService->warmup([
            'products:featured' => fn() => \App\Models\Product::featured()->get(),
            'categories:all' => fn() => \App\Models\Category::all(),
            'settings:global' => fn() => \App\Models\Setting::all()
                ->pluck('value', 'key')
                ->toArray(),
        ]);

        $this->info('Cache warmup completed!');
    }

    private function clearCache(?string $tag): void
    {
        if ($tag) {
            Cache::tags($tag)->flush();
            $this->info("Cleared cache for tag: {$tag}");
        } else {
            Cache::flush();
            $this->info('Cleared all cache');
        }
    }

    private function showStats(CacheService $cacheService): void
    {
        $stats = $cacheService->getStats();
        $this->table(
            ['Metric', 'Value'],
            collect($stats)->map(fn($value, $key) => [$key, $value])->toArray()
        );
    }
} 