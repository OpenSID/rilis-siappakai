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

class DeleteSiteJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, IsMonitored;
    private $params = [];
    private $params1 = [];
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
        ];

        $port = Aplikasi::pengaturan_aplikasi()['pengaturan_domain'];
        if ($port == 'proxy') {
            $this->params1 = [
                '--port_domain' => $request->get('port_domain'),
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
            Artisan::call('opensid:delete-pelanggan-openkab', $this->params);
        } else {
            Artisan::call('opensid:delete-pelanggan', $this->params);
        }
    }
}
