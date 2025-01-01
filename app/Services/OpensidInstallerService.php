<?php

namespace App\Services;

use App\Enums\Opensid;
use App\Models\Aplikasi;
use App\Enums\RepositoryEnum;
use App\Enums\RepositoriOpensid;
use App\Services\DatabaseService;
use App\Services\RepositoryService;
use Illuminate\Support\Facades\File;
use Illuminate\Filesystem\Filesystem;
use App\Services\ServerLayananService;
use App\Http\Controllers\Helpers\TemaController;

class OpensidInstallerService extends MasterOpensidService
{
    private $temas;
    private $master;
    private $ip_source_code;
    private $tema_bawaan;
    private $serverLayanan;

    /**
     * Constructor untuk OpensidInstallerService.
     *
     * @param bool $master Nilai boolean untuk menentukan apakah installer
     *                     untuk master atau tidak. Jika true maka akan
     *                     melakukan installasi master sedang jika false maka
     *                     akan melakukan installasi gabungan.
     */
    public function __construct($master = false)
    {
        parent::__construct();
        // Inisialisasi controller tema
        $this->temas = new TemaController();
        $this->master = $master;

        // Mengambil konfigurasi dari environment atau pengaturan aplikasi
        $this->ip_source_code = env('OPENKAB') == 'true' ? Aplikasi::pengaturan_aplikasi()['ip_source_code'] : 'localhost';

        // Tema bawaan yang akan digunakan jika tema desa tidak ditemukan
        $this->tema_bawaan = env('OPENKAB') == 'true' ? Aplikasi::pengaturan_aplikasi()['tema_bawaan'] : 'esensi';

        // URL server layanan yang digunakan untuk mengunduh template
        $this->serverLayanan = ServerLayananService::getUrl();
    }


    /**
     *
     * Melakukan instalasi OpenSID gabungan untuk OpenSID umum/premium.
     *
     * @param string $opensid Nama OpenSID yang akan diinstall. Contoh: "Umum" atau "Premium"
     * @param string $kode_desa Kode desa yang akan diinstall
     * @param string|null $token_premium Token premium jika OpenSID yang diinstall adalah premium
     *
     * @return void
     */
    public function instalOpensid(string $opensid, string $kode_desa, ?string $token_premium = ''): void
    {
        $opensid = strtolower($opensid);
        $direktoriMaster = config('siappakai.root.folder') . 'master-opensid';
        $direktoriSitus = $direktoriMaster . DIRECTORY_SEPARATOR . $opensid;

        // Jika ini adalah instalasi master, maka clone repository terlebih dahulu
        if ($this->master) {
            $this->klonRepositori($opensid, $direktoriMaster);
            ProcessService::gitSafeDirectori($direktoriSitus);
            $namaDatabase = 'siappakai_opensid_' . $opensid;
        } else {
            $namaDatabase = 'gabungan_' . $opensid;
        }
        // Mengatur database dan konfigurasi Opensid
        $this->siapkanDatabase($namaDatabase, $kode_desa);
        $this->aturKonfigurasiOpensid($direktoriSitus, $namaDatabase, $kode_desa, $token_premium);
        $this->tanganiHtaccess($direktoriSitus);
        $this->siapkanLingkungan($direktoriSitus);
    }


    /**
     * Melakukan kloning repository OpenSID umum/premium
     *
     * @param string $opensid Jneis OpenSID yang akan diinstall. Contoh: "Umum" atau "Premium"
     * @param string $direktoriMaster Direktori master OpenSID
     *
     * @return void
     */
    private function klonRepositori(string $opensid, string $direktoriMaster): void
    {
        // cek folder master
        // jika sudah ada lewati clone repo
        if (!File::exists($direktoriMaster . DIRECTORY_SEPARATOR . $opensid)) {
            $repoEnum = RepositoryEnum::fromFolderName(strtolower($opensid));
            GitService::cloneRepository($repoEnum, $direktoriMaster);
            ProcessService::gitSafeDirectori($direktoriMaster . DIRECTORY_SEPARATOR . $opensid);
            echo "Proses Git clone Opensid selesai\n";
        }
    }

    /**
     * Mengatur file config.php dan database.php di dalam folder desa
     * yang dibuat dari template desa.
     *
     * @param string $direktoriSitus Folder site OpenSID yang akan diatur.
     * @param string $namaDatabase Nama database yang akan digunakan.
     * @param string $kode_desa Kode desa yang akan diatur.
     * @param string $token_premium Token premium untuk OpenSID premium.
     *
     * @return void
     */
    public function aturKonfigurasiOpensid(string $direktoriSitus, string $namaDatabase,  string $kode_desa, string $token_premium = ''): void
    {
        if (!file_exists($direktoriSitus)) {
            echo "Folder site tidak ditemukan: $direktoriSitus\n";
            return;
        }

        // kode ini untuk mengantisipasi struktur folder yang berubah di tahun depan
        if (strpos($direktoriSitus, 'premium') !== false) {
            $template = 'template-opensid';
        } else {
            $template = 'template-opensid'; // ini untuk umum
        }

        $direktoriDesa = $direktoriSitus . DIRECTORY_SEPARATOR . 'desa';
        $direktoriTemplateDesa = base_path() . DIRECTORY_SEPARATOR  .  'master-template' . DIRECTORY_SEPARATOR . $template . DIRECTORY_SEPARATOR . 'desa';

        ProcessService::BuatFolder($direktoriDesa);
        // Menentukan tema web yang akan digunakan
        $temaWeb = in_array($this->tema_bawaan, ['esensi', 'natra'])
            ? $this->tema_bawaan
            : 'desa/' . $this->tema_bawaan;

        if (file_exists($direktoriDesa)) {
            // Copy directory desa template ke folder desa
            File::copyDirectory($direktoriTemplateDesa, $direktoriDesa);

            $direktoriTemplateConfig = $direktoriTemplateDesa . DIRECTORY_SEPARATOR . 'config';
            $konfigurasiDesa = $direktoriDesa . DIRECTORY_SEPARATOR . 'config';
            $kodeDesaWithoutDot = str_replace('.', '', $kode_desa);

            // Mengatur file config.php
            $filesystem = new Filesystem();
            $templateKonfigurasi = $filesystem->get($direktoriTemplateConfig . DIRECTORY_SEPARATOR . 'config.php');

            $isiKonfigurasi = str_replace(
                ['{$kodedesa}', '{$token_premium}', '{$server_layanan}', '{$web_theme}'],
                [$kodeDesaWithoutDot, $token_premium, $this->serverLayanan, $temaWeb],
                $templateKonfigurasi
            );
            $filesystem->replace($konfigurasiDesa . DIRECTORY_SEPARATOR . 'config.php', $isiKonfigurasi);
            // Mengatur file database.php
            $templateDatabase = $filesystem->get(
                $konfigurasiDesa . DIRECTORY_SEPARATOR . 'database.php'
            );

            $isiDatabase = str_replace(
                ['{$kodedesa}', '{$database}', '{$db_host}'],
                [$kodeDesaWithoutDot, $namaDatabase,  $this->ip_source_code],
                $templateDatabase
            );
            $filesystem->replace($konfigurasiDesa . DIRECTORY_SEPARATOR . 'database.php', $isiDatabase);

            // Menjalankan proses instalasi tema premium
            ProcessService::runProcess(
                ['php', 'artisan', 'siappakai:install-tema-premium', '--symlink=true'],
                config('siappakai.root.folder') . 'dasbor-siappakai',
                'Menginstal tema premium...'
            );
        }
    }

    /**
     * Menyiapkan database untuk Opensid.
     *
     * @param string $namaDatabase Nama database yang akan dibuat
     *
     * @return void
     */
    private function siapkanDatabase(string $namaDatabase, string $kode_desa): void
    {
        echo "Memulai Proses Pembuatan database dan user\n";
        $kodeDesaWithoutDot = str_replace('.', '', $kode_desa);
        $databaseService = new DatabaseService($this->ip_source_code);
        $databaseService->createDatabase($namaDatabase);
        $databaseService->createUser($namaDatabase, $kodeDesaWithoutDot);
    }

    /**
     * Menyiapkan lingkungan untuk Opensid.
     *
     * Proses ini akan mengatur vendor tema, mengatur kepemilikan direktori, dan menjalankan
     * migrasi database.
     *
     * @param string $direktoriSitus Folder site tempat lingkungan akan diatur
     *
     * @return void
     */
    private function siapkanLingkungan(string $direktoriSitus): void
    {
        // Memasang vendor tema
        $this->temas->pemasanganVendorTema();
        // Mengatur kepemilikan direktori
        ProcessService::aturKepemilikanDirektori($direktoriSitus);
        // Menjalankan migrasi
        ProcessService::runProcess(['php', 'artisan', 'siappakai:migrate', "--path={$direktoriSitus}"], base_path());
    }
}
