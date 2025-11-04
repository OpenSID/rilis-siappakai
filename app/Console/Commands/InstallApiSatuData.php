<?php

namespace App\Console\Commands;

use App\Enums\RepositoryEnum;
use App\Http\Controllers\Helpers\AttributeSiapPakaiController;
use App\Http\Controllers\Helpers\CommandController;
use App\Http\Controllers\Helpers\EnvController;
use App\Services\DatabaseService;
use App\Services\FileService;
use App\Services\GitService;
use App\Services\ProcessService;
use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\File;

class InstallApiSatuData extends Command
{
    // Nama dan tanda tangan dari perintah konsol
    protected $signature = 'siappakai:install-api-satudata {--domain=} {--vhost=} {--domain_openkab=}';

    // Deskripsi perintah konsol
    protected $description = 'Instal Api SatuData';

    private $att;
    private $files;
    private $filesEnv;
    private $database = "api_satudata";
    private $command;

    // Konstruktor untuk menginisialisasi objek yang diperlukan
    public function __construct(
        AttributeSiapPakaiController $att,
        Filesystem $files,
        EnvController $filesEnv,
        CommandController $command)
    {
        parent::__construct();
        $this->att = $att;
        $this->files = $files;
        $this->filesEnv = $filesEnv;
        $this->command = $command;
    }

    // Fungsi utama yang akan dieksekusi saat perintah dijalankan
    public function handle()
    {
        $domain = $this->validateDomain($this->option('domain'));
        $domain_openkab = $this->validateDomain($this->option('domain_openkab'));
        $vhost = $this->option('vhost');
        $db_host = $this->att->getHost();
        $db_username = $this->att->getUsername();
        $db_password = $this->att->getPassword();
        $db_database = $this->database;
        $urlApp = formatUrl($domain);
        $url_openkab = formatUrl($domain_openkab);

        if ($this->att->getSiteFolder()) {
            GitService::cloneRepository(RepositoryEnum::OPENKAB_API, config('siappakai.root.folder'));
            $this->createDatabase($db_database, $db_host);

            $this->att->setSiteFolderApiSatuData(config('siappakai.root.folder') . 'api-gabungan');

            $this->fileEnv($db_database, $db_username, $db_password, $urlApp, $url_openkab);
            $this->setupVhost($domain, $vhost);
            $this->runMigrationsAndStorage();
            $this->setFolderPermissions();
        }

        ProcessService::aturKepemilikanDirektori($this->att->getSiteFolderApiSatuData());
    }

    private function fileEnv($db_database, $db_username, $db_password, $urlApp, $url_openkab)
    {
        if(file_exists($this->att->getSiteFolderApiSatuData())){
            $envTemplate = $this->att->getTemplateFolderApiSatuData() . DIRECTORY_SEPARATOR . '.env.example';
            $target = $this->att->getSiteFolderApiSatuData() . DIRECTORY_SEPARATOR . '.env';
            $fileservices = new FileService();
            $fileservices->processTemplate(
                $envTemplate,
                $target,
                [
                    '{$app_url}' => $urlApp,
                    '{$db_host}' => $this->att->getHost(),
                    '{$db_api_satudata}' => $db_database,
                    '{$db_username}' => $db_username,
                    '{$db_password}' => $db_password,
                    '{$db_database_gabungan}' => 'db_' . nama_database_gabungan('premium'),
                    '{$db_username_gabungan}' => $db_username,
                    '{$db_password_gabungan}' => $db_password,
                    '{$url_pantau}' => config('siappakai.pantau.api_pantau'),
                    '{$token_pantau}' => config('siappakai.pantau.token_pantau'),
                    '{$url_openkab}' => $url_openkab,

                ]
            );
        }
    }

    // Validasi domain yang diberikan
    private function validateDomain($domain)
    {
        return validasi_domain($domain);
    }

    // Setup konfigurasi vhost
    private function setupVhost($domain, $vhost)
    {
        $this->setVhostApiSatuData($domain);
        $this->setVhostApache($domain, $vhost);
    }

    // Membuat database jika belum ada
    private function createDatabase($db_database, $db_host)
    {
        $databaseservice = new DatabaseService($db_host);
        $databaseservice->createDatabase($db_database);
        $databaseservice->createUser($db_database);
    }

    // Menjalankan migrasi dan setup storage
    private function runMigrationsAndStorage()
    {
        $siteFolder = $this->att->getSiteFolderApiSatuData();
        $this->command->chownCommand($siteFolder);
        $this->command->composerInstall($siteFolder);
        $this->command->keyGenerateCommand($siteFolder);
        $this->command->migrateSeedForce($siteFolder);
        $this->command->storageLink($siteFolder);
        $this->command->chmodDirectoryCommand($siteFolder . DIRECTORY_SEPARATOR . 'storage');
        $this->command->chmodDirectoryCommand($siteFolder . DIRECTORY_SEPARATOR . 'bootstrap');
        $this->command->indexCommand($siteFolder . DIRECTORY_SEPARATOR . 'public');
    }

    // Mengatur izin untuk folder storage
    private function setFolderPermissions()
    {
        $siteFolder = $this->att->getSiteFolderApiSatuData();
        $this->command->chmodDirectoryCommandRead($siteFolder . DIRECTORY_SEPARATOR . 'storage');
        $this->command->commitCommand('first install', $siteFolder);
        $this->command->notifMessage('Berhasil Install Api Satu Data');
    }

    // Setup konfigurasi vhost untuk Openkab
    private function setVhostApiSatuData($domain)
    {
        $vhostTemplate = $this->att->getTemplateFolder() . DIRECTORY_SEPARATOR . 'vhost';
        $vhostOpenKab = $this->att->getSiteFolderApiSatuData() . DIRECTORY_SEPARATOR . 'vhost';
        $documentRoot = $this->att->getSiteFolderApiSatuData() . DIRECTORY_SEPARATOR . 'public';
        $documentDirectory = $this->att->getSiteFolderApiSatuData() . DIRECTORY_SEPARATOR . 'public';

        if (file_exists($vhostTemplate) && $domain != '') {
            File::copyDirectory($vhostTemplate, $vhostOpenKab);
            $this->replaceVhostPlaceholders($vhostTemplate, $vhostOpenKab, $domain, $documentRoot, $documentDirectory);
        }
    }

    // Mengganti placeholder dalam file konfigurasi vhost
    private function replaceVhostPlaceholders($vhostTemplate, $vhostOpenKab, $domain, $documentRoot, $documentDirectory)
    {
        $apacheTemplate = $this->files->get($vhostTemplate . DIRECTORY_SEPARATOR . 'apache.conf');
        $content = str_replace(['{$domain}', '{$domainLog}', '{$documentRoot}', '{$documentDirectory}'], [$domain, str_replace('.', '', $domain), $documentRoot, $documentDirectory], $apacheTemplate);
        $this->files->replace($vhostOpenKab . DIRECTORY_SEPARATOR . 'apache.conf', $content);

        $nginxTemplate = $this->files->get($vhostTemplate . DIRECTORY_SEPARATOR . 'nginx');
        $content = str_replace(['{$domain}', '{$domainLog}', '{$documentRoot}', '{$documentDirectory}'], [$domain, str_replace('.', '', $domain), $documentRoot, $documentDirectory], $nginxTemplate);
        $this->files->replace($vhostOpenKab . DIRECTORY_SEPARATOR . 'nginx', $content);
    }

    // Setup konfigurasi vhost untuk Apache
    private function setVhostApache($domain, $vhost)
    {
        $vhostOpenkab = $this->att->getSiteFolderApiSatuData() . DIRECTORY_SEPARATOR . 'vhost';

        if (file_exists($vhostOpenkab) && $vhost != 'proxy') {
            $originalConf = $vhostOpenkab . DIRECTORY_SEPARATOR . 'apache.conf';

            $this->command->chownCommand($this->att->getApacheConfDir());
            $this->command->copyFile($originalConf, $this->att->getApacheConfDir() . $domain . '.conf');
            exec("sudo a2ensite $domain.conf");

            // Membuat SSL
            $this->command->certbotSsl($domain);

            // Restart Apache
            $this->command->restartApache();
        }
    }
}
