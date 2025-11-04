<?php

namespace App\Console\Commands;

use App\Models\Aplikasi;
use App\Models\Pelanggan;
use App\Services\PbbService;
use Illuminate\Console\Command;
use App\Services\ProcessService;
use App\Services\DatabaseService;
use Illuminate\Support\Facades\DB;
use App\Services\ApiOpensidService;
use App\Services\OpenLiteSpeedService;
use Illuminate\Filesystem\Filesystem;
use Symfony\Component\Process\Process;
use App\Http\Controllers\Helpers\EnvController;
use App\Http\Controllers\Helpers\IndexController;

use App\Http\Controllers\Helpers\VhostController;
use App\Http\Controllers\Helpers\ConfigController;
use App\Http\Controllers\Helpers\CommandController;
use App\Http\Controllers\Helpers\KoneksiController;
use App\Http\Controllers\Helpers\AttributeSiapPakaiController;
use App\Services\OpensidService;
use Exception;

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
    private $openLiteSpeed;

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

        // Initialize OpenLiteSpeed service if dependencies are available
        try {
            $this->openLiteSpeed = app(OpenLiteSpeedService::class);
        } catch (\Exception $e) {
            $this->openLiteSpeed = null;
        }
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
            $server_panel = $this->aplikasi::pengaturan_aplikasi()['server_panel'];
            $port_domain = null;
            if ($port_vhost == 'proxy') {
                $pelanggan = Pelanggan::where('kode_desa', $this->kode_desa_default)->first();
                $port_domain = $pelanggan->port_domain ?: 80;
            } else {
                $this->updateVirtualHost($server_panel, $kodedesa, $konfigurasi['domain'], $port_vhost, $port_domain);
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

        // jika config pada database opensid >= 1, maka itu database gabungan
        $databaseType = Aplikasi::where('key', 'pengaturan_database')->first();
        $ip_source_code = Aplikasi::pengaturan_aplikasi()['ip_source_code'] ?? 'localhost';
        $databaseService = new DatabaseService($ip_source_code);


        if ($databaseType && $databaseType->value == 'database_gabungan') {

            // konfigurasi Database OpenSID premium
            $this->filesOpenSID->configDatabaseBaru(
                $kodedesa,
                'gabungan_premium', // diambil dari table pelanggan agar dapat digunakan di OpenKab
                $ip_source_code,
                $this->att->getConfigSiteFolder() . DIRECTORY_SEPARATOR . 'database.php',      //configSite
                $this->att->getConfigTemplateFolder() . DIRECTORY_SEPARATOR . 'database.php',  //configMaster
            );

            // eksekusi index.php di opensid untuk menjalankan migrasi dan pembuatan symlink
            $appKey = 'base64:' . base64_encode(random_bytes(32));
            $appKeyPath = $this->att->getSiteFolderOpensid() . DIRECTORY_SEPARATOR . 'desa' . DIRECTORY_SEPARATOR . 'app_key';
            if (!$this->files->exists($appKeyPath)) {
                // buat app_key
                $this->files->put($appKeyPath, $appKey);
                $this->command->chownFileCommand($appKeyPath);
                $this->command->migratePremium($this->att->getSiteFolderOpensid());
            } else {
                $this->command->migratePremium($this->att->getSiteFolderOpensid());
            }

            $databaseService->createUser('gabungan_premium', $kodedesa);

            // Cek apakah tabel users sudah ada di database custom
            try {
                $totalDesa = DB::connection()->select("SELECT COUNT(*) as total FROM db_gabungan_premium.config")[0]->total;

                $this->command->notifMessageNotice('totalDesa ' . $totalDesa);
            } catch (\Exception $e) {
                $this->command->notifMessageNotice('Database db_gabungan_premium or table config not found, creating new setup');
                $totalDesa = 0;
            }

            if ($totalDesa >= 1) {
                $this->command->notifMessageNotice('jalankan generate desa baru');
                // buat desa baru pada config
                $this->command->indexDesaBaru($this->att->getSiteFolderOpensid());
            }
        } else {

            // cek tabel config di database db_kodedesa
            $dbName = 'db_' . $kodedesa;
            $databaseService->createUser($dbName, $kodedesa);

            $opensidservice = new OpensidService($this->kode_desa_default);

            if (!$databaseService->tableExists($dbName, 'config')) {
                $this->command->notifMessageNotice('jalankan generate desa baru');
                // migrasi desa baru
                $opensidservice->migrasiDatabaseTunggal($this->kode_desa_default);
            } else {
                $opensidservice->updateConfig($this->kode_desa_default);
            }
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

    /**
     * Update virtual host configuration based on server panel type
     *
     * @param string $server_panel
     * @param string $kodedesa
     * @param string $domain
     * @param string $port_vhost
     * @param int|null $port_domain
     * @return void
     */
    private function updateVirtualHost($server_panel, $kodedesa, $domain, $port_vhost, $port_domain)
    {
        try {
            // Check if using OpenLiteSpeed
            if ($server_panel == "3" && $this->openLiteSpeed) {
                $this->updateOpenLiteSpeedVirtualHost($kodedesa, $domain);
            } else {
                // Use traditional Apache/proxy configuration

                $confFilePath = $this->att->getApacheConfDir() . $domain . '.conf';
                $isExpiredVhost = false;

                if (file_exists($confFilePath)) {
                    $confContent = file_get_contents($confFilePath);
                    if ($confContent && str_contains($confContent, 'expired.html')) {
                        $isExpiredVhost = true;
                    }
                }

                if ($isExpiredVhost) {
                    // Remove expired vhost configuration
                    $this->removeExpiredVhostConfiguration($domain);
                }
                $this->vhost->setVhostOpensid($this->att->getSiteFolderOpensid(), $domain, $port_vhost, $port_domain);
                $this->vhost->setVhostApache($this->att->getSiteFolderOpensid(), $domain, $port_vhost, $port_domain);
            }
        } catch (\Exception $e) {
            $this->error("Error updating virtual host for {$domain}: " . $e->getMessage());
            // Fallback to traditional method
            $this->vhost->setVhostOpensid($this->att->getSiteFolderOpensid(), $domain, $port_vhost, $port_domain);
            $this->vhost->setVhostApache($this->att->getSiteFolderOpensid(), $domain, $port_vhost, $port_domain);
        }
    }

    /**
     * Update OpenLiteSpeed virtual host configuration
     *
     * @param string $kodedesa
     * @param string $domain
     * @return void
     */
    private function updateOpenLiteSpeedVirtualHost($kodedesa, $domain)
    {
        if (!$this->openLiteSpeed) {
            throw new \Exception("OpenLiteSpeed service is not available");
        }

        $documentRoot = $this->att->getSiteFolderOpensid();

        // Check if virtual host already exists
        if ($this->openLiteSpeedVHostExists($kodedesa)) {
            $this->info("Updating existing OpenLiteSpeed virtual host for {$domain}");

            // Remove existing virtual host first to ensure clean update
            $this->openLiteSpeed->removeVirtualHost($kodedesa, $domain);
        } else {
            $this->info("Creating new OpenLiteSpeed virtual host for {$domain}");
        }

        // Create/update virtual host
        $this->openLiteSpeed->createVirtualHost($kodedesa, $domain, $documentRoot);

        // Reload OpenLiteSpeed configuration
        $this->openLiteSpeed->reload();

        $this->info("OpenLiteSpeed virtual host updated successfully for {$domain}");
    }

    /**
     * Check if OpenLiteSpeed virtual host exists for the given kode desa
     *
     * @param string $kodedesa
     * @return bool
     */
    private function openLiteSpeedVHostExists($kodedesa)
    {
        try {
            $vhostName = "opensid_{$kodedesa}";
            $configPath = config('multitenant.openlitespeed.vhosts_dir') . "/{$vhostName}/vhconf.conf";
            return file_exists($configPath);
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Remove expired vhost configuration and SSL files
     *
     * @param string $domain
     * @return void
     */
    private function removeExpiredVhostConfiguration(string $domain): void
    {
        $confFilePath = $this->att->getApacheConfDir() . $domain . '.conf';
        $sslConfFilePath = $this->att->getApacheConfDir() . $domain . '-le-ssl.conf';

        try {
            // Backup existing configurations before removing
            if (file_exists($confFilePath)) {
                $backupPath = $confFilePath . '.expired-backup.' . date('Y-m-d_H-i-s');
                copy($confFilePath, $backupPath);
            }

            if (file_exists($sslConfFilePath)) {
                $backupPath = $sslConfFilePath . '.expired-backup.' . date('Y-m-d_H-i-s');
                copy($sslConfFilePath, $backupPath);
            }

            // Disable sites
            ProcessService::runProcess(
                ['sudo', 'a2dissite', "{$domain}.conf"],
                base_path(),
                "Disabling Apache site {$domain}.conf..."
            );

            ProcessService::runProcess(
                ['sudo', 'a2dissite', "{$domain}-le-ssl.conf"],
                base_path(),
                "Disabling Apache SSL site {$domain}-le-ssl.conf..."
            );

            // Remove configuration files
            if (file_exists($confFilePath)) {
                unlink($confFilePath);
            }

            if (file_exists($sslConfFilePath)) {
                unlink($sslConfFilePath);
            }

            $this->command->notifMessageNotice("Removed expired vhost configuration for domain: {$domain}");
        } catch (\Exception $e) {
            $this->command->notifMessageNotice("Warning: Failed to remove expired vhost configuration: " . $e->getMessage());
        }
    }
}
