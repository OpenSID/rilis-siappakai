<?php

namespace App\Services;

use GuzzleHttp\Client;
use App\Models\Aplikasi;
use App\Models\Pelanggan;
use GuzzleHttp\Cookie\CookieJar;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Filesystem\Filesystem;
use Symfony\Component\Process\Process;


class OpensidService
{
    private $ip_source_code;
    private $ip_database;
    private $kode_desa_without_dot;
    private $rootFolder;
    private $root_multisite_folder;
    private $root_folder_siappakai;
    private $template_opensid_path;
    private $kode_desa;
    private $pelanggan;

    public function __construct($kodeDesa)
    {
        $this->kode_desa = $kodeDesa;
        $this->kode_desa_without_dot = str_replace('.', '', $kodeDesa);
        $this->ip_source_code = Aplikasi::pengaturan_aplikasi()['ip_source_code'] ?? 'localhost';
        $this->ip_database = env('DB_HOST', '127.0.0.1');
        $this->root_multisite_folder = config('siappakai.root.folder_multisite');
        $this->root_folder_siappakai = base_path();
        $this->template_opensid_path = base_path('master-template/template-opensid/');
        $this->pelanggan = Pelanggan::where('kode_desa', $kodeDesa)->firstOrFail();
    }


    public function migrasiDatabaseTunggal(string $kodeDesa): void
    {
        $domain = $this->pelanggan->domain_opensid;
        $baseUrl = str_starts_with($domain, 'http://') || str_starts_with($domain, 'https://')
            ? $domain
            : 'https://' . $domain;

        $folderKodeDesa = $this->root_multisite_folder . $this->kode_desa_without_dot;
        $folderDesa = $folderKodeDesa . DIRECTORY_SEPARATOR . 'desa';

        if (File::exists($folderDesa)) {
            (new RecyclebinService())->moveToRecycleBin($folderDesa);
        }

        ProcessService::runProcess(['php', 'index.php', 'artisan', 'view:clear'], $folderKodeDesa);

        $jar = new CookieJar();
        $client = new Client([
            'base_uri' => $baseUrl,
            'verify' => false,
            'cookies' => $jar
        ]);

        // Step 1: Ambil sidcsrf
        $client->request('GET', 'index.php/install/database', [
            'headers' => ['Referer' => url()->current()],
            'allow_redirects' => [
                'max' => 2,
                'strict' => true,
                'referer' => true,
                'track_redirects' => true
            ],
        ]);
        $sidcsrf = collect($jar->toArray())->firstWhere('Name', 'sidcsrf');
        if (!$sidcsrf) {
            throw new \Exception('sidcsrf tidak ditemukan di halaman database');
        }

        // Step 2: Submit form database
        $dbData = [
            'sidcsrf' => $sidcsrf['Value'],
            'database_hostname' => $this->ip_database,
            'database_port' => env('DB_PORT', '3306'),
            'database_name' => 'db_' . $this->kode_desa_without_dot,
            'database_username' => 'user_' . $this->kode_desa_without_dot,
            'database_password' => 'pass_' . $this->kode_desa_without_dot,
        ];
        $client->request('POST', '/install/database', [
            'form_params' => $dbData,
            'headers' => ['Referer' => url()->current()],
            'allow_redirects' => [
                'max' => 2,
                'strict' => true,
                'referer' => true,
                'track_redirects' => true
            ],
            'cookies' => $jar,
            'timeout' => 0,
        ]);

        $sidcsrf = collect($jar->toArray())->firstWhere('Name', 'sidcsrf');
        if (!$sidcsrf) {
            throw new \Exception('sidcsrf tidak ditemukan di halaman database');
        }

        // Step 3: Jalankan migrasi
        $client->request('POST', '/install/migrations', [
            'form_params' => ['sidcsrf' => $sidcsrf['Value']],
            'headers' => ['Referer' => url()->current()],
            'allow_redirects' => [
                'max' => 2,
                'strict' => true,
                'referer' => true,
                'track_redirects' => true
            ],
            'cookies' => $jar,
            'timeout' => 0,
        ]);

        $this->updateConfig();
    }

    public function updateConfig(): void
    {
        $folderKodeDesa = $this->root_multisite_folder . $this->kode_desa_without_dot;
        (new OpensidInstallerService())->aturKonfigurasiOpensid(
            $folderKodeDesa,
            $this->kode_desa_without_dot,
            $this->kode_desa_without_dot,
            $this->pelanggan->token_premium
        );
        ProcessService::runProcess(['php', 'index.php', 'artisan', 'view:clear'], $folderKodeDesa);
    }
}
