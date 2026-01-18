<?php

namespace App\Console\Commands;

use App\Enums\Level;
use App\Models\Aplikasi;
use Illuminate\Console\Command;
use App\Services\ConsoleService;
use App\Services\ProcessService;
use App\Services\DatabaseService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Filesystem\Filesystem;
use Symfony\Component\Process\Process;
use App\Http\Controllers\Helpers\EnvController;
use App\Http\Controllers\Helpers\IndexController;
use App\Http\Controllers\Helpers\ConfigController;
use App\Http\Controllers\Helpers\CommandController;
use App\Http\Controllers\Helpers\KoneksiController;
use App\Http\Controllers\Helpers\AttributeSiapPakaiController;

class BuildSiteOpenKab extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'siappakai:build-site-openkab {--kode_desa=} {--domain_opensid=} {--langganan_opensid=} {--tgl_akhir_premium=} {--token_premium=} {--kode_desa_default=} {--port_domain=} {--port_vhost=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Membuat Web Pelanggan OpenKab: OpenSID Premium, OpenSID API, Aplikasi PBB';

    private $att;
    private $comm;
    private $files;
    private $filesConfig;
    private $filesEnv;
    private $filesIndex;
    private $koneksi;
    private $ip_source_code;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->att = new AttributeSiapPakaiController();
        $this->comm = new CommandController();
        $this->files = new Filesystem();
        $this->filesConfig = new ConfigController();
        $this->filesEnv = new EnvController();
        $this->filesIndex = new IndexController();
        $this->koneksi = new KoneksiController();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $kodedesa = $this->option('kode_desa');
        $domain = $this->option('domain_opensid');
        $langganan = $this->option('langganan_opensid');
        $token_premium = $this->option('token_premium');
        $kodedesa_default = $this->option('kode_desa_default');
        $port_domain = $this->option('port_domain');
        $port_vhost = $this->option('port_vhost');
        $urlApp = substr($domain, 0, 8) == "https://" ? $domain : "https://" . $domain;
        $database = nama_database_gabungan($langganan);
        $wilayah = Level::Kabupaten == 2 ? str_replace('.', '', Aplikasi::wilayah()['kode_kabupaten']) : str_replace('.', '', Aplikasi::wilayah()['kode_wilayah']); // dilanjutkan di issue berikutnya
        $this->ip_source_code = env('OPENKAB') == 'true' ? Aplikasi::pengaturan_aplikasi()['ip_source_code'] : 'localhost';

        /** premium, premium_1, premium_2, premium_3, premium_4, premium_5, umum */
        $this->att->setSiteFolderOpensid($this->att->getMultisiteFolder() . $kodedesa);
        $this->att->setIndexDesa($this->att->getSiteFolderOpensid() . DIRECTORY_SEPARATOR . 'index.php');

        if (!file_exists($this->att->getSiteFolderOpensid())) {
            File::copyDirectory($this->att->getTemplateFolder(), $this->att->getSiteFolderOpensid());

            $this->setConfigOpensid($kodedesa,  $token_premium, $database, $wilayah);
            $this->createDatabase($database, $wilayah);

            if ($domain != '-') {
                $this->setVhostOpensid($domain, $port_vhost, $port_domain);
                $this->setVhostApache($domain, $port_vhost, $port_domain);
            }

            $this->setFolderOpensid($langganan, $kodedesa);
            //$this->comm->chownCommand($this->att->getSiteFolderOpensid());
            $this->comm->chmodDirectoryDesa($this->att->getSiteFolderOpensid());

            if ($token_premium != "") {
                $this->setAppKeyOpensid();
                $this->daftarkan_dbGabungan($database, $wilayah);
            }

            if ($langganan == 'premium') {
                //create site aplikasi pbb
                $this->createSitePbb($urlApp, $kodedesa, $kodedesa_default, $token_premium, $langganan, $database);

                //create opensid api
                $this->createSiteApi($urlApp, $kodedesa, $kodedesa_default, $token_premium, $langganan, $database);
            }


            //notif log
            ConsoleService::info('Berhasil tambahkan directory ' . $kodedesa);
        }
         ProcessService::aturKepemilikanDirektori($this->att->getSiteFolderOpensid());
    }

    private function daftarkan_dbGabungan($database, $wilayah)
    {
        // jika config pada database opensid >= 1, maka itu database gabungan
        $dbOpensid = $this->koneksi->getObjDatabase($database, $wilayah);
        if ($dbOpensid) {
            $totalDesa = $dbOpensid->query('select * from config')->num_rows;
            $this->comm->notifMessageNotice('totalDesa ' . $totalDesa);
            if ($totalDesa >= 1) {
                $this->comm->notifMessageNotice('jalankan generate desa baru');
                // buat desa baru pada config
                $this->comm->indexDesaBaru($this->att->getSiteFolderOpensid());
            }
        }
    }

    /**
     * Mengatur konfigurasi OpenSID untuk desa baru.
     *
     * @param string $kodedesa Kode desa yang akan dikonfigurasi.
     * @param string $langganan Jenis langganan, default 'umum'.
     * @param string $token_premium Token premium untuk desa.
     * @param string $openkab Nama database gabungan.
     */
    private function setConfigOpensid($kodedesa, $token_premium, $database, $wilayah)
    {
        // config .php
        $this->filesConfig->configDesaBaru(
            $kodedesa,
            $token_premium,
            $this->att->getServerLayanan(),
            $this->att->getConfigSiteFolder() . DIRECTORY_SEPARATOR . 'config.php',      //configSite
            $this->att->getConfigTemplateFolder() . DIRECTORY_SEPARATOR . 'config.php',  //configMaster
        );

        // database .php
        $this->filesConfig->configDatabaseBaru(
            $wilayah,
            $database,
            $this->att->getHost(),
            $this->att->getConfigSiteFolder() . DIRECTORY_SEPARATOR . 'database.php',      //configSite
            $this->att->getConfigTemplateFolder() . DIRECTORY_SEPARATOR . 'database.php',  //configMaster
        );

        // jalankan symlink tema bawaan
        $tema_bawaan = Aplikasi::pengaturan_aplikasi()['tema_bawaan'];
        $process = new Process(['php', 'artisan', 'siappakai:install-tema-bawaan', "--tema={$tema_bawaan}", "--kode_desa={$kodedesa}"], base_path());
        $process->setTimeout(null);
        $process->run();
    }

    private function setAppKeyOpensid()
    {
        $appKey = 'base64:' . base64_encode(random_bytes(32));
        $appKeyPath = $this->att->getSiteFolderOpensid() . DIRECTORY_SEPARATOR . 'desa' . DIRECTORY_SEPARATOR . 'app_key';
        if (!$this->files->exists($appKeyPath)) {
            $this->files->put($appKeyPath, $appKey);
            $this->comm->chownFileCommand($appKeyPath);
            $this->comm->chmodFileCommand($appKeyPath);
        }
    }

    private function createDatabase($database, $wilayah)
    {
        $databaseService = new DatabaseService($this->ip_source_code);
        $databaseService->createDatabase("db_$database");
        $databaseService->createUser("db_$database", $wilayah);
    }

    private function setVhostOpensid($domain, $port_vhost, $port_domain)
    {
        if ($port_vhost == 'proxy') {
            $apacheTemplate = $this->files->get($this->att->getTemplateFolder() . DIRECTORY_SEPARATOR . 'vhost' . DIRECTORY_SEPARATOR . 'proxy.conf');
            $content = str_replace(['{$port}', '{$documentRoot}', '{$documentDirectory}'], [$port_domain, $this->att->getSiteFolderOpensid(), $this->att->getSiteFolderOpensid()], $apacheTemplate);
            $this->files->replace($this->att->getSiteFolderOpensid() . DIRECTORY_SEPARATOR . 'vhost' . DIRECTORY_SEPARATOR . 'proxy.conf', $content);
        } else {
            $apacheTemplate = $this->files->get($this->att->getTemplateFolder() . DIRECTORY_SEPARATOR . 'vhost' . DIRECTORY_SEPARATOR . 'apache.conf');
            $content = str_replace(['{$domain}', '{$domainLog}', '{$documentRoot}', '{$documentDirectory}'], [$domain, str_replace('.', '', $domain), $this->att->getSiteFolderOpensid(), $this->att->getSiteFolderOpensid()], $apacheTemplate);
            $this->files->replace($this->att->getSiteFolderOpensid() . DIRECTORY_SEPARATOR . 'vhost' . DIRECTORY_SEPARATOR . 'apache.conf', $content);
        }
    }

    private function setVhostApache($domain, $port_vhost, $port_domain)
    {
        if ($port_vhost == 'proxy') {
            $originalConf = $this->att->getSiteFolderOpensid() . DIRECTORY_SEPARATOR . 'vhost' . DIRECTORY_SEPARATOR . 'proxy.conf';
            $this->comm->copyFile($originalConf, $this->att->getApacheConfDir() . $domain . '.conf');
            $this->comm->symlinkDirectory($this->att->getApacheConfDir() . $domain . '.conf', $this->att->getApacheConfSym() . $domain . '.conf');
        } else {
            $originalConf = $this->att->getSiteFolderOpensid() . DIRECTORY_SEPARATOR . 'vhost' . DIRECTORY_SEPARATOR . 'apache.conf';
            $command = 'sudo cp ' . $originalConf . ' ' . $this->att->getApacheConfDir() . $domain . '.conf';
            exec($command);
            exec("sudo a2ensite $domain.conf");

            // buat ssl
            $ssl = new Process(['sudo', 'certbot', 'run', '-n', '--apache', '--agree-tos', '-d', $domain]);
            $ssl->setTimeout(null);
            $ssl->run();
        }

        exec("sudo ufw allow " . $port_domain);
        exec("sudo service apache2 restart");
    }

    private function setFolderOpensid($langganan, $kodedesa)
    {
        $this->filesIndex->indexPhpOpensid(
            $this->att->getRootFolder() . 'master-opensid' . DIRECTORY_SEPARATOR . $langganan,
            $this->att->getMultisiteFolder() . $kodedesa,
            $this->att->getIndexTemplate(),
            $this->att->getIndexDesa()
        );

        // eksekusi index.php di opensid dibackgroung untuk menjalankan migrasi dan pembuatan symlink di
        $this->comm->migratePremium($this->att->getSiteFolderOpensid());
    }

    /** ========================= Aplikasi PBB ==================================== */
    private function createSitePbb($urlApp, $kodedesa, $kodedesa_default, $token_premium, $langganan, $openkab)
    {
        /** pbb_desa, pbb_desa_1, pbb_desa_2, pbb_desa_3, pbb_desa_4, pbb_desa_5 */
        $this->att->setSiteFolderPbb($this->att->getMultisiteFolder() . $kodedesa . DIRECTORY_SEPARATOR . 'pbb-app');
        $this->att->setIndexPbb($this->att->getSiteFolderPbb() . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'index.php');
        if (!file_exists($this->att->getSiteFolderPbb())) {
            File::copyDirectory($this->att->getTemplateFolderPbb(), $this->att->getSiteFolderPbb());

            $this->comm->chownCommand($this->att->getSiteFolderPbb());
            $this->createDatabasePbb($kodedesa, $openkab);

            $this->filesEnv->envPbb(
                $this->att->getHost(),
                $this->att->getTemplateFolderPbb(),
                $this->att->getServerLayanan(),
                $this->att->getSiteFolderPbb(),
                $kodedesa_default,
                $kodedesa,
                $urlApp . DIRECTORY_SEPARATOR . 'pbb',
                $token_premium,
            );
        }
        $this->setFolderPbb($langganan, $kodedesa);

        $this->comm->indexCommand($this->att->getSiteFolderPbb() . DIRECTORY_SEPARATOR . 'public');
        $this->comm->keyGenerateCommand($this->att->getSiteFolderPbb());
        $this->comm->migrateSeedForce($this->att->getSiteFolderPbb());
        $this->comm->storageLink($this->att->getSiteFolderPbb());
    }

    private function setFolderPbb($langganan, $kodedesa)
    {
        $pbbFolder = '';
        if ($langganan == 'premium') {
            $pbbFolder = 'pbb_desa';
        }

        $this->filesIndex->indexPhpPbb(
            $pbbFolder,
            $this->att->getRootFolder() . 'master-pbb' . DIRECTORY_SEPARATOR,               // pbbFolderFrom
            $this->att->getMultisiteFolder() . $kodedesa . DIRECTORY_SEPARATOR . 'pbb-app', // pbbFolderTo
            $this->att->getIndexTemplatePbb(),
            $this->att->getIndexPbb()
        );
    }

    private function createDatabasePbb($kodedesa, $openkab)
    {
        $database = $this->koneksi->cekDatabasePbb($kodedesa);

        if ($database == false) {
            $namadbuser = $kodedesa . "_pbb";
            DB::statement("CREATE DATABASE db_" . $namadbuser);

            if ($kodedesa != $openkab) {
                DB::statement("CREATE USER 'user_$kodedesa'@'$this->ip_source_code' IDENTIFIED BY 'pass_$kodedesa' ");
            }

            DB::statement("GRANT ALL PRIVILEGES ON db_$namadbuser.* TO 'user_$kodedesa'@'$this->ip_source_code' WITH GRANT OPTION");
            DB::statement("FLUSH PRIVILEGES");
        }
    }

    /** ========================= Aplikasi Api ==================================== */
    private function createSiteApi($urlApp, $kodedesa, $kodedesa_default, $token_premium, $langganan, $openkab)
    {
        /** opensid-api, opensid-api_1, opensid-api_2, opensid-api_3, opensid-api_4, opensid-api_5 */
        $this->att->setSiteFolderApi($this->att->getMultisiteFolder() . $kodedesa . DIRECTORY_SEPARATOR . 'api-app');
        $this->att->setIndexApi($this->att->getSiteFolderApi() . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'index.php');
        if (!file_exists($this->att->getSiteFolderApi())) {
            File::copyDirectory($this->att->getTemplateFolderApi(), $this->att->getSiteFolderApi());

            $this->comm->chownCommand($this->att->getSiteFolderApi());

            $this->filesEnv->envApi(
                $this->att->getHost(),
                $this->att->getTemplateFolderApi(),
                $this->att->getServerLayanan(),
                $this->att->getSiteFolderApi(),
                $kodedesa_default,
                $openkab,
                $token_premium,
                null,
                null,
                null,
                null,
                $urlApp,
                $this->att->getFtpUser(),
                $this->att->getFtpPass(),
            );
        }
        $this->setFolderApi($langganan, $kodedesa);
        $this->comm->indexCommand($this->att->getSiteFolderApi() . DIRECTORY_SEPARATOR . 'public');
        $this->comm->keyGenerateCommand($this->att->getSiteFolderApi());
        $this->comm->keyJwtSecretCommand($this->att->getSiteFolderApi());
    }

    private function setFolderApi($langganan, $kodedesa)
    {
        $apiFolder = '';
        if ($langganan == 'premium') {
            $apiFolder = 'opensid-api';
        }

        $this->filesIndex->indexPhpApi(
            $apiFolder,
            $this->att->getRootFolder() . 'master-api' . DIRECTORY_SEPARATOR,               //apiFolderFrom,
            $this->att->getMultisiteFolder() . $kodedesa . DIRECTORY_SEPARATOR . 'api-app', //apiFolderTo,
            $this->att->getIndexTemplateApi(),
            $this->att->getIndexApi()
        );
    }
}
