<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use romanzipp\QueueMonitor\Traits\IsMonitored;

class UnduhFolderDesaJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, IsMonitored;
    private $params = [];
    public $timeout = 7200;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($request = [])
    {
        $request = collect($request);
        $this->params = [
            '--directory_from' => $request->get('directory_from'),
            '--file' => $request->get('file'),
            '--directory' => $request->get('directory'),
        ];
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        Artisan::call('opensid:unduh-folder-desa', $this->params);
    }
}
