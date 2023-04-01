<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use romanzipp\QueueMonitor\Traits\IsMonitored;

class TemaProJob implements ShouldQueue
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
            '--tema' => $request->get('tema'),
            '--kode_desa' => $request->get('kode_desa'),
            '--token_premium' => $request->get('token_premium'),
            '--domain_opensid' => $request->get('domain_opensid'),
            '--aktivasi_tema' => strtolower($request->get('aktivasi_tema')),
            '--config_logo' => strtolower($request->get('config_logo')),
            '--config_kode_kota' => $request->get('config_kode_kota'),
            '--config_fbadmin' => $request->get('config_fbadmin'),
            '--config_fbappid' => $request->get('config_fbappid'),
            '--config_ip_address' => $request->get('config_ip_address'),
            '--config_color' => $request->get('config_color'),
            '--config_fluid' => strtolower($request->get('config_fluid')),
            '--config_menu' => strtolower($request->get('config_menu')),
        ];
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        Artisan::call('opensid:install-tema', $this->params);
    }
}
