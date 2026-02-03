<?php

namespace App\Console\Commands;

use App\Http\Controllers\Helpers\AttributeSiapPakaiController;
use App\Http\Controllers\Helpers\CommandController;
use App\Jobs\SslJob;
use App\Models\Pelanggan;
use Illuminate\Console\Command;

class UpdateSslLetsencrypt extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'siappakai:update-ssl-lets-encrypt';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update SSL Let\'s Encrypt pada semua domain yang belum aktif.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        // data pelanggan
    
        $pelanggans = Pelanggan::where('jenis_ssl', 'letsencrypt')
            ->where('tgl_akhir', '<=', now()->addDays(7)->toDateString())
            ->whereNotNull('domain_opensid')
            ->get();

        foreach ($pelanggans as $pelanggan) {
            $data = [
                'domain' => $pelanggan->domain_opensid,
                'update' => true,
            ];

            SslJob::dispatch($data, false);
        }
    }
}
