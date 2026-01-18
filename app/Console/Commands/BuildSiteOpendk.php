<?php

namespace App\Console\Commands;

use App\Models\Wilayah;
use App\Models\Aplikasi;
use App\Models\Pelanggan;
use App\Services\FileService;
use Illuminate\Console\Command;
use App\Services\OpenLiteSpeedService;
use App\Services\ProcessService;
use App\Services\DatabaseService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Filesystem\Filesystem;
use App\Http\Controllers\Helpers\EnvController;
use App\Http\Controllers\Helpers\IndexController;
use App\Http\Controllers\Helpers\AapanelController;
use App\Http\Controllers\Helpers\CommandController;
use App\Http\Controllers\Helpers\AttributeSiapPakaiController;

class BuildSiteOpendk extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'siappakai:build-site-opendk {--kode_kecamatan=} {--domain_opendk=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Membuat site opendk';

    private $aapanel;
    private $att;
    private $aplikasi;
    private $comm;
    private $files;
    private $filesEnv;
    private $filesIndex;
    private $ip_source_code;
    private $server_panel;
    private $path;
    private $fileService;
    private $openLiteSpeed;

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
        $this->filesEnv = new EnvController();
        $this->filesIndex = new IndexController();
        $this->fileService = new FileService();

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
        $kodekecamatan = $this->option('kode_kecamatan');
        // pastikan kode kecamatan bersih dari titik
        $kodekecamatan = str_replace('.', '', $kodekecamatan);
        $domain = $this->option('domain_opendk');
        $urlApp = substr($domain, 0, 8) == "https://" ? $domain : "https://" . $domain;
        $this->ip_source_code = $this->aplikasi::pengaturan_aplikasi()['ip_source_code'] ?? 'localhost';
        $this->server_panel = $this->aplikasi::pengaturan_aplikasi()['server_panel'];
        $this->path = 'multisite' . DIRECTORY_SEPARATOR . $kodekecamatan;

        $this->att->setSiteFolderOpendk($this->att->getMultisiteFolder() . $kodekecamatan);
        $this->att->setIndexOpendk($this->att->getSiteFolderOpendk() . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'index.php');
        if (!file_exists($this->att->getSiteFolderOpendk())) {
            File::copyDirectory($this->att->getTemplateFolderOpendk(), $this->att->getSiteFolderOpendk());
        }

        //create site opendk
        $this->createSiteOpendk($urlApp, $kodekecamatan, $domain);

        $this->updateData($kodekecamatan);
        $this->permissionSite();
    }

    public function createSiteOpendk($urlApp, $kodekecamatan, $domain)
    {

        if ($this->server_panel == "1") {
            /** key dan url web di aaPanel */
            $phpversion = $this->aplikasi::aapanel()['aapanel_php'];
            $this->aapanel->key = $this->aplikasi::aapanel()['aapanel_key'];
            $this->aapanel->url = $this->aplikasi::aapanel()['aapanel_ip'];
            $this->att->setFtpUser($kodekecamatan . 'ftp');
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
            $this->aapanel->AddDatabase($kodekecamatan);

            // create FTP
            $this->aapanel->addFtpUser($this->att->getFtpUser(), $this->att->getFtpPass(), $this->path);

            // setDirectory disable anti xss
            $this->aapanel->userDir($this->path);
        } else {
            // create database
            $this->createDatabase($kodekecamatan);

            // vhost dan ssl
            $domain = preg_replace('#^https?://#', '', $domain);
            $this->setVhostOpendk($domain);
            
            // Check server panel type and setup appropriate vhost
            if ($this->server_panel == "3") {
                $this->setVhostOls($domain, $kodekecamatan);
            } else {
                $this->setVhostApache($domain);
            }
        }

        $this->filesEnv->envOpendk(
            $this->att->getHost(),
            $this->att->getTemplateFolderOpendk(),
            $this->att->getSiteFolderOpendk(),
            $kodekecamatan,
            $urlApp,
        );


        $this->comm->setHtaccess(
            $this->aplikasi::pengaturan_aplikasi()['multiphp'],
            $this->att->getTemplateFolderOpendk() . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . '.htaccess',
            $this->att->getRootFolder() . 'master-opendk' . DIRECTORY_SEPARATOR . 'opendk' . DIRECTORY_SEPARATOR . '.htaccess',
            $this->att->getSiteFolderOpendk() . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . '.htaccess'
        );

        $this->setFolderOpendk($kodekecamatan);

        $this->comm->removeFile($this->att->getSiteFolderOpendk() . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . '.htaccess');
        $this->comm->indexCommand($this->att->getSiteFolderOpendk() . DIRECTORY_SEPARATOR . 'public');
        $this->comm->keyGenerateCommand($this->att->getSiteFolderOpendk());

        // Cek apakah ada user pada database, jika tidak ada, jalankan seeder

        $database = 'db_' . $kodekecamatan;
        $dbUsername = 'user_' . $kodekecamatan;
        $dbPassword = 'pass_' . $kodekecamatan;
        $envConfig = [
            'DB_DATABASE' => $database,
            'DB_USERNAME' => $dbUsername,
            'DB_PASSWORD' => $dbPassword,
        ];

        // Konfigurasi koneksi database khusus untuk OpenDK
        config(['database.connections.opendk_temp' => [
            'driver' => 'mysql',
            'host' => config('database.connections.mysql.host'),
            'port' => config('database.connections.mysql.port'),
            'database' => $database,
            'username' => $dbUsername,
            'password' => $dbPassword,
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'strict' => true,
            'engine' => null,
        ]]);

        // Cek apakah tabel users sudah ada di database custom
        $connection = DB::connection('opendk_temp');

        if (!$connection->getSchemaBuilder()->hasTable('users')) {
            // Jalankan migrate jika tabel belum ada
            ProcessService::runProcess(['php', 'artisan', 'migrate', '--seed', '--force'], $this->att->getSiteFolderOpendk(), null, $envConfig);
            
            // Buat file kosong 'installed' di storage
            $installedFilePath = $this->att->getSiteFolderOpendk() . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'installed';
            file_put_contents($installedFilePath, '');

            // Update das_profil table dengan data wilayah
            $this->updateDasProfil($connection, $kodekecamatan);
        }

        // Disconnect koneksi temporary
        DB::purge('opendk_temp');

        $this->comm->storageLink($this->att->getSiteFolderOpendk());
        
        // Composer autoload dump
        ProcessService::runProcess(['composer', 'dump-autoload'], $this->att->getSiteFolderOpendk());
    }

    public function permissionSite()
    {
        if ($this->server_panel == "1") {
            $this->aapanel->permissionDirectory($this->path);
        } else {
            ProcessService::aturKepemilikanDirektori($this->att->getSiteFolderOpendk());
        }
    }

    private function createDatabase($kodekecamatan)
    {
        $databaseService = new DatabaseService($this->ip_source_code);

        $databaseService->createDatabase("$kodekecamatan");
        $databaseService->createUser("$kodekecamatan", $kodekecamatan);
    }

    private function setVhostOpendk($domain)
    {
        $dirPublic = $this->att->getSiteFolderOpendk() . DIRECTORY_SEPARATOR . 'public';
        $apacheTemplate = $this->files->get($this->att->getTemplateFolderOpendk() . DIRECTORY_SEPARATOR . 'vhost' . DIRECTORY_SEPARATOR . 'apache.conf');
        $content = str_replace(
            ['{$domain}', '{$domainLog}', '{$documentRoot}', '{$documentDirectory}'],
            [$domain, str_replace('.', '', $domain), $dirPublic, $dirPublic],
            $apacheTemplate
        );
        $this->files->replace($this->att->getSiteFolderOpendk() . DIRECTORY_SEPARATOR . 'vhost' . DIRECTORY_SEPARATOR . 'apache.conf', $content);

        $apacheTemplate = $this->files->get($this->att->getTemplateFolderOpendk() . DIRECTORY_SEPARATOR . 'vhost' . DIRECTORY_SEPARATOR . 'nginx');
        $content = str_replace(
            ['{$domain}', '{$domainLog}', '{$documentRoot}', '{$documentDirectory}'],
            [$domain, str_replace('.', '', $domain), $dirPublic, $dirPublic],
            $apacheTemplate
        );
        $this->files->replace($this->att->getSiteFolderOpendk() . DIRECTORY_SEPARATOR . 'vhost' . DIRECTORY_SEPARATOR . 'nginx', $content);
    }

    /**
     * Menulis file konfigurasi vhost Apache dan mengaktifkannya.
     *
     * @param string $domain Nama domain yang akan dibuatkan konfigurasi vhost-nya.
     */
    private function setVhostApache($domain)
    {
        // Remove http:// or https:// from domain if present

        $originalConf = $this->att->getSiteFolderOpendk() . DIRECTORY_SEPARATOR . 'vhost' . DIRECTORY_SEPARATOR . 'apache.conf';
        $this->comm->chownCommand($this->att->getApacheConfDir());

        $this->fileService->replaceFile($originalConf, $this->att->getApacheConfDir() . $domain . '.conf');
        exec("sudo a2ensite $domain.conf");

        // buat ssl
        $this->comm->certbotSsl($domain);

        exec("sudo service apache2 restart");
    }

    /**
     * Setup konfigurasi vhost untuk OpenLiteSpeed
     *
     * @param string $domain
     * @param string $kodekecamatan
     * @return void
     */
    private function setVhostOls($domain, $kodekecamatan)
    {
        if ($this->server_panel != "3" || !$this->openLiteSpeed) {
            return;
        }

        try {
            $documentRoot = $this->att->getSiteFolderOpendk() . DIRECTORY_SEPARATOR . 'public';

            $this->info("Creating OpenLiteSpeed virtual host for {$domain}");

            // Create virtual host using OpenLiteSpeedService
            $this->openLiteSpeed->createVirtualHost($kodekecamatan, $domain, $documentRoot);

            // Membuat SSL
            $this->comm->certbotSsl($domain);

            // Reload OpenLiteSpeed configuration
            $this->openLiteSpeed->reload();

            $this->info("OpenLiteSpeed virtual host created successfully for {$domain}");
        } catch (\Exception $e) {
            $this->error("Error creating OpenLiteSpeed virtual host for {$domain}: " . $e->getMessage());
        }
    }

    private function setFolderOpendk($kodekecamatan)
    {
        $this->filesIndex->indexPhpOpendk(
            'opendk',
            $this->att->getRootFolder() . 'master-opendk' . DIRECTORY_SEPARATOR,  // opendkFolderFrom
            $this->att->getMultisiteFolder() . $kodekecamatan, // opendkFolderTo
            $this->att->getIndexTemplateOpendk(),
            $this->att->getIndexOpendk()
        );
    }

    private function updateData($kodekecamatan): void
    {
        if (preg_match('/^(\d{2})(\d{2})(\d{2})$/', $kodekecamatan, $matches)) {
            $kodekecamatan = $matches[1] . '.' . $matches[2] . '.' . $matches[3];
        }
        Wilayah::where('kode_kec', $kodekecamatan)
            ->update(['opendk_terdaftar' => '1']);
    }

    /**
     * Update das_profil table dengan data wilayah
     *
     * @param \Illuminate\Database\Connection $connection
     * @param string $kodekecamatan
     * @return void
     */
    private function updateDasProfil($connection, $kodekecamatan): void
    {
        // Format kode kecamatan menjadi format dengan titik
        $kodeKecFormatted = preg_match('/^(\d{2})(\d{2})(\d{2})$/', $kodekecamatan, $matches)
            ? $matches[1] . '.' . $matches[2] . '.' . $matches[3]
            : $kodekecamatan;

        // Ambil data dari tabel pelanggan
        $pelanggan = Pelanggan::where('kode_kecamatan', $kodeKecFormatted)->first();

        if ($pelanggan) {
            // Update das_profil di database opendk
            $connection->table('das_profil')
                ->where('id', 1)
                ->update([
                    'provinsi_id' => $pelanggan->kode_provinsi,
                    'nama_provinsi' => $pelanggan->nama_provinsi,
                    'kabupaten_id' => $pelanggan->kode_kabupaten,
                    'nama_kabupaten' => $pelanggan->nama_kabupaten,
                    'kecamatan_id' => $pelanggan->kode_kecamatan,
                    'nama_kecamatan' => $pelanggan->nama_kecamatan,
                ]);
        }
    }
}

