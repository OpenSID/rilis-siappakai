<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use romanzipp\QueueMonitor\Traits\IsMonitored;

class PengaturanEmailJob implements ShouldQueue
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
            '--kode_desa' => $request->get('kode_desa'),
            '--mail_host' => $request->get('mail_host'),
            '--mail_user' => $request->get('mail_user'),
            '--mail_pass' => $request->get('mail_pass'),
            '--mail_address' => $request->get('mail_address'),
        ];
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        Artisan::call('opensid:config-email', $this->params);
    }
}
