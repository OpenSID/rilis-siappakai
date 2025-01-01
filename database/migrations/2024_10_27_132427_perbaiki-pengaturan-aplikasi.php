<?php

use Illuminate\Support\Facades\DB;
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
        $data = DB::table('pengaturan_aplikasi')->get();
        foreach ($data as $key => $value) {
            DB::table('pengaturan_aplikasi')->where('key', $value->key)->update(['urut' => $key + 3]);
        }

        DB::table('pengaturan_aplikasi')->where('key', 'level')->update(['urut' => 1]);
        DB::table('pengaturan_aplikasi')->where('key', 'nama_wilayah')->update(['urut' => 2]);
        DB::table('pengaturan_aplikasi')->where('key', 'nama_aplikasi')->update(['urut' => 3]);
        DB::table('pengaturan_aplikasi')->where('key', 'sebutan_kabupaten')->update(['urut' => 4]);
        DB::table('pengaturan_aplikasi')->where('key', 'sebutan_desa')->update(['urut' => 5]);
        DB::table('pengaturan_aplikasi')->where('key', 'ip_source_code')->update(['urut' => 6]);
        DB::table('pengaturan_aplikasi')->where('key', 'host_backup_server')->update(['urut' => 7]);
        DB::table('pengaturan_aplikasi')->where('key', 'permission')->update(['urut' => 8]);

        // update tema
        $tema = [
            ["value" => "bima", "label" => "Tema Bima"],
            ["value" => "esensi", "label" => "Tema Esensi"],
            ["value" => "natra", "label" => "Tema Natra"],
        ];
        DB::table('pengaturan_aplikasi')->where('key', 'tema_bawaan')->update(['options' => json_encode($tema), 'urut' => 9]);

        // update pengaturan domain
        $engine = [
            ["value" => "apache", "label" => "Apache"],
            ["value" => "proxy", "label" => "Proxy"],
        ];
        DB::table('pengaturan_aplikasi')->where('key', 'pengaturan_domain')->update(['options' => json_encode($engine), 'urut' => 10]);

        // update akun_pengguna
        $akun_pengguna = [
            [
                'label' => 'Nama Lengkap',
                'value' => 1
            ],
            [
                'label' => 'Nama Pengguna',
                'value' => 2
            ],
            [
                'label' => 'Alamat Email',
                'value' => 3
            ]
        ];
        DB::table('pengaturan_aplikasi')->where('key', 'akun_pengguna')->update(['options' => json_encode($akun_pengguna), 'urut' => 11]);

        DB::table('pengaturan_aplikasi')->where('key', 'waktu_backup')->update(['urut' => 12]);
        DB::table('pengaturan_aplikasi')->where('key', 'maksimal_backup')->update(['urut' => 13]);

        $cloudOptions = [
            ["value" => "drive", "label" => "Google Drive"],
            ["value" => "sftp", "label" => "VPS / SFTP"],
        ];

        DB::table('pengaturan_aplikasi')->where('key', 'cloud_storage')->update(['jenis' => 'option_multiple', 'options' => json_encode($cloudOptions), 'class' => 'multiSelect', 'placeholder' => 'Pilih Cloud Storage', 'urut' => 14]);

        // update serve panel
        $server_panel = [
            ["value" => 1, "label" => "aaPanel"],
            ["value" => 2, "label" => "VPS Biasa"],
        ];
        DB::table('pengaturan_aplikasi')->where('key', 'server_panel')->update(['options' => json_encode($server_panel), 'urut' => 15]);

        DB::table('pengaturan_aplikasi')->where('key', 'aapanel_key')->update(['urut' => 16]);
        DB::table('pengaturan_aplikasi')->where('key', 'aapanel_ip')->update(['urut' => 17]);
        DB::table('pengaturan_aplikasi')->where('key', 'aapanel_php')->update(['urut' => 18]);

        // update multiphp
        $multiphp = [
            ["value" => 1, "label" => "Ya"],
            ["value" => 2, "label" => "Tidak"]
        ];
        DB::table('pengaturan_aplikasi')->where('key', 'multiphp')->update(['options' => json_encode($multiphp), 'urut' => 19]);

        // update paksa https
        $https = [
            ["value" => 1, "label" => "Ya"],
            ["value" => 0, "label" => "Tidak"],
        ];
        DB::table('pengaturan_aplikasi')->where('key', 'redirect_https')->update(['options' => json_encode($https), 'urut' => 20]);

        // update donotusecolumnstatistics
        $donotusecolumnstatistics = [
            ["value" => "1", "label" => "Ya"],
            ["value" => "2", "label" => "Tidak"]
        ];
        DB::table('pengaturan_aplikasi')->where('key', 'donotusecolumnstatistics')->update(['options' => json_encode($donotusecolumnstatistics), 'urut' => 21]);

        // update level
        $level = [
            ["value" => 1, "label" => "Level 1 Provinsi"],
            ["value" => 2, "label" => "Level 2 Kabupaten"],
            ["value" => 3, "label" => "Level 3 Kecamatan"],
        ];
        DB::table('pengaturan_aplikasi')->where('key', 'level')->update(['options' => json_encode($level)]);

        // ubah ke hidden
        DB::table('pengaturan_aplikasi')->whereIn(
            'key',
            [
                'kode_kabupaten',
                'nama_wilayah',
                'kode_wilayah',
                'kode_provinsi',
                'nama_provinsi',
                'kode_kabupaten',
                'nama_kabupaten'
            ]
        )->update(['jenis' => 'hidden']);

        // tambah pengaturan
        // ambil wilayah dulu
        $nama_wilayah = DB::table('pengaturan_aplikasi')->where('key',  'nama_wilayah')->first();
        DB::table('pengaturan_aplikasi')->insert([
            'key' => 'pengaturan_wilayah',
            'urut' => 2,
            'options' => json_encode([]),
            'value' =>  $nama_wilayah->value,
            'jenis' => 'option',
            'kategori' => 'pengaturan_wilayah',
            'keterangan' => '',
            'required' => 1,
            'created_at' => now(),
            'updated_at' => now(),
            'script' => 'pages.pengaturan.aplikasi.components.pengaturan-wilayah',

        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Mengatur ulang nilai 'urut' untuk semua baris di tabel 'pengaturan_aplikasi' ke null
        DB::table('pengaturan_aplikasi')->update(['urut' => null]);
    }
};
