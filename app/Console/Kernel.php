<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // $schedule->command('app:get-logs-b-m-s')->cron('* * * * *');
        
        /**
         * Schedule the 'app:get-logs-b-m-s' command to run at the following times every day:
         * - 05:00 AM
         * - 10:00 AM
         * - 07:00 PM
         * - 10:00 PM
         *
         * Cron expression: '0 5,10,19,22 * * *'
         */
        $schedule->command('app:get-logs-b-m-s')->cron('* * * * *')->appendOutputTo(storage_path('logs/schedule.log'));
        // $schedule->command('app:get-logs-b-m-s')->cron('0 5,19,22,10 * * *')->cron('58 10 * * *')->appendOutputTo(storage_path('logs/schedule.log'));

        $schedule->command('app:get-logs-b-m-s')->cron('0 5,10,19,22 * * *')->appendOutputTo(storage_path('logs/schedule.log'));
        if (app_type() == 'saas') {
            $schedule->command(\App\SuperAdmin\Commands\UpdateLicenseExpiry::class)->daily();
            $schedule->command(\App\SuperAdmin\Commands\NotifyLicenseExpiryPre::class)->daily();
        }
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
