<?php

namespace Database\Seeders\Pengaturan;

use App\Models\Aplikasi;
use Illuminate\Database\Seeder;

class AplikasiSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $aplikasi = array(
            ["key" => "kode_wilayah", "value" => "33.03.01.2001", "keterangan" => "Isi 2 digit kode provinsi", "jenis" => "text", "kategori" => "pengaturan_wilayah", "script" => ""],
            ["key" => "nama_wilayah", "value" => "PURBALINGGA, PROVINSI JAWA TENGAH", "keterangan" => "Isi nama provinsi", "jenis" => "text", "kategori" => "pengaturan_wilayah", "script" => ""],
            ["key" => "kode_provinsi", "value" => "33", "keterangan" => "Isi 2 digit kode provinsi", "jenis" => "text", "kategori" => "pengaturan_wilayah", "script" => ""],
            ["key" => "nama_provinsi", "value" => "JAWA TENGAH", "keterangan" => "Isi nama provinsi", "jenis" => "text", "kategori" => "pengaturan_wilayah", "script" => ""],
            ["key" => "kode_kabupaten", "value" => "03", "keterangan" => "Isi 2 digit kode kabupaten", "jenis" => "text", "kategori" => "pengaturan_wilayah", "script" => ""],
            ["key" => "nama_kabupaten", "value" => "PURBALINGGA", "keterangan" => "Isi nama kabupaten", "jenis" => "text", "kategori" => "pengaturan_wilayah", "script" => ""],
            ["key" => "nama_aplikasi", "value" => "Dasbor SiapPakai", "keterangan" => "Isi nama Aplikasi", "jenis" => "text", "kategori" => "pengganti_sebutan", "script" => ""],
            ["key" => "sebutan_kabupaten", "value" => "Kabupaten", "keterangan" => "Pengganti sebutan kabupaten/kota", "jenis" => "text", "kategori" => "pengganti_sebutan", "script" => ""],
            ["key" => "sebutan_desa", "value" => "Desa", "keterangan" => "Pengganti sebutan desa/keluarahan", "jenis" => "text", "kategori" => "pengganti_sebutan", "script" => ""],
            ["key" => "ip_source_code", "value" => "localhost", "keterangan" => "IP source code untuk akses ke database (misalkan xxx.xxx.xxx.xx)", "jenis" => "text", "kategori" => "pengganti_sebutan", "script" => ""],
            ["key" => "akun_pengguna", "value" => "", "keterangan" => "Pilih Akun Pengguna yang ditampilkan di navbar", "jenis" => "option", "kategori" => "", "script" => ""],
            ["key" => "latar_login", "value" => "", "keterangan" => "Kosongkan, jika latar login tidak berubah", "jenis" => "image", "kategori" => "latar_login", "script" => "previewLatarLogin()"],
            ["key" => "logo", "value" => "", "keterangan" => "Kosongkan, jika logo tidak berubah", "jenis" => "image", "kategori" => "logo", "script" => "previewLogo()"],
            ["key" => "logo_aplikasi", "value" => "", "keterangan" => "Kosongkan, jika logo aplikasi tidak berubah", "jenis" => "image", "kategori" => "logo", "script" => "previewLogoAplikasi()"],
            ["key" => "favicon", "value" => "", "keterangan" => "Kosongkan, jika favicon tidak berubah", "jenis" => "image", "kategori" => "logo", "script" => "previewLogoFavicon()"],
        );

        foreach ($aplikasi as $item) {
            Aplikasi::create($item);
        }
    }
}
