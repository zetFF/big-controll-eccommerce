<?php

namespace App\Health\Checks;

use Spatie\Health\Checks\Check;
use Spatie\Health\Checks\Result;
use Illuminate\Support\Facades\Redis;

class RedisCheck extends Check
{
    protected ?string $name = 'Redis';

    public function run(): Result
    {
        try {
            Redis::ping();

            return Result::make()
                ->ok()
                ->shortSummary('Redis is operational');

        } catch (\Exception $e) {
            return Result::make()
                ->failed()
                ->shortSummary('Could not connect to Redis')
                ->meta(['error' => $e->getMessage()]);
        }
    }
} 