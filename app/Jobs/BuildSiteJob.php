<?php

namespace App\Jobs;

use App\Models\Aplikasi;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use romanzipp\QueueMonitor\Traits\IsMonitored;

class BuildSiteJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, IsMonitored;
    private $params = [];
    private $params1 = [];
    private $mitra = [];
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
            '--domain_opensid' => $request->get('domain_opensid'),
            '--langganan_opensid' => $request->get('langganan_opensid'),
            '--tgl_akhir_premium' => $request->get('tgl_akhir_premium'),
            '--token_premium' => $request->get('token_premium'),
            '--kode_desa_default' => $request->get('kode_desa'),
        ];

        $this->mitra = [
            '--kode_desa' => str_replace('.', '', $request->get('kode_desa')),
            '--mitra' => $request->get('mitra'),
        ];

        $port = Aplikasi::pengaturan_aplikasi()['pengaturan_domain'];
        if ($port == 'proxy') {
            $this->params1 = [
                '--port_domain' => $request->get('port_domain'),
                '--port_vhost' => $port,
            ];

            $this->params = array_merge($this->params, $this->params1);
        }
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        if (env('OPENKAB') == 'true') {
            Artisan::call('opensid:build-site-openkab', $this->params);
        } else {
            Artisan::call('opensid:build-site', $this->params);
            Artisan::call('opensid:mitra', $this->mitra);
        }
    }
}
