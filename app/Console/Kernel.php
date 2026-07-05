<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        $schedule->command('accounting:reconcile-daily --fix --notify')
            ->dailyAt('02:00')
            ->withoutOverlapping()
            ->runInBackground();

        $schedule->command('accounting:validate-integrity')
            ->dailyAt('03:00')
            ->withoutOverlapping()
            ->runInBackground();

        $schedule->command('accounting:retry-failures')
            ->hourly()
            ->withoutOverlapping()
            ->runInBackground();

        $schedule->command('accounting:process-recurring')
            ->dailyAt('01:00')
            ->withoutOverlapping()
            ->runInBackground();

        $schedule->command('accounting:remind-overdue')
            ->weeklyOn(1, '08:00')
            ->withoutOverlapping()
            ->runInBackground();
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
