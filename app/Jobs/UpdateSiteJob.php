<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use romanzipp\QueueMonitor\Traits\IsMonitored;

class UpdateSiteJob implements ShouldQueue
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
            '--kode_desa' => str_replace('.', '', $request->get('kode_desa')),
            '--token_premium' => $request->get('token_premium'),
            '--kode_desa_default' => $request->get('kode_desa'),
            '--domain_opensid' => $request->get('domain_opensid'),
        ];
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        Artisan::call('opensid:update-pelanggan', $this->params);
    }
}
