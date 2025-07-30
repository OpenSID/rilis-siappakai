<?php

namespace App\Console\Commands;

use App\Models\Aplikasi;
use Illuminate\Console\Command;
use App\Services\ProcessService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Filesystem\Filesystem;
use App\Http\Controllers\Helpers\EnvController;
use App\Http\Controllers\Helpers\IndexController;
use App\Http\Controllers\Helpers\ConfigController;
use App\Http\Controllers\Helpers\AapanelController;
use App\Http\Controllers\Helpers\CommandController;
use App\Http\Controllers\Helpers\KoneksiController;
use App\Http\Controllers\Helpers\AttributeSiapPakaiController;

class BuildSite extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'siappakai:build-site {--kode_desa=} {--domain_opensid=} {--langganan_opensid=} {--tgl_akhir_premium=} {--token_premium=} {--kode_desa_default=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Membuat Web Pelanggan : OpenSID Premium, OpenSID API, Aplikasi PBB';

    private $aapanel;
    private $att;
    private $aplikasi;
    private $comm;
    private $files;
    private $filesConfig;
    private $filesEnv;
    private $filesIndex;
    private $koneksi;
    private $ip_source_code;
    private $server_panel;
    private $path;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->aapanel = new AapanelController();
        $this->att = new AttributeSiapPakaiController();
        $this->aplikasi = new Aplikasi();
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
        $urlApp = substr($domain, 0, 8) == "https://" ? $domain : "https://" . $domain;
        $database = nama_database_gabungan($langganan);
        $this->ip_source_code = $this->aplikasi::pengaturan_aplikasi()['ip_source_code'] ?? 'localhost';
        $this->server_panel = $this->aplikasi::pengaturan_aplikasi()['server_panel'];
        $this->path = 'multisite' . DIRECTORY_SEPARATOR . $kodedesa;

        /** premium, premium_1, premium_2, premium_3, premium_4, premium_5, umum */
        $this->att->setSiteFolderOpensid($this->att->getMultisiteFolder() . $kodedesa);
        $this->att->setIndexDesa($this->att->getSiteFolderOpensid() . DIRECTORY_SEPARATOR . 'index.php');
        if (!file_exists($this->att->getSiteFolderOpensid())) {
            File::copyDirectory($this->att->getTemplateFolder(), $this->att->getSiteFolderOpensid());

            $this->setConfigOpensid($kodedesa, $token_premium, $database);

            //create site opensid
            $this->createSiteOpenSid($kodedesa, $domain);
            $this->filesConfig->setAppKeyOpensid($this->att->getSiteFolderOpensid() . DIRECTORY_SEPARATOR . 'desa' . DIRECTORY_SEPARATOR . 'app_key');

            //create opensid api
            $this->createSiteApi($urlApp, $kodedesa, $kodedesa_default, $token_premium, $langganan);

            //create site pbb
            $this->createSitePbb($urlApp, $kodedesa, $kodedesa_default, $token_premium, $langganan);
        }
        $this->comm->setHtaccess(
            $this->aplikasi::pengaturan_aplikasi()['multiphp'],
            $this->att->getTemplateFolder() . DIRECTORY_SEPARATOR . '.htaccess',
            $this->att->getTemplateFolder() . DIRECTORY_SEPARATOR . 'htaccess.txt',
            $this->att->getSiteFolderOpensid() . DIRECTORY_SEPARATOR . '.htaccess'
        );
        $this->setFolderOpensid($langganan, $kodedesa);
        $this->permissionSite();
    }

    public function createSiteOpenSid($kodedesa, $domain)
    {
        if ($this->server_panel == "1") {
            /** key dan url web di aaPanel */
            $phpversion = $this->aplikasi::aapanel()['aapanel_php'];
            $this->aapanel->key = $this->aplikasi::aapanel()['aapanel_key'];
            $this->aapanel->url = $this->aplikasi::aapanel()['aapanel_ip'];
            $this->att->setFtpUser($kodedesa . 'ftp');
            $this->att->setFtpPass(base64_encode(random_bytes(12)));

            /** buat site aaPanel
             * domain, path, desc, type_id, type, phpversion, port,
             * ftp, ftpuser, ftppass, db, dbuser, dbpass, setssl, forcessl
             */
            $this->aapanel->addSite(
                $domain,
                $this->path,
                str_replace('.', '_', $domain),
                0,
                'php',
                $phpversion,
                '80',
                null,
                null,
                null,
                null,
                null,
                null,
                1,
                1
            );

            // create database
            $this->aapanel->AddDatabase($kodedesa);

            // create FTP
            $this->aapanel->addFtpUser($this->att->getFtpUser(), $this->att->getFtpPass(), $this->path);

            // setDirectory disable anti xss
            $this->aapanel->userDir($this->path);
        } else {
            // create database
            $this->createDatabase($kodedesa);

            // vhost dan ssl
            $this->setVhostOpensid($domain);
            $this->setVhostApache($domain);
        }
        ProcessService::aturKepemilikanDirektori(config('siappakai.root.folder'));
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

    public function permissionSite()
    {
        if ($this->server_panel == "1") {
            $this->aapanel->permissionDirectory($this->path);
        } else {
            $this->comm->chownCommand($this->att->getSiteFolderOpensid());
            $this->comm->chmodDirectoryDesa($this->att->getSiteFolderOpensid());
        }
    }

    private function setConfigOpensid($kodedesa, $token_premium, $database)
    {
        // config .php
        $this->filesConfig->configDesaBaru(
            $kodedesa,
            $token_premium,
            $this->att->getServerLayanan(),
            $this->att->getConfigSiteFolder() . DIRECTORY_SEPARATOR . 'config.php',      //configSite
            $this->att->getConfigTemplateFolder() . DIRECTORY_SEPARATOR . 'config.php'   //configMaster
        );

        // database .php
        $this->filesConfig->configDatabaseBaru(
            $kodedesa,
            $database,
            $this->att->getHost(),
            $this->att->getConfigSiteFolder() . DIRECTORY_SEPARATOR . 'database.php',      //configSite
            $this->att->getConfigTemplateFolder() . DIRECTORY_SEPARATOR . 'database.php'   //configMaster
        );
    }

    private function createDatabase($kodedesa)
    {
        $database = $this->koneksi->cekDatabase($kodedesa);

        if ($database == false) {
            DB::statement("CREATE DATABASE db_$kodedesa");
            DB::statement("CREATE USER 'user_$kodedesa'@'$this->ip_source_code' IDENTIFIED BY 'pass_$kodedesa' ");
            DB::statement("GRANT ALL PRIVILEGES ON db_$kodedesa.* TO 'user_$kodedesa'@'$this->ip_source_code' WITH GRANT OPTION");
            DB::statement("FLUSH PRIVILEGES");
        }
    }

    private function setVhostOpensid($domain)
    {
        $apacheTemplate = $this->files->get($this->att->getTemplateFolder() . DIRECTORY_SEPARATOR . 'vhost' . DIRECTORY_SEPARATOR . 'apache.conf');
        $content = str_replace(['{$domain}', '{$domainLog}', '{$documentRoot}', '{$documentDirectory}'], [$domain, str_replace('.', '', $domain), $this->att->getSiteFolderOpensid(), $this->att->getSiteFolderOpensid()], $apacheTemplate);
        $this->files->replace($this->att->getSiteFolderOpensid() . DIRECTORY_SEPARATOR . 'vhost' . DIRECTORY_SEPARATOR . 'apache.conf', $content);

        $apacheTemplate = $this->files->get($this->att->getTemplateFolder() . DIRECTORY_SEPARATOR . 'vhost' . DIRECTORY_SEPARATOR . 'nginx');
        $content = str_replace(['{$domain}', '{$domainLog}', '{$documentRoot}', '{$documentDirectory}'], [$domain, str_replace('.', '', $domain), $this->att->getSiteFolderOpensid(), $this->att->getSiteFolderOpensid()], $apacheTemplate);
        $this->files->replace($this->att->getSiteFolderOpensid() . DIRECTORY_SEPARATOR . 'vhost' . DIRECTORY_SEPARATOR . 'nginx', $content);
    }

    private function setVhostApache($domain)
    {
        $originalConf = $this->att->getSiteFolderOpensid() . DIRECTORY_SEPARATOR . 'vhost' . DIRECTORY_SEPARATOR . 'apache.conf';
        $this->comm->copyFile($originalConf, $this->att->getApacheConfDir() . $domain . '.conf');
        exec("sudo a2ensite $domain.conf");

        // buat ssl
        $this->comm->certbotSsl($domain);

        exec("sudo service apache2 restart");
    }

    /** ========================= Aplikasi PBB ==================================== */
    private function createSitePbb($urlApp, $kodedesa, $kodedesa_default, $token_premium, $langganan)
    {
        /** pbb_desa, pbb_desa_1, pbb_desa_2, pbb_desa_3, pbb_desa_4, pbb_desa_5 */
        $this->att->setSiteFolderPbb($this->att->getMultisiteFolder() . $kodedesa . DIRECTORY_SEPARATOR . 'pbb-app');
        $this->att->setIndexPbb($this->att->getSiteFolderPbb() . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'index.php');
        if (!file_exists($this->att->getSiteFolderPbb())) {
            File::copyDirectory($this->att->getTemplateFolderPbb(), $this->att->getSiteFolderPbb());
            $this->comm->makeDirectory($this->att->getSiteFolderPbb() . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'import');

            if ($this->server_panel == "1") {
                $this->aapanel->permissionDirectory($this->att->getSiteFolderPbb());
                $destinationPanel = $this->att->getServerDbAapanel() . 'db_' . $kodedesa . '_pbb';
                $this->comm->moveFile($this->att->getSiteFolderPbb() . DIRECTORY_SEPARATOR . 'db_pbb', $destinationPanel);
                $this->comm->chownCommandNew($destinationPanel, 'mysql');
                $this->comm->chmodDirectory($destinationPanel, 700);
                $this->setPrivilegeUserPbb($kodedesa, $kodedesa . "_pbb");
            } else {
                $this->comm->chownCommand($this->att->getSiteFolderPbb());
                $this->comm->removeDirectory($this->att->getSiteFolderPbb() . DIRECTORY_SEPARATOR . 'db_pbb');
                $this->createDatabasePbb($kodedesa);
            }

            $this->filesEnv->envPbb(
                $this->att->getHost(),
                $this->att->getTemplateFolderPbb(),
                $this->att->getServerLayanan(),
                $this->att->getSiteFolderPbb(),
                $kodedesa_default,
                $kodedesa,
                $urlApp . DIRECTORY_SEPARATOR . 'pbb',
                $token_premium
            );
        }
        $this->comm->setHtaccess(
            $this->aplikasi::pengaturan_aplikasi()['multiphp'],
            $this->att->getTemplateFolderPbb() . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . '.htaccess',
            $this->att->getRootFolder() . 'master-pbb' . DIRECTORY_SEPARATOR . 'pbb_desa' . DIRECTORY_SEPARATOR . '.htaccess',
            $this->att->getSiteFolderPbb() . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . '.htaccess'
        );
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

    private function createDatabasePbb($kodedesa)
    {
        $database = $this->koneksi->cekDatabasePbb($kodedesa);

        if ($database == false) {
            $namadbuser = $kodedesa . "_pbb";
            DB::statement("CREATE DATABASE db_" . $namadbuser);
            $this->setPrivilegeUserPbb($kodedesa, $namadbuser);
        }
    }

    private function setPrivilegeUserPbb($kodedesa, $namadbuser)
    {
        DB::statement("GRANT ALL PRIVILEGES ON db_$namadbuser.* TO 'user_$kodedesa'@'$this->ip_source_code' WITH GRANT OPTION");
        DB::statement("FLUSH PRIVILEGES");
    }

    /** ========================= Aplikasi Api ==================================== */
    private function createSiteApi($urlApp, $kodedesa, $kodedesa_default, $token_premium, $langganan)
    {
        /** opensid-api, opensid-api_1, opensid-api_2, opensid-api_3, opensid-api_4, opensid-api_5 */
        $this->att->setSiteFolderApi($this->att->getMultisiteFolder() . $kodedesa . DIRECTORY_SEPARATOR . 'api-app');
        $this->att->setIndexApi($this->att->getSiteFolderApi() . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'index.php');
        if (!file_exists($this->att->getSiteFolderApi())) {
            File::copyDirectory($this->att->getTemplateFolderApi(), $this->att->getSiteFolderApi());

            $this->comm->chownCommandPanel($this->att->getSiteFolderApi());

            $this->filesEnv->envApi(
                $this->att->getHost(),
                $this->att->getTemplateFolderApi(),
                $this->att->getServerLayanan(),
                $this->att->getSiteFolderApi(),
                $kodedesa_default,
                $kodedesa,
                $token_premium,
                null,
                null,
                null,
                null,
                $urlApp,
                $this->att->getFtpUser(),
                $this->att->getFtpPass()
            );
        }
        $this->comm->setHtaccess(
            $this->aplikasi::pengaturan_aplikasi()['multiphp'],
            $this->att->getTemplateFolderApi() . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . '.htaccess',
            $this->att->getRootFolder() . 'master-api' . DIRECTORY_SEPARATOR . 'opensid-api' . DIRECTORY_SEPARATOR . '.htaccess',
            $this->att->getSiteFolderApi() . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . '.htaccess'
        );
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
