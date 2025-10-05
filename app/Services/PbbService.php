<?php

namespace App\Services;

use App\Models\Aplikasi;

use App\Models\Pelanggan;
use App\Enums\RepositoryEnum;
use App\Services\FileService;
use App\Services\DatabaseService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File;
use Illuminate\Filesystem\Filesystem;

class PbbService
{

    private $pathTemplate;
    private $files;
    private $fileservice;
    public function __construct()
    {
        $this->files = new Filesystem();
        $this->pathTemplate = RepositoryEnum::getFolderTemplate('pbb');
        $this->fileservice = new FileService();
    }

    function installTemplateDesa($kodedesa): void
    {

        $pathMultisite = config('siappakai.root.folder_multisite');
        $pathCustomer = $pathMultisite . $kodedesa;
        $pathPbb = $pathCustomer . DIRECTORY_SEPARATOR . 'pbb-app';
        $temp = storage_path('app') . '/temp';

        if (file_exists($pathPbb)) {
            $this->konfigurasiIndex($kodedesa);
            $this->konfigurasiEnv($kodedesa);

            return;
        }

        if (!File::exists($temp)) {
            File::makeDirectory($temp); // hanya menghapus isi di dalam folder, bukan foldernya
        }

        // Hapus jika ada folder temp lama (biar bersih)
        if (File::exists($temp)) {
            File::cleanDirectory($temp); // hanya menghapus isi di dalam folder, bukan foldernya
        }

        // jika folder api-app belum ada, maka install ulang api opensid
        if (File::copyDirectory($this->pathTemplate, $temp)) {
            // Rename temp-copy jadi api-app
            File::move($temp, $pathPbb);
            Log::info('Folder berhasil disalin dan diubah menjadi pbb-app.');
        }

        $this->konfigurasiIndex($kodedesa);
        $this->konfigurasiEnv($kodedesa);
    }

    function konfigurasiEnv($kodedesa)
    {
        $kodedesaFormatted = substr($kodedesa, 0, 2) . '.' . substr($kodedesa, 2, 2) . '.' . substr($kodedesa, 4, 2) . '.' . substr($kodedesa, 6, 4);
        $pathMultisite = config('siappakai.root.folder_multisite');
        $pathCustomer = $pathMultisite . $kodedesa;
        $pathPbb = $pathCustomer . DIRECTORY_SEPARATOR . 'pbb-app';
        $envPath = $pathPbb . DIRECTORY_SEPARATOR . '.env';
        $publicPath = $pathPbb . DIRECTORY_SEPARATOR . 'public';
        $DB_HOST = config('database.connections.mysql.host');
        $pelanggan = Pelanggan::where('kode_desa', $kodedesaFormatted)->first();
        $appkey = 'base64:' . base64_encode(random_bytes(32));
        $database = 'db_' . $kodedesa . '_pbb';
        $dbUsername = 'user_' . $kodedesa;
        $dbPassword = 'pass_' . $kodedesa;
        $envConfig = [
            'DB_DATABASE' => $database,
            'DB_USERNAME' => $dbUsername,
            'DB_PASSWORD' => $dbPassword,
        ];
        $urlPbb = 'https://' . $pelanggan->domain_pbb;

        if (!File::exists($envPath)) {
            // Jika file .env belum ada, copy dari .env.example
            $envExamplePath = $pathPbb . DIRECTORY_SEPARATOR . '.env.example';
            if (File::exists($envExamplePath)) {
                File::copy($envExamplePath, $envPath);
            }
        }
        $ip_source_code = Aplikasi::pengaturan_aplikasi()['ip_source_code'] ?? 'localhost';
        $databaseService = new DatabaseService($ip_source_code);
        $databaseService->createUser($kodedesa . '_pbb', $kodedesa);

        if (File::exists($envPath)) {
            ConsoleService::info('menjalankan index.php di :' . $publicPath);
            ProcessService::runProcess(['php', 'index.php'], $publicPath);
            ConsoleService::info('Mengkonfigurasi file .env untuk ' . $kodedesa);
            ConsoleService::info('path env: ' . $envPath);
            $env = File::get($envPath);
            $env = preg_replace('/^APP_KEY=.*/m', 'APP_KEY=' . $appkey, $env);
            $env = preg_replace('/^ASSET_URL=.*/m', 'ASSET_URL=' . $urlPbb, $env);
            $env = preg_replace('/^APP_URL=.*/m', 'APP_URL=' . $urlPbb, $env);
            $env = preg_replace('/^DB_HOST=.*/m', 'DB_HOST=' . $DB_HOST, $env);
            $env = preg_replace('/^DB_PORT=.*/m', 'DB_PORT=' . env('DB_PORT', '3306'), $env);
            $env = preg_replace('/^DB_DATABASE=.*/m', 'DB_DATABASE=' . $database, $env);
            $env = preg_replace('/^DB_USERNAME=.*/m', 'DB_USERNAME=' . $dbUsername, $env);
            $env = preg_replace('/^DB_PASSWORD=.*/m', 'DB_PASSWORD=' . $dbPassword, $env);
            $env = preg_replace('/^KODE_DESA=.*/m', 'KODE_DESA=' . $pelanggan->kode_desa, $env);
            $env = preg_replace('/^HOST_PREMIUM=.*/m', 'HOST_PREMIUM=' . 'https://layanan.opendesa.id', $env);
            $env = preg_replace('/^TOKEN_PREMIUM=.*/m', 'TOKEN_PREMIUM=' . $pelanggan->token_premium, $env);
            $env = preg_replace('/^MAIL_MAILER=.*/m', 'MAIL_MAILER=' . env('MAIL_MAILER', 'smtp'), $env);
            $env = preg_replace('/^MAIL_HOST=.*/m', 'MAIL_HOST=' . env('MAIL_HOST', 'mailhog'), $env);
            $env = preg_replace('/^MAIL_PORT=.*/m', 'MAIL_PORT=' . env('MAIL_PORT', '1025'), $env);
            $env = preg_replace('/^MAIL_USERNAME=.*/m', 'MAIL_USERNAME=' . env('MAIL_USERNAME', 'null'), $env);
            $env = preg_replace('/^MAIL_PASSWORD=.*/m', 'MAIL_PASSWORD=' . env('MAIL_PASSWORD', 'null'), $env);
            $env = preg_replace('/^MAIL_ENCRYPTION=.*/m', 'MAIL_ENCRYPTION=' . env('MAIL_ENCRYPTION', 'null'), $env);
            $env = preg_replace('/^MAIL_FROM_ADDRESS=.*/m', 'MAIL_FROM_ADDRESS=' . env('MAIL_FROM_ADDRESS', 'null'), $env);
            File::put($envPath, $env);

            ConsoleService::info('Memulai proses artisan untuk ' . $kodedesa . '...');
            ProcessService::runProcess(
                ['php', 'artisan', 'migrate', '--force'],
                $pathPbb,
                null,
                $envConfig
            );

            // Cek apakah ada user pada database, jika tidak ada, jalankan seeder
            $userCount = $userCount = DB::table($database . '.users')->count();

            if ($userCount == 0) {
                ConsoleService::info('Tidak ada user pada database ' . $database . ', menjalankan seeder...');
                ProcessService::runProcess(['php', 'artisan', 'db:seed', '--force'], $pathPbb, null, $envConfig);
            }

            ConsoleService::info('Menjalankan artisan optimize:clear untuk ' . $kodedesa);
            ProcessService::runProcess(['php', 'artisan', 'optimize:clear'], $pathPbb);
            ProcessService::runProcess(['php', 'artisan', 'storage:link'], $pathPbb);
            ConsoleService::info('Proses artisan untuk ' . $kodedesa . ' selesai.');
        }
        return;
    }

    function konfigurasiIndex($kodedesa)
    {
        $pathMultisite = config('siappakai.root.folder_multisite');
        $pathRoot = config('siappakai.root.folder');
        $pbbFolderFrom = $pathRoot . 'master-pbb' . DIRECTORY_SEPARATOR;
        $pbbFolder = 'pbb_desa';
        $pathCustomer = $pathMultisite . $kodedesa;

        $directorySeparator = DIRECTORY_SEPARATOR;
        $pbbFolderTo =  $pathCustomer . DIRECTORY_SEPARATOR . 'pbb-app';
        $pbbIndex = $pbbFolderTo . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'index.php';
        if (!File::exists($pbbIndex)) {
            ConsoleService::info('File index.php tidak ditemukan di ' . $pbbIndex);
            Log::error('File index.php tidak ditemukan di ' . $pbbIndex);
            return;
        }

        $indexTemplate = $this->files->get($pbbIndex);

        $content = str_replace(
            ['{$pbbFolder}', '{$pbbFolderFrom}', '{$pbbFolderTo}', '{$directorySeparator}'],
            [$pbbFolder, $pbbFolderFrom, $pbbFolderTo, $directorySeparator],
            $indexTemplate
        );
        $this->files->replace($pbbIndex, $content);

        // perbaiki index yang salah
        $indexContent = $this->files->get($pbbIndex);
        // Cari dan ganti define PBB_FOLDER_FROM
        $indexContent = preg_replace(
            "/define\('PBB_FOLDER_FROM',\s*'[^']*'\);/",
            "define('PBB_FOLDER_FROM', '/var/www/html/master-pbb/pbb_desa');",
            $indexContent
        );

        $this->files->put($pbbIndex, $indexContent);

        // Cek dan hapus symlink yang bermasalah di $pbbFolderTo (file dan folder)
        if (File::isDirectory($pbbFolderTo)) {
            $allItems = File::allFiles($pbbFolderTo);
            $allDirs = File::directories($pbbFolderTo);
            $this->fileservice->hapusSymlinkBermasalah($allItems, $allDirs, 'pbbFolderTo');
        }

         // Cek dan hapus symlink yang bermasalah di public (file dan folder)
        $publicPath = $pbbFolderTo . DIRECTORY_SEPARATOR . 'public';
        if (File::isDirectory($publicPath)) {
            $files = File::allFiles($publicPath);
            $dirs = File::directories($publicPath);
            $this->fileservice->hapusSymlinkBermasalah($files, $dirs, 'public');
            $this->fileservice->hapusSymlinkHtaccess($publicPath, 'public');
        }

        // Hapus symlink .htaccess di root pbb-app jika ada
        $this->fileservice->hapusSymlinkHtaccess($pbbFolderTo, 'root pbb-app');

        ConsoleService::info('Menjalankan index.php di: ' . $pbbIndex);
        ProcessService::runProcess(['php', 'index.php'], $publicPath);
    }
}
