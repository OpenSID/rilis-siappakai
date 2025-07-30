<?php

namespace App\Console\Commands;

use App\Models\Aplikasi;
use App\Models\Pelanggan;
use App\Services\PbbService;
use Illuminate\Console\Command;
use App\Services\ProcessService;
use App\Services\ApiOpensidService;
use Illuminate\Filesystem\Filesystem;
use Symfony\Component\Process\Process;
use App\Http\Controllers\Helpers\EnvController;
use App\Http\Controllers\Helpers\IndexController;
use App\Http\Controllers\Helpers\VhostController;

use App\Http\Controllers\Helpers\ConfigController;
use App\Http\Controllers\Helpers\CommandController;
use App\Http\Controllers\Helpers\KoneksiController;
use App\Http\Controllers\Helpers\AttributeSiapPakaiController;

class UpdateTokenPremium extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'siappakai:update-token-premium {--kode_desa=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Pembaruan Token di desa tertentu berdasarkan kode desa';

    private $aplikasi;
    private $att;
    private $command;
    private $files;
    private $filesEnv;
    private $filesIndex;
    private $filesOpenSID;
    private $folderPbb;
    private $folderApi;
    private $kode_desa_default;
    private $koneksi;
    private $symlinkCorrupted = false;
    private $vhost;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->aplikasi = new Aplikasi();
        $this->att = new AttributeSiapPakaiController();
        $this->command = new CommandController();
        $this->files = new Filesystem();
        $this->filesEnv = new EnvController();
        $this->filesIndex = new IndexController();
        $this->filesOpenSID = new ConfigController();
        $this->koneksi = new KoneksiController();
        $this->vhost = new VhostController();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->kode_desa_default = $this->option('kode_desa');
        $kodedesa = str_replace('.', '', $this->option('kode_desa'));

        $this->att->setSiteFolderOpensid($this->att->getMultisiteFolder() . $kodedesa);
        $this->folderPbb = $this->att->getSiteFolderOpensid() . DIRECTORY_SEPARATOR . 'pbb-app';
        $this->folderApi = $this->att->getSiteFolderOpensid() . DIRECTORY_SEPARATOR . 'api-app';
        $this->att->setIndexDesa($this->att->getSiteFolderOpensid() . DIRECTORY_SEPARATOR . 'index.php');
        $this->att->setIndexPbb($this->folderPbb . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'index.php');
        $this->att->setIndexApi($this->folderApi . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'index.php');

        $umum = $this->att->getRootFolder() . 'master-opensid' . DIRECTORY_SEPARATOR . 'umum';

        if (!file_exists($umum)) {
            $this->symlinkCorrupted = true;
        }

        if (!file_exists($this->att->getSiteFolderOpensid())) {
            $this->att->notifMessage("Peringatan: kode desa tidak ditemukan");
            return die();
        }

        $konfigurasi = $this->filesOpenSID->konfigurasi($this->kode_desa_default);

        if ($this->att->getSiteFolderOpensid()) {

            $urlApp = substr($konfigurasi['domain'], 0, 8) == "https://" ? $konfigurasi['domain'] : "https://" . $konfigurasi['domain'];

            // status langganan
            $tglakhir = $konfigurasi['tgl_akhir_premium'];
            $hariini = date('Y-m-d');
            $selisih = (strtotime($hariini) - strtotime($tglakhir)) / 60 / 60 / 24;
            $langganan = $this->filesIndex->langganan($selisih);

            $port_vhost = $this->aplikasi::pengaturan_aplikasi()['pengaturan_domain'];
            $port_domain = null;
            if ($port_vhost == 'proxy') {
                $pelanggan = Pelanggan::where('kode_desa', $this->kode_desa_default)->first();
                $port_domain = $pelanggan->port_domain ?: 80;
            }
            if ($konfigurasi['domain'] != '-') {
                $this->vhost->setVhostOpensid($this->att->getSiteFolderOpensid(), $konfigurasi['domain'], $port_vhost, $port_domain);
                $this->vhost->setVhostApache($this->att->getSiteFolderOpensid(), $konfigurasi['domain'], $port_vhost, $port_domain);
            }

            $this->updateOpenSID($kodedesa, $konfigurasi, $langganan);
            $this->updateApi($kodedesa, $konfigurasi, $langganan, $urlApp);
            $this->updatePbb($kodedesa, $konfigurasi, $langganan, $urlApp);
            ProcessService::aturKepemilikanDirektori($this->att->getSiteFolderOpensid());
        }
    }

    public function updateOpenSID($kodedesa, $konfigurasi, $langganan)
    {
        // unlink opensid
        $this->command->unlinkCommandOpenSid($this->att->getSiteFolderOpensid(), $this->symlinkCorrupted);

        // konfigurasi config OpenSID premium
        $this->filesOpenSID->configDesa(
            $kodedesa,
            $konfigurasi['token_premium'], // diambil dari table pelanggan agar dapat digunakan di OpenKab
            $konfigurasi,
            $konfigurasi['smtp_protocol'],
            $konfigurasi['smtp_host'],
            $konfigurasi['smtp_user'],
            $konfigurasi['smtp_pass'],
            $konfigurasi['smtp_port'],
            $this->att->getServerLayanan(),
            $this->att->getConfigSiteFolder(),                                           //configFolder
            $this->att->getConfigSiteFolder() . DIRECTORY_SEPARATOR . 'config.php',      //configSite
            $this->att->getConfigTemplateFolder() . DIRECTORY_SEPARATOR . 'config.php',  //configMaster
        );

        // ubah symlink di file index
        if (file_exists($this->att->getSiteFolderOpensid()) && file_exists($this->att->getIndexDesa())) {
            $this->filesIndex->indexPhpOpensid(
                $this->att->getRootFolder() . 'master-opensid' . DIRECTORY_SEPARATOR . $langganan,
                $this->att->getSiteFolderOpensid(),
                $this->att->getIndexTemplate(),
                $this->att->getIndexDesa()
            );
        }

        // eksekusi index.php di opensid untuk menjalankan migrasi dan pembuatan symlink
        $appKey = 'base64:' . base64_encode(random_bytes(32));
        $appKeyPath = $this->att->getSiteFolderOpensid() . DIRECTORY_SEPARATOR . 'desa' . DIRECTORY_SEPARATOR . 'app_key';
        if (!$this->files->exists($appKeyPath)) {
            // buat app_key
            $this->files->put($appKeyPath, $appKey);
            $this->command->chownFileCommand($appKeyPath);
            $this->command->chmodFileCommand($appKeyPath);

            // database
            $openkab = env('OPENKAB') == 'true' ? nama_database_gabungan() : $kodedesa;

            // jika config pada database opensid >= 1, maka itu database gabungan
            $dbOpensid = $this->koneksi->getObjDatabase($openkab);
            if ($dbOpensid) {
                $totalDesa = $dbOpensid->query('select * from config')->num_rows;
                $this->command->notifMessageNotice('totalDesa ' . $totalDesa);
                if ($totalDesa >= 1) {
                    $this->command->notifMessageNotice('jalankan generate desa baru');
                    // buat desa baru pada config
                    $this->command->indexDesaBaru($this->att->getSiteFolderOpensid());
                }
            }
        } else {
            $this->command->migratePremium($this->att->getSiteFolderOpensid());
        }
    }

    public function updatePbb($kodedesa, $konfigurasi, $langganan, $urlApp)
    {
        // unlink
        $this->command->unlinkCommandAppLaravel($this->folderPbb, $this->symlinkCorrupted);
        $this->command->unlinkCommandAppLaravelPublic($this->folderPbb, $this->symlinkCorrupted);

        // pasang template Api jika tidak ada
        $pbbService = new PbbService();
        $pbbService->installTemplateDesa($kodedesa);
    }

    public function updateApi($kodedesa, $konfigurasi, $langganan, $urlApp)
    {
        // unlink
        $this->command->unlinkCommandAppLaravel($this->folderApi, $this->symlinkCorrupted);

        // pasang template Api jika tidak ada
        $apiService = new ApiOpensidService();
        $apiService->installTemplateDesa($kodedesa);
    }
}
