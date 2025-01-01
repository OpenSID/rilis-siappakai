<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        echo "\n";

        // ambil data pelanggan
        $pel = DB::table('pelanggan')->whereNull('kode_provinsi')->get();
        foreach ($pel as $key => $value) {
            $kode = explode('.', $value->kode_desa);
            $kode_prov = $kode[0];
            $kode_kab = $kode[0] . '.' . $kode[1];
            $kode_kec = $kode[0] . '.' . $kode[1] . '.' . $kode[2];

            // ambil data di pantau

            $prov = $this->pantau($kode_prov, 'prov');
            $kab =  $this->pantau($kode_kab, 'kab');
            $kec = $this->pantau($kode_kec, 'kec');

            DB::table('pelanggan')->where('id', $value->id)->update([
                'kode_provinsi' => $prov['kode'],
                'nama_provinsi' => $prov['nama'],
                'kode_kabupaten' => $kab['kode'],
                'nama_kabupaten' => $kab['nama'],
                'kode_kecamatan' => $kec['kode'],
                'nama_kecamatan' => $kec['nama'],
            ]);
            echo ('Berhasil memperbarui pelanggan : ' .  $value->nama_desa."\n");
        }
    }

    function pantau($kode, string $return)
    {
        // URL API yang akan diakses
        echo ('Mengambil data pantau dengan Kode : ' . $kode."\n");
        $url = 'https://pantau.opensid.my.id/api/wilayah/list_wilayah';
        $response = Http::retry(3, 100)->get($url, [
            'token' => 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpZCI6bnVsbCwidGltZXN0YW1wIjoxNjAzNDY2MjM5fQ.HVCNnMLokF2tgHwjQhSIYo6-2GNXB4-Kf28FSIeXnZw',
            'kode' => $kode
        ]);

        if ($response->successful()) {
            $json = $response->json();
            echo ('Berhasil mengambil data pantau dengan Kode : ' . $kode."\n");
            return [
                'kode' =>  $json['results'][0]['kode_' . $return],
                'nama' => $json['results'][0]['nama_' . $return]
            ];
        } else {
            throw $response->body();
            die();
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {

    }
};
