<?php

namespace App\Console\Commands;

use App\Models\Product;
use App\Models\User;
use App\Models\Order;
use Illuminate\Console\Command;

class ManageSearchIndices extends Command
{
    protected $signature = 'search:index
                          {action : Action to perform (rebuild/clear)}
                          {--model= : Specific model to index}';

    protected $description = 'Manage search indices';

    private $models = [
        Product::class,
        User::class,
        Order::class
    ];

    public function handle()
    {
        $action = $this->argument('action');
        $modelClass = $this->option('model');

        if ($modelClass && !in_array($modelClass, $this->models)) {
            $this->error('Invalid model specified');
            return 1;
        }

        $models = $modelClass ? [$modelClass] : $this->models;

        match ($action) {
            'rebuild' => $this->rebuildIndices($models),
            'clear' => $this->clearIndices($models),
            default => $this->error('Invalid action specified')
        };
    }

    private function rebuildIndices(array $models): void
    {
        $this->info('Rebuilding search indices...');

        foreach ($models as $model) {
            $this->info("Processing {$model}...");

            $model::chunk(100, function ($records) {
                $records->first()::bulkIndex($records);
            });
        }

        $this->info('Search indices rebuilt successfully!');
    }

    private function clearIndices(array $models): void
    {
        $this->info('Clearing search indices...');

        foreach ($models as $model) {
            $this->info("Clearing index for {$model}...");
            
            $model::chunk(100, function ($records) {
                foreach ($records as $record) {
                    $record->deleteSearchIndex();
                }
            });
        }

        $this->info('Search indices cleared successfully!');
    }
} 