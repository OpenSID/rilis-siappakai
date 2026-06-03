<?php

namespace Database\Seeders;

use App\Models\OlsSystemVhost;
use Illuminate\Database\Seeder;

class OlsSystemVhostSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $systemVhosts = [
            [
                'vhost_name' => 'siappakai',
                'domain' => config('app.url') ? parse_url(config('app.url'), PHP_URL_HOST) : 'scriptopendesa.ngadakab.go.id',
                'keterangan' => 'Dasbor SiapPakai - platform utama',
                'aktif' => true,
            ],
            [
                'vhost_name' => 'openkab',
                'domain' => env('OLS_OPENKAB_DOMAIN', 'openkab.ngadakab.go.id'),
                'keterangan' => 'OpenKab - aplikasi OpenSID terpadu per kabupaten',
                'aktif' => true,
            ],
            [
                'vhost_name' => 'api-gabungan',
                'domain' => env('OLS_API_GABUNGAN_DOMAIN', 'apiopenkab.ngadakab.go.id'),
                'keterangan' => 'API Gabungan - REST API untuk OpenKab',
                'aktif' => true,
            ],
        ];

        foreach ($systemVhosts as $vhost) {
            OlsSystemVhost::updateOrCreate(
                ['vhost_name' => $vhost['vhost_name']],
                $vhost
            );
        }
    }
}
