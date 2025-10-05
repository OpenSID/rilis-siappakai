<?php

namespace App\Services;

use Directory;
use App\Models\Aplikasi;
use App\Models\Pelanggan;
use App\Enums\RepositoryEnum;
use App\Services\FileService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File;
use Illuminate\Filesystem\Filesystem;


class ApiOpensidService
{
    const DATABASE_TYPE_SINGLE = 'database_tunggal';
    const DATABASE_TYPE_COMBINED = 'database_gabungan';

    private $pathTemplate;
    private $files;
    private $fileservice;

    public function __construct()
    {
        $this->files = new Filesystem();
        $this->pathTemplate = RepositoryEnum::getFolderTemplate('opensid-api');
        $this->fileservice = new FileService();
    }

    /**
     * Instalasi template api opensid untuk tiap desa. Jika folder api-app sudah ada, maka tidak perlu diinstall ulang
     *
     * @param string $kodedesa kode desa yang akan diinstall
     * @return void
     */
    public function installTemplateDesa(string $kodedesa): void
    {
        $pathConfig = $this->getInstallationPaths($kodedesa);

        if ($this->isApiAlreadyInstalled($pathConfig['pathApi'])) {
            $this->configureExistingInstallation($kodedesa);
            return;
        }

        $this->performFreshInstallation($pathConfig, $kodedesa);
    }

    /**
     * Dapatkan path untuk instalasi
     */
    private function getInstallationPaths(string $kodedesa): array
    {
        $pathMultisite = config('siappakai.root.folder_multisite');
        $pathCustomer = $pathMultisite . $kodedesa;
        $pathApi = $pathCustomer . DIRECTORY_SEPARATOR . 'api-app';
        $temp = storage_path('app') . '/temp';

        return [
            'pathMultisite' => $pathMultisite,
            'pathCustomer' => $pathCustomer,
            'pathApi' => $pathApi,
            'temp' => $temp
        ];
    }

    /**
     * Cek apakah API sudah terinstall
     */
    private function isApiAlreadyInstalled(string $pathApi): bool
    {
        return file_exists($pathApi);
    }

    /**
     * Konfigurasi untuk instalasi yang sudah ada
     */
    private function configureExistingInstallation(string $kodedesa): void
    {
        $this->konfigurasiIndex($kodedesa);
        $this->konfigurasiEnv($kodedesa);
    }

    /**
     * Lakukan instalasi fresh
     */
    private function performFreshInstallation(array $pathConfig, string $kodedesa): void
    {
        $this->prepareTempDirectory($pathConfig['temp']);
        $this->copyTemplateToTemp($pathConfig);
        $this->configureExistingInstallation($kodedesa);
    }

    /**
     * Siapkan direktori temp
     */
    private function prepareTempDirectory(string $temp): void
    {
        if (!File::exists($temp)) {
            File::makeDirectory($temp);
        }

        // Hapus jika ada folder temp lama (biar bersih)
        if (File::exists($temp)) {
            File::cleanDirectory($temp);
        }
    }

    /**
     * Copy template ke temp dan rename ke api-app
     */
    private function copyTemplateToTemp(array $pathConfig): void
    {
        // jika folder api-app belum ada, maka install ulang api opensid
        if (File::copyDirectory($this->pathTemplate, $pathConfig['temp'])) {
            // Rename temp-copy jadi api-app
            File::move($pathConfig['temp'], $pathConfig['pathApi']);
            Log::info('Folder berhasil disalin dan diubah menjadi api-app.');
        }
    }

    /**
     * Mengkonfigurasi file .env untuk opensid-api customer.
     *
     * Mengkonfigurasi file .env untuk opensid-api customer dengan data pelanggan yang
     * sesuai. Jika file .env tidak ada, maka tidak akan ada yang diubah.
     *
     * @param string $kodedesa Kode desa yang akan diatur.
     *
     * @return void
     */
    public function konfigurasiEnv(string $kodedesa): void
    {
        $kodedesaFormatted = $this->formatKodeDesa($kodedesa);
        $pelanggan = $this->getPelanggan($kodedesaFormatted);

        if (!$pelanggan) {
            Log::error("Pelanggan dengan kode desa {$kodedesaFormatted} tidak ditemukan");
            return;
        }

        $pathConfig = $this->getPathConfiguration($kodedesa);
        $this->ensureEnvFileExists($pathConfig['envPath'], $pathConfig['envExamplePath']);

        $databaseConfig = $this->getDatabaseConfiguration($kodedesa, $pelanggan);
        $this->createUserDatabase($databaseConfig);

        $this->updateEnvFile($pathConfig, $pelanggan, $databaseConfig);
        $this->runArtisanCommands($pathConfig);
    }

    /**
     * Format kode desa ke format yang sesuai (xx.xx.xx.xxxx)
     */
    private function formatKodeDesa(string $kodedesa): string
    {
        return substr($kodedesa, 0, 2) . '.' . substr($kodedesa, 2, 2) . '.' . substr($kodedesa, 4, 2) . '.' . substr($kodedesa, 6, 4);
    }

    /**
     * Ambil data pelanggan berdasarkan kode desa
     */
    private function getPelanggan(string $kodedesaFormatted): ?Pelanggan
    {
        return Pelanggan::where('kode_desa', $kodedesaFormatted)->first();
    }

    /**
     * Konfigurasi path yang diperlukan
     */
    private function getPathConfiguration(string $kodedesa): array
    {
        $pathMultisite = config('siappakai.root.folder_multisite');
        $pathCustomer = $pathMultisite . $kodedesa;
        $pathApi = $pathCustomer . DIRECTORY_SEPARATOR . 'api-app';

        return [
            'pathApi' => $pathApi,
            'envPath' => $pathApi . DIRECTORY_SEPARATOR . '.env',
            'envExamplePath' => $pathApi . DIRECTORY_SEPARATOR . '.env.example',
            'publicPath' => $pathApi . DIRECTORY_SEPARATOR . 'public'
        ];
    }

    /**
     * Pastikan file .env ada, jika tidak copy dari .env.example
     */
    private function ensureEnvFileExists(string $envPath, string $envExamplePath): void
    {
        if (!File::exists($envPath) && File::exists($envExamplePath)) {
            File::copy($envExamplePath, $envPath);
        }
    }

    /**
     * Konfigurasi database berdasarkan pengaturan
     */
    private function getDatabaseConfiguration(string $kodedesa, Pelanggan $pelanggan): array
    {
        $langgananOpensid = preg_replace('/[^a-zA-Z_]/', '', $pelanggan->langganan_opensid);
        $tipDatabase = $this->getDatabaseType();

        if ($tipDatabase === self::DATABASE_TYPE_SINGLE) {
            return [
                'type' => self::DATABASE_TYPE_SINGLE,
                'database_name' => 'db_' . $kodedesa,
                'database_key' => $kodedesa,
                'user_key' => $kodedesa
            ];
        }

        return [
            'type' => self::DATABASE_TYPE_COMBINED,
            'database_name' => 'db_gabungan_' . $langgananOpensid,
            'database_key' => 'gabungan_' . $langgananOpensid,
            'user_key' => $kodedesa
        ];
    }

    /**
     * Ambil tipe database dari pengaturan aplikasi
     */
    private function getDatabaseType(): string
    {
        $pengaturanDatabase = Aplikasi::where('key', 'pengaturan_database')->first();
        return $pengaturanDatabase ? $pengaturanDatabase->value : self::DATABASE_TYPE_COMBINED;
    }

    /**
     * Buat database dan user sesuai konfigurasi
     */
    private function createUserDatabase(array $databaseConfig): void
    {
        $aplikasi = Aplikasi::where('key', 'ip_source_code')->first();
        $ip_source_code = $aplikasi ? $aplikasi->value : 'localhost';
        $databaseService = new DatabaseService($ip_source_code);

        $databaseService->createUser($databaseConfig['database_key'], $databaseConfig['user_key']);
    }

    /**
     * Update file .env dengan konfigurasi yang diperlukan
     */
    private function updateEnvFile(array $pathConfig, Pelanggan $pelanggan, array $databaseConfig): void
    {
        $envPath = $pathConfig['envPath'];
        $publicPath = $pathConfig['publicPath'];
        $pathApi = $pathConfig['pathApi'];

        if (!File::exists($envPath)) {
            return;
        }

        ProcessService::runProcess(['php', 'index.php'], $publicPath, 'triger index.php');
        ConsoleService::info('Mengkonfigurasi file .env untuk ' . $pelanggan->kode_desa);
        ConsoleService::info('path env: ' . $envPath);

        $envConfig = $this->prepareEnvConfiguration($pelanggan, $databaseConfig);
        $this->writeEnvFile($envPath, $envConfig);
    }

    /**
     * Siapkan konfigurasi environment
     */
    private function prepareEnvConfiguration(Pelanggan $pelanggan, array $databaseConfig): array
    {
        $DB_HOST = config('database.connections.mysql.host');
        $ftpuser = env('FTP_USER', 'ftpuser');
        $ftppass = env('FTP_PASS', 'ftppass');
        $appkey = 'base64:' . base64_encode(random_bytes(32));

        return [
            'APP_KEY' => $appkey,
            'DB_HOST' => $DB_HOST,
            'DB_PORT' => env('DB_PORT', '3306'),
            'DB_DATABASE' => $databaseConfig['database_name'],
            'DB_USERNAME' => 'user_' . $databaseConfig['user_key'],
            'DB_PASSWORD' => 'pass_' . $databaseConfig['user_key'],
            'FTP_HOST' => 'localhost',
            'FTP_URL' => "'" . $pelanggan->domain_opensid . "'",
            'FTP_ROOT' => "'/var/www/html/multisite/" . str_replace('.', '', $pelanggan->kode_desa) . "'",
            'FTP_USERNAME' => $ftpuser,
            'FTP_PASSWORD' => $ftppass,
            'KODE_DESA' => $pelanggan->kode_desa,
            'HOST_PREMIUM' => 'https://layanan.opendesa.id',
            'TOKEN_PREMIUM' => $pelanggan->token_premium,
            'MAIL_MAILER' => env('MAIL_MAILER', 'smtp'),
            'MAIL_HOST' => env('MAIL_HOST', 'mailhog'),
            'MAIL_PORT' => env('MAIL_PORT', '1025'),
            'MAIL_USERNAME' => env('MAIL_USERNAME', 'null'),
            'MAIL_PASSWORD' => env('MAIL_PASSWORD', 'null'),
            'MAIL_ENCRYPTION' => env('MAIL_ENCRYPTION', 'null'),
            'MAIL_FROM_ADDRESS' => env('MAIL_FROM_ADDRESS', 'null'),
        ];
    }

    /**
     * Tulis konfigurasi ke file .env
     */
    private function writeEnvFile(string $envPath, array $envConfig): void
    {
        $env = File::get($envPath);

        foreach ($envConfig as $key => $value) {
            $env = preg_replace("/^{$key}=.*/m", "{$key}={$value}", $env);
        }

        File::put($envPath, $env);
    }

    /**
     * Jalankan perintah artisan yang diperlukan
     */
    private function runArtisanCommands(array $pathConfig): void
    {
        $pathApi = $pathConfig['pathApi'];
        $kodedesa = basename($pathApi, '/api-app');

        ConsoleService::info('Memulai proses artisan untuk ' . $kodedesa . '...');
        ProcessService::runProcess(['php', 'artisan', 'jwt:secret', '--force'], $pathApi);
        ConsoleService::info('Menjalankan artisan optimize:clear untuk ' . $kodedesa);
        ProcessService::runProcess(['php', 'artisan', 'optimize:clear'], $pathApi);
        ConsoleService::info('Proses artisan untuk ' . $kodedesa . ' selesai.');
    }

    /**
     * Konfigurasi file index.php untuk API OpenSID
     *
     * @param string $kodedesa Kode desa yang akan dikonfigurasi
     * @return void
     */
    public function konfigurasiIndex(string $kodedesa): void
    {
        $pathConfig = $this->getIndexPathConfiguration($kodedesa);

        if (!$this->validateIndexFile($pathConfig['apiIndex'])) {
            return;
        }

        $this->updateIndexTemplate($pathConfig);
        $this->fixIndexConfiguration($pathConfig['apiIndex']);
        $this->cleanupSymlinks($pathConfig);
        $this->executeIndexScript($pathConfig);
    }

    /**
     * Konfigurasi path untuk index
     */
    private function getIndexPathConfiguration(string $kodedesa): array
    {
        $pathMultisite = config('siappakai.root.folder_multisite');
        $pathRoot = config('siappakai.root.folder');
        $pathCustomer = $pathMultisite . $kodedesa;
        $apiFolderTo = $pathCustomer . DIRECTORY_SEPARATOR . 'api-app';
        $publicPath = $apiFolderTo . DIRECTORY_SEPARATOR . 'public';

        return [
            'pathRoot' => $pathRoot,
            'pathCustomer' => $pathCustomer,
            'apiFolderFrom' => $pathRoot . 'master-api' . DIRECTORY_SEPARATOR,
            'apiFolderTo' => $apiFolderTo,
            'apiIndex' => $apiFolderTo . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'index.php',
            'publicPath' => $publicPath,
            'apiFolder' => 'opensid-api',
            'directorySeparator' => DIRECTORY_SEPARATOR
        ];
    }

    /**
     * Validasi file index.php
     */
    private function validateIndexFile(string $apiIndex): bool
    {
        if (!File::exists($apiIndex)) {
            ConsoleService::info('File index.php tidak ditemukan di ' . $apiIndex);
            Log::error('File index.php tidak ditemukan di ' . $apiIndex);
            return false;
        }
        return true;
    }

    /**
     * Update template index.php
     */
    private function updateIndexTemplate(array $pathConfig): void
    {
        $indexTemplate = $this->files->get($pathConfig['apiIndex']);

        $content = str_replace(
            ['{$apiFolder}', '{$apiFolderFrom}', '{$apiFolderTo}', '{$directorySeparator}'],
            [$pathConfig['apiFolder'], $pathConfig['apiFolderFrom'], $pathConfig['apiFolderTo'], $pathConfig['directorySeparator']],
            $indexTemplate
        );

        $this->files->replace($pathConfig['apiIndex'], $content);
    }

    /**
     * Perbaiki konfigurasi index.php
     */
    private function fixIndexConfiguration(string $apiIndex): void
    {
        $indexContent = $this->files->get($apiIndex);

        // Cari dan ganti define API_FOLDER_FROM
        $indexContent = preg_replace(
            "/define\('API_FOLDER_FROM',\s*'[^']*'\);/",
            "define('API_FOLDER_FROM', '/var/www/html/master-api/opensid-api');",
            $indexContent
        );

        $this->files->put($apiIndex, $indexContent);
    }

    /**
     * Bersihkan symlink yang bermasalah
     */
    private function cleanupSymlinks(array $pathConfig): void
    {
        $apiFolderTo = $pathConfig['apiFolderTo'];
        $publicPath = $pathConfig['publicPath'];

        // Cek dan hapus symlink yang bermasalah di api folder
        if (File::isDirectory($apiFolderTo)) {
            $allItems = File::allFiles($apiFolderTo);
            $allDirs = File::directories($apiFolderTo);
            $this->fileservice->hapusSymlinkBermasalah($allItems, $allDirs, 'apiFolderTo');
        }

        // Cek dan hapus symlink yang bermasalah di public
        if (File::isDirectory($publicPath)) {
            $files = File::allFiles($publicPath);
            $dirs = File::directories($publicPath);
            $this->fileservice->hapusSymlinkBermasalah($files, $dirs, 'public');
            $this->fileservice->hapusSymlinkHtaccess($publicPath, 'public');
        }

        // Hapus symlink .htaccess di root api-app jika ada
        $this->fileservice->hapusSymlinkHtaccess($apiFolderTo, 'root api-app');
    }

    /**
     * Jalankan script index.php
     */
    private function executeIndexScript(array $pathConfig): void
    {
        ConsoleService::info('Menjalankan index.php di: ' . $pathConfig['apiIndex']);
        ProcessService::runProcess(['php', 'index.php'], $pathConfig['publicPath']);
    }
}
