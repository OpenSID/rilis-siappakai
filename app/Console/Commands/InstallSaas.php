<?php

namespace App\Console\Commands;

use App\Http\Controllers\Helpers\AttributeSiapPakaiController;
use App\Http\Controllers\Helpers\CommandController;
use App\Http\Controllers\Helpers\EnvController;
use App\Http\Controllers\Helpers\KoneksiController;
use App\Services\DatabaseService;
use App\Services\ProcessService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Filesystem\Filesystem;

class InstallSaas extends Command
{
    // Nama dan tanda tangan dari perintah konsol
    protected $signature = 'siappakai:install-siappakai {--domain=} {--vhost=} {--permission=}';

    // Deskripsi perintah konsol
    protected $description = 'Instal Siapakai, OpenSID Premium, OpenSID Umum, OpenSID API, dan Aplikasi PBB ';

    private $att;
    private $files;
    private $koneksi;
    private $database = "saas_dashboard";
    private $env;
    private $command;

    // Konstruktor untuk menginisialisasi objek yang diperlukan
    public function __construct()
    {
        parent::__construct();
        $this->att = new AttributeSiapPakaiController();
        $this->files = new Filesystem();
        $this->koneksi = new KoneksiController();
        $this->env = new EnvController();
        $this->command = new CommandController();
    }

    // Fungsi utama yang akan dieksekusi saat perintah dijalankan
    public function handle()
    {
        $domain = $this->validateDomain($this->option('domain'));
        $vhost = $this->option('vhost');
        $permission = $this->option('permission');
        $db_host = $this->att->getHost();
        $db_username = $this->att->getUsername();
        $db_password = $this->att->getPassword();
        $db_database = $this->database;
        $urlApp = formatUrl($domain);

        if ($this->att->getSiteFolder()) {
            $this->setupVhost($domain, $vhost);
            $this->createDatabase($db_database, $db_host);
            $this->configureEnv($db_database, $db_username, $db_password, $db_host, $urlApp);
            $this->runMigrationsAndStorage();
            $this->command->chownCommandNew($this->att->getSiteFolder(), $permission);
            $this->setFolderPermissions();
        }

        ProcessService::aturKepemilikanDirektori(config('siappakai.root.folder'));
    }

    // Validasi domain yang diberikan
    private function validateDomain($domain)
    {
        return validasi_domain($domain);
    }

    // Setup konfigurasi vhost
    private function setupVhost($domain, $vhost)
    {
        $this->setVhostSiapPakai($domain);
        $this->setVhostApache($domain, $vhost);
    }

    // Membuat database jika belum ada
    private function createDatabase($db_database, $db_host)
    {
        $databaseservice = new DatabaseService($db_host);
        $databaseservice->createDatabase($db_database);
        $databaseservice->createUser($db_database);
    }

    // Konfigurasi variabel lingkungan
    private function configureEnv($db_database, $db_username, $db_password, $db_host, $urlApp)
    {
        $envExample = $this->att->getSiteFolder() . DIRECTORY_SEPARATOR . 'master-template' . DIRECTORY_SEPARATOR . '.env.example';
        $env = $this->att->getSiteFolder() . DIRECTORY_SEPARATOR . '.env';
        $this->env->envSiapPakai($envExample, $env, $db_host, $db_database, $db_username, $db_password, $this->att->getRootFolder(), $this->att->getTokenGithub(), $this->att->getFtpUser(), $this->att->getFtpPass(), $this->att->getAppEnv(), $this->att->getAppDebug(), $this->att->getOpenKab(), $urlApp);
    }

    // Menjalankan migrasi dan setup storage
    private function runMigrationsAndStorage()
    {
        $siteFolder = $this->att->getSiteFolder();
        $this->command->migrateSeedForce($siteFolder);
        $this->command->storageLink($siteFolder);
        $this->command->indexCommand($siteFolder . DIRECTORY_SEPARATOR . 'public');
    }

    // Mengatur izin untuk folder storage
    private function setFolderPermissions()
    {
        $siteFolder = $this->att->getSiteFolder();
        $this->command->chmodDirectoryCommandRead($siteFolder . DIRECTORY_SEPARATOR . 'storage');
        $this->command->commitCommand('first install', $siteFolder);
        $this->command->notifMessage('Berhasil Install Dasbor SiapPakai');
    }

    // Setup konfigurasi vhost untuk SiapPakai
    private function setVhostSiapPakai($domain)
    {
        $vhostTemplate = $this->att->getTemplateFolder() . DIRECTORY_SEPARATOR . 'vhost';
        $vhostSiapPakai = $this->att->getSiteFolder() . DIRECTORY_SEPARATOR . 'vhost';
        $documentRoot = $this->att->getRootFolder() . 'public_html';
        $documentDirectory = rtrim($this->att->getRootFolder(), "/");

        if (file_exists($vhostTemplate) && $domain != '') {
            File::copyDirectory($vhostTemplate, $vhostSiapPakai);
            $this->replaceVhostPlaceholders($vhostTemplate, $vhostSiapPakai, $domain, $documentRoot, $documentDirectory);
        }
    }

    // Mengganti placeholder dalam file konfigurasi vhost
    private function replaceVhostPlaceholders($vhostTemplate, $vhostSiapPakai, $domain, $documentRoot, $documentDirectory)
    {
        $apacheTemplate = $this->files->get($vhostTemplate . DIRECTORY_SEPARATOR . 'apache.conf');
        $content = str_replace(['{$domain}', '{$domainLog}', '{$documentRoot}', '{$documentDirectory}'], [$domain, str_replace('.', '', $domain), $documentRoot, $documentDirectory], $apacheTemplate);
        $this->files->replace($vhostSiapPakai . DIRECTORY_SEPARATOR . 'apache.conf', $content);

        $nginxTemplate = $this->files->get($vhostTemplate . DIRECTORY_SEPARATOR . 'nginx');
        $content = str_replace(['{$domain}', '{$domainLog}', '{$documentRoot}', '{$documentDirectory}'], [$domain, str_replace('.', '', $domain), $documentRoot, $documentDirectory], $nginxTemplate);
        $this->files->replace($vhostSiapPakai . DIRECTORY_SEPARATOR . 'nginx', $content);
    }

    // Setup konfigurasi vhost untuk Apache
    private function setVhostApache($domain, $vhost)
    {
        $vhostSiapPakai = $this->att->getSiteFolder() . DIRECTORY_SEPARATOR . 'vhost';

        if (file_exists($vhostSiapPakai) && $vhost != 'proxy') {
            $originalConf = $vhostSiapPakai . DIRECTORY_SEPARATOR . 'apache.conf';
            $command = 'sudo cp ' . $originalConf . ' ' . $this->att->getApacheConfDir() . $domain . '.conf';
            exec($command);
            exec("sudo a2ensite $domain.conf");

            // Membuat SSL
            $this->command->certbotSsl($domain);

            // Restart Apache
            $this->command->restartApache();
        }
    }
}
