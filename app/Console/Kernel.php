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

        $schedule->command('app:get-logs-b-m-s')->everyMinute();

        // $schedule->command('app:get-logs-b-m-s')->cron('0 5,10,19,22 * * *');

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
