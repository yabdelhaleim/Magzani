<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     *
     * Notes:
     *  - In production, the central server runs `php artisan schedule:run`
     *    every minute via cron (see deployment docs).
     *  - Backup jobs are scheduled against the **central** database only.
     *    Tenant DBs are backed up by the central backup which uses
     *    `mysqldump --all-databases` (or a per-tenant script, see Spatie docs).
     *  - Cleanup is scheduled separately so backups are never deleted
     *    in the same window as a new run.
     */
    protected function schedule(Schedule $schedule): void
    {
        // ---------------------------------------------------------------
        // Existing accounting tasks (preserved as-is)
        // ---------------------------------------------------------------
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

        // ---------------------------------------------------------------
        // Backup tasks (added in this commit)
        // ---------------------------------------------------------------
        // Daily backup at 03:30 (after accounting reconciliation runs).
        // In production this lands on the disk(s) specified in BACKUP_DISKS,
        // typically S3.
        $backupTime = (string) env('BACKUP_DAILY_AT', '03:30');

        $schedule->command('backup:run')
            ->dailyAt($backupTime)
            ->withoutOverlapping(30)
            ->runInBackground()
            ->onFailure(function () {
                \Log::error('Backup scheduled run failed.');
            });

        // Cleanup at 04:30 — keep the most recent N backups per disk.
        $schedule->command('backup:clean')
            ->dailyAt('04:30')
            ->withoutOverlapping(30)
            ->runInBackground()
            ->onFailure(function () {
                \Log::error('Backup cleanup failed.');
            });

        // Health monitor at 05:00 — sends UnhealthyBackupWasFound
        // notifications if the latest backup is too old or too large.
        $schedule->command('backup:monitor')
            ->dailyAt('05:00')
            ->withoutOverlapping()
            ->runInBackground();

        // Per-tenant backup: the Spatie backup:run only covers the central
        // DB. We also iterate over every tenant DB and dump each one to
        // the configured backup disk. Skip when BACKUP_DAILY_TENANTS=false.
        if (env('BACKUP_DAILY_TENANTS', true)) {
            $schedule->command('tenants:backup ' . env('BACKUP_DISKS', 'local') . ' --keep=' . env('BACKUP_TENANT_KEEP', 7))
                ->dailyAt('03:45')
                ->withoutOverlapping(60)
                ->runInBackground();
        }

        // ---------------------------------------------------------------
        // Log rotation housekeeping (optional shell hint)
        // ---------------------------------------------------------------
        // The 'daily' log channel handles rotation automatically. The
        // schedule below is intentionally NOT included because rotation
        // happens at write time; adding a cron here would conflict.
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
