<?php

namespace App\Services;

use App\Models\Aplikasi;
use App\Services\ProcessService;
use App\Enums\UrlApiGit;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Exception;

class MasterOpensidService
{
    private $multiPhp;
    private $folderMultiside;
    public function __construct()
    {
        // Konfigurasi multi PHP yang digunakan untuk mengatur nama folder
        // yang dihasilkan oleh installer
        $this->multiPhp = Aplikasi::pengaturan_aplikasi()['multiphp'];

        $this->folderMultiside = config('siappakai.root.multisite');
    
    }
    public function cekVersiServer($opensid = 'premium')
    {
        $path_root = dirname(base_path(), 1);
        $content_versi = 0;

        switch ($opensid) {
            case 'premium':
                $premium_folder = $path_root . DIRECTORY_SEPARATOR . 'master-opensid' . DIRECTORY_SEPARATOR . 'premium';
                if (File::exists($premium_folder)) {
                    $tags_server = 'cd ' . $premium_folder . DIRECTORY_SEPARATOR . ' && git describe --tags';
                    $content_versi = exec($tags_server);
                }
                $version_server = substr($content_versi, 0, 9);

                break;

            default:
                $umum_folder = $path_root . DIRECTORY_SEPARATOR . 'master-opensid' . DIRECTORY_SEPARATOR . 'umum';
                if (File::exists($umum_folder)) {
                    $tags_server = 'cd ' . $umum_folder . DIRECTORY_SEPARATOR . ' && git describe --tags';
                    $content_versi = exec($tags_server);
                }
                $version_server = substr($content_versi, 0, 9);
                break;
        }

        return  $version_server;
    }

    /**
     * Menangani file .htaccess yang digunakan untuk mengatur URL Opensid.
     *
     * Jika $this->multiPhp == 1, maka file .htaccess akan dibuat dengan symlink ke
     * file .htaccess di master-template/template-opensid.
     * Jika $this->multiPhp == 0, maka file .htaccess akan dibuat dengan meng-copy
     * file htaccess.txt di master-template/template-opensid.
     *
     * @param string $direktoriSitus Folder site tempat .htaccess akan dibuat
     * @return void
     */
    public function tanganiHtaccess(string $direktoriSitus): void
    {
        $direktoriTemplate = config('siappakai.root.folder') . 'master-template' . DIRECTORY_SEPARATOR . 'template-opensid';
        $htaccessFrom = $direktoriTemplate . DIRECTORY_SEPARATOR . '.htaccess';
        $fileFrom =  $direktoriTemplate . DIRECTORY_SEPARATOR . 'htaccess.txt';
        $htaccessTo =    $direktoriSitus . DIRECTORY_SEPARATOR . '.htaccess';

        // Menentukan metode penanganan .htaccess berdasarkan konfigurasi multiPhp
        if ($this->multiPhp == 1) {
            ProcessService::createSymlink($htaccessFrom, $htaccessTo);
        } else {
            ProcessService::copyFile($fileFrom, $htaccessTo);
        }
    }

    static function getLatestVersionFromGitHub($opensid = 'premium')
    {
        $url = $opensid == 'premium' ? UrlApiGit::OPENSID_PREMIUM_LATEST : UrlApiGit::OPENSID_UMUM_LATEST;
        try {
            // Mengambil informasi rilis terbaru dari GitHub
            $response = Http::withHeaders([
                'Accept' => 'application/vnd.github.v3+json',
                'Authorization' => "token " . config('siappakai.git.token'),
            ])->get($url)->throw()->json();


            return $response['tag_name'];
        } catch (Exception $e) {
            throw new Exception("Gagal mendapatkan versi terbaru dari GitHub: " . $e->getMessage());
        }
    }
}
