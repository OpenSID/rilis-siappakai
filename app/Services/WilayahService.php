<?php
namespace App\Services;

use GuzzleHttp\Client;
use App\Models\Wilayah;

class WilayahService
{
    protected $client;
    protected $host;
    protected $token;

    public function __construct()
    {
        $this->client = new Client();
        $this->host = config('siappakai.pantau.api_pantau');
        $this->token = config('siappakai.pantau.token_pantau');
    }

    function InstallOpensid($jenis = 'premium')  {
        
    }

    public function ambilDataWilayah($kode_kabupaten)
    {
        $response = $this->client->get("{$this->host}/wilayah?kode={$kode_kabupaten}&token={$this->token}");
        $data = json_decode($response->getBody(), true);

        foreach ($data['results'] as $desa) {
            Wilayah::updateOrCreate(['kode_desa' => $desa['kode_desa']], [
                'nama_desa' => $desa['nama_desa'],
                'nama_kecamatan' => $desa['nama_kecamatan']
            ]);
        }
    }
}
