<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use romanzipp\QueueMonitor\Traits\IsMonitored;

class AktivasiTemaJob implements ShouldQueue
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
            '--token_premium' => $request->get('token_premium'),
            '--aktivasi_tema' => strtolower($request->get('aktivasi_tema')),
            '--config_logo' => strtolower($request->get('logo')),
            '--config_kode_kota' => $request->get('kode_kota'),
            '--config_fbadmin' => $request->get('fbadmin'),
            '--config_fbappid' => $request->get('fbappid'),
            '--config_ip_address' => $request->get('ip_address'),
            '--config_color' => $request->get('color'),
            '--config_fluid' => strtolower($request->get('fluid')),
            '--config_menu' => strtolower($request->get('menu')),
        ];
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        Artisan::call('opensid:update-konfigurasi-tema', $this->params);
    }
}
