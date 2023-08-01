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
        // $schedule->command('inspire')->hourly();
        $schedule->command('siappakai:backup-database')->timezone('Asia/Jakarta')->at('01:00');
        $schedule->command('siappakai:backup-folder-desa')->timezone('Asia/Jakarta')->at('02:00');
        $schedule->command('siappakai:update-saas')->timezone('Asia/Jakarta')->at('03:00');
        $schedule->command('siappakai:update-opensid')->timezone('Asia/Jakarta')->at('03:15');
        $schedule->command('siappakai:update-pbb')->timezone('Asia/Jakarta')->at('03:30');
        $schedule->command('siappakai:update-api')->timezone('Asia/Jakarta')->at('03:45');
        $schedule->command('siappakai:update-tema')->timezone('Asia/Jakarta')->at('04:00');
        $schedule->command('siappakai:delete-saas')->timezone('Asia/Jakarta')->at('04:00');
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
