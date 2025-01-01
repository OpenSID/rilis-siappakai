<?php

namespace App\Services;

use App\Enums\Level;
use App\Enums\Opensid;
use App\Http\Controllers\Helpers\AttributeSiapPakaiController;
use App\Models\Aplikasi;
use App\Models\Pelanggan;
use App\Services\TemaService;

class AplikasiService
{
    public function simpanPengaturan($data)
    {
        foreach ($data as $key => $value) {

            Aplikasi::where('key', $key)->update(['value' => $value]);
        }
    }

    function pengaturanApikasi($key)
    {
        return Aplikasi::where('key', $key)->first()->value ?? '';
    }

    /**
     * Memeriksa apakah pengaturan OpenSID sesuai dengan yang diberikan.
     *
     * @param string $opensid Nama pengaturan OpenSID yang akan di cek.
     *
     * @return bool True jika pengaturan sesuai, false jika tidak.
     */
    public function cekPengaturanOpensid($opensid)
    {
        $pengaturan = Aplikasi::where('key', 'opensid')->first();
        if ($opensid == 'umum' && ($pengaturan->value == Opensid::PREMIUM->value)) {
            return false;
        }
        if ($opensid == 'premium' && ($pengaturan->value == Opensid::UMUM->value)) {
            return false;
        };

        return true;
    }

    /**
     * Memperbarui template OpenSID yang ada di folder pelanggan.
     *
     * Fungsi ini memperbarui template OpenSID yang ada di folder pelanggan dengan
     * menggantikan variabel yang ada di template OpenSID dengan data pelanggan.
     *
     * Variabel yang digantikan adalah:
     * - {$kodedesa}
     * - {$token_premium}
     * - {$server_layanan}
     * - {$web_theme}
     * - {$tingkatan_database}
     *
     * Jika domain opensid tidak sama dengan '-', maka akan menambahkan
     * konfigurasi khusus untuk domain tersebut.
     *
     */
    function updateTemplate()
    {
        // dapatkan alamat server layanan
        $att = new AttributeSiapPakaiController();
        $server_layanan = $att->getServerLayanan();

        // dapatkan data pelanggan
        $customers = Pelanggan::all();
        $tema_bawaan = self::pengaturanApikasi('tema_bawaan');
        $folderTemplate = base_path() . DIRECTORY_SEPARATOR . 'master-template' . DIRECTORY_SEPARATOR . 'template-opensid';
        $configTemplate = $folderTemplate . DIRECTORY_SEPARATOR . 'desa' . DIRECTORY_SEPARATOR .'config'. DIRECTORY_SEPARATOR . 'config.php';
        foreach ($customers as $customer) {
            $folder_opensid = str_replace('.', '', $customer->kode_desa);
            $configCustomer = config('siappakai.root.folder_multisite') . $folder_opensid . DIRECTORY_SEPARATOR . 'desa' . DIRECTORY_SEPARATOR .'config' . DIRECTORY_SEPARATOR . 'config.php';

            // perbarui variabel config.php
            $fileservice = new FileService();

            $replace = $this->getReplaceArray($customer, $server_layanan, $tema_bawaan);

            if ($customer->domain_opensid != '-') {
                $newKonfigurasi = $this->getNewKonfigurasi($customer);
                $replace = array_merge($replace, $newKonfigurasi);

            }

            $fileservice->processTemplate($configTemplate, $configCustomer,  $replace);
        }
    }

    /**
     * Membuat array untuk menggantikan variable yang ada di template konfigurasi opensid
     *
     * @param Pelanggan $customer Data pelanggan yang akan di proses
     * @param string $server_layanan Alamat server layanan
     * @param string $tema_bawaan Nama tema bawaan
     *
     * @return array Array yang berisi variable yang akan di gantikan
     */
    private function getReplaceArray($customer, $server_layanan, $tema_bawaan)
    {
        $tingkatan_database = Aplikasi::where('key', 'level')->first()->value ?? Level::Kabupaten->value;
        return [
            '{$kodedesa}' => $customer->kode_desa_without_dot,
            '{$token_premium}' => $customer->token_premium,
            '{$server_layanan}' => $server_layanan,
            '{$web_theme}' => $tema_bawaan,
            '{$tingkatan_database}' => $tingkatan_database
        ];
    }

    /**
     * Mendapatkan konfigurasi tema dan email yang dibutuhkan untuk menggantikan variabel
     * dalam file config.php.
     *
     * @param Pelanggan $customer Data pelanggan yang akan di proses.
     *
     * @return array Konfigurasi tema dan email yang dibutuhkan untuk menggantikan variabel.
     */
    private function getNewKonfigurasi($customer)
    {
        $configTema = TemaService::getAtribute($customer->kode_desa);
        $configEmail = EmailService::getEmailConfiguration($customer->id);

        return [
            '{$aktivasi_tema}' => $configTema['aktivasi_tema'],
            '{$config_logo}' => $configTema['logo'],
            '{$config_kode_kota}' => $configTema['kode_kota'],
            '{$config_fbadmin}' => $configTema['fbadmin'],
            '{$config_fbappid}' => $configTema['fbappid'],
            '{$config_ip_address}' => $configTema['ip_address'],
            '{$config_color}' => $configTema['color'],
            '{$config_fluid}' => $configTema['fluid'],
            '{$config_menu}' => $configTema['menu'],
            '{$config_chats}' => $configTema['chats'],
            '{$config_widget}' => $configTema['widget'],
            '{$config_style}' => $configTema['style'],
            '{$config_hide_layanan}' => $configTema['hide_layanan'],
            '{$config_hide_banner_layanan}' => $configTema['hide_banner_layanan'],
            '{$config_hide_banner_laporan}' => $configTema['hide_banner_laporan'],
            '{$smtp_protocol}' => $configEmail['smtp_protocol'],
            '{$smtp_host}' => $configEmail['smtp_host'],
            '{$smtp_user}' => $configEmail['smtp_user'],
            '{$smtp_pass}' => $configEmail['smtp_pass'],
            '{$smtp_port}' => $configEmail['smtp_port'],
        ];
    }
}
