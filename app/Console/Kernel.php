<?php

namespace App\Console;

use App\Models\JadwalTugas;
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
        $jadwals = JadwalTugas::where('is_active', 1)->get();

        foreach ($jadwals as $task) {

            // Skip jika tidak ada jadwal yang valid
            if (empty($task->cron_expression) && empty($task->jam)) {
                continue;
            }

            $event = $schedule->command($task->command)
                ->timezone($task->timezone ?? 'Asia/Jakarta')
                ->withoutOverlapping();

            // PRIORITAS: cron_expression → jam
            if (!empty($task->cron_expression)) {
                $event->cron($task->cron_expression);
            } else {
                $event->at($task->jam);
            }

            // jika sukses
            $event->after(function () use ($task) {
                $task->update([
                    'last_run_at' => now(),
                    'log' => 'SUCCESS: ' . now()->format('Y-m-d H:i:s'),
                ]);
            });

            // jika gagal
            $event->onFailure(function () use ($task) {
                $task->update([
                    'log' => 'FAILED: ' . now()->format('Y-m-d H:i:s'),
                ]);
            });
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
