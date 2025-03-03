<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Spatie\Health\Facades\Health;
use Spatie\Health\Checks\Checks\UsedDiskSpaceCheck;
use Spatie\Health\Checks\Checks\DatabaseCheck;
use Spatie\Health\Checks\Checks\CacheCheck;
use App\Health\Checks\RedisCheck;
use App\Health\Checks\PaymentGatewayCheck;

class HealthServiceProvider extends ServiceProvider
{
    public function register()
    {
        //
    }

    public function boot()
    {
        Health::checks([
            UsedDiskSpaceCheck::new()
                ->failWhenUsedSpaceIsAbovePercentage(90)
                ->warnWhenUsedSpaceIsAbovePercentage(70),
            
            DatabaseCheck::new(),
            
            CacheCheck::new(),
            
            RedisCheck::new(),
            
            PaymentGatewayCheck::new(),
        ]);
    }
} 