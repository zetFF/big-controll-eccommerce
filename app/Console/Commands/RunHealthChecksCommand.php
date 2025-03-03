<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Spatie\Health\Health;
use Spatie\Health\ResultStores\ResultStore;

class RunHealthChecksCommand extends Command
{
    protected $signature = 'health:check';
    protected $description = 'Run all health checks';

    public function handle(Health $health, ResultStore $resultStore): int
    {
        $this->info('Running health checks...');

        $checkResults = $health->checkAll();
        $resultStore->save($checkResults);

        $this->info('Health checks completed!');

        $failedChecks = $checkResults->filter(fn($result) => !$result->ok());

        if ($failedChecks->count() > 0) {
            $this->error('Some checks have failed:');
            foreach ($failedChecks as $result) {
                $this->error("- {$result->name}: {$result->shortSummary}");
            }
            return 1;
        }

        $this->info('All checks passed!');
        return 0;
    }
} 