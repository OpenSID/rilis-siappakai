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
        $schedule->command('opensid:backup-database')->timezone('Asia/Jakarta')->at('01:00');
        $schedule->command('opensid:backup-folder-desa')->timezone('Asia/Jakarta')->at('01:00');
        $schedule->command('opensid:update-saas')->timezone('Asia/Jakarta')->at('03:00');
        $schedule->command('opensid:update-premium')->timezone('Asia/Jakarta')->at('03:00');
        $schedule->command('opensid:update-pbb')->timezone('Asia/Jakarta')->at('03:00');
        $schedule->command('opensid:update-api')->timezone('Asia/Jakarta')->at('03:00');
        $schedule->command('opensid:update-tema')->timezone('Asia/Jakarta')->at('03:00');
        $schedule->command('opensid:delete-saas')->timezone('Asia/Jakarta')->at('03:00');
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
