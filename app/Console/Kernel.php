<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     *
     * These schedules are used to run console commands.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule): void
    {
        // $schedule->command('inspire')->hourly();
        $schedule->command('analytics:process-events')->everyFiveMinutes();
        $schedule->command('health:check')->everyFiveMinutes();

        // Daily database backup at 1 AM
        $schedule->command('backup:create database')
            ->dailyAt('01:00')
            ->when(fn() => config('backup.enabled', true));

        // Weekly full backup on Sundays at 2 AM
        $schedule->command('backup:create full')
            ->weekly()
            ->sundays()
            ->at('02:00')
            ->when(fn() => config('backup.enabled', true));
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
} 