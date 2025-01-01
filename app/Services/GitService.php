<?php

namespace App\Services;

use Exception;
use App\Enums\RepositoryEnum;
use App\Services\FileService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class GitService
{
    protected $apiBaseUrl;

    public function __construct()
    {
        $this->apiBaseUrl = 'https://api.github.com/repos';
    }

    /**
     * Clone repository dari Github.
     *
     * @param RepositoryEnum $repositoryEnum Enum repository yang akan di-clone.
     * @param string $destination Direktori tujuan untuk menyimpan repository yang di-clone.
     * @return bool True jika proses clone berhasil, false jika gagal.
     * @throws Exception Jika terjadi kesalahan saat proses clone.
     */
    public static function cloneRepository(RepositoryEnum $repositoryEnum, string $destination): void
    {
        try {
            // Ambil owner dan repo dari RepositoryEnum
            $owner = $repositoryEnum->getOwner();
            $repo = $repositoryEnum->getRepo();
            $folderName = $repositoryEnum->getFolderName();

            // Pastikan direktori kosong
            if (!Storage::exists($destination)) {
                Storage::makeDirectory($destination);
            }

            // URL untuk git clone
            $repoUrl = "https://github.com/$owner/$repo.git";

            echo "Proses Git clone $repoUrl ke folder $destination\n";
            ProcessService::runProcess(['git', 'clone', $repoUrl, $folderName], $destination);
            echo "Proses Git clone $folderName selesai\n";
        } catch (Exception $e) {
            throw new Exception("Clone Repositori gagal: " . $e->getMessage());
        }
    }

    public function cloneWithTag(RepositoryEnum $repositoryEnum,  string $destination, string $tag, $renameOldFolder = null, $update = true): void
    {
        $folderName = $repositoryEnum->getFolderName();
        $folderPath = $destination . DIRECTORY_SEPARATOR . $folderName;
        $filservice = new FileService();

        // Validasi folder tujuan
        if (is_dir($folderPath)) {
            if ($update == false) {
                throw new \InvalidArgumentException("Tujuan path sudah ada: $folderName");
            }
            $tmpFolder =  $filservice->renameToTmp($folderPath);
        }
         try {
            $owner = $repositoryEnum->getOwner();
            $repo = $repositoryEnum->getRepo();
            $folderName = $repositoryEnum->getFolderName();

            $repoUrl = "https://github.com/$owner/$repo.git";

            // Jalankan perintah `git clone`
            $command = ['git', 'clone', '--branch', $tag, '--depth', '1', $repoUrl, $folderName];
            ProcessService::runProcess($command, $destination);

            // Validasi apakah folder berhasil dibuat
            if (!is_dir($destination . DIRECTORY_SEPARATOR . $folderName)) {
                throw new \RuntimeException("Gagal mengkloning repositori ke: $folderName");
            }

            if (isset($tmpFolder)) {
                if ($renameOldFolder) {
                    $renameRealPath = $destination . DIRECTORY_SEPARATOR . $renameOldFolder;
                    $filservice->renameFolder($tmpFolder, $renameRealPath );
                }else{
                    $filservice->deleteFolder($tmpFolder);
                }

            }
        } catch (Exception $e) {
            if (isset($tmpFolder)) {
                $filservice->restoreFromTmp($tmpFolder, $folderPath);
            }

            throw new Exception($e->getMessage());
        }
    }

    /**
     * Ambil data rilis terakhir dari Github API.
     *
     * @param RepositoryEnum $repositoryEnum Enum repository yang akan diambil.
     * @return array Array of last release objects.
     * @throws Exception Jika terjadi kesalahan saat proses memuat data rilis.
     */
    public function getLastRelease(RepositoryEnum $repositoryEnum): array
    {
        // Ambil owner dan repo dari RepositoryEnum
        $owner = $repositoryEnum->getOwner();
        $repo = $repositoryEnum->getRepo();

        // Cek Token Github
        cek_token_github();

        try {
            $url = "{$this->apiBaseUrl}/$owner/$repo/releases/latest";

            $response = Http::withHeaders([
                'Accept' => 'application/vnd.github.v3+json',
                'Authorization' => "token " . config('siappakai.git.token'),
            ])->get($url);

            if ($response->failed()) {
                throw new Exception("Gagal mengambil rilis terakhir: " . $response->body());
            }

            return $response->json();
        } catch (Exception $e) {
            throw new Exception("Terjadi kesalahan saat mengambil rilis terakhir: " . $e->getMessage() . ', Silakan cek token Github');
        }
    }


    /**
     * Ambil 6 rilis terakhir dari GitHub API.
     *
     * @param RepositoryEnum $repositoryEnum Enum repository yang akan diambil
     * @return array Array of release objects
     */
    public function getLastSixReleases(RepositoryEnum $repositoryEnum): array
    {
        // Ambil owner dan repo dari RepositoryEnum
        $owner = $repositoryEnum->getOwner();
        $repo = $repositoryEnum->getRepo();

        try {
            $url = "{$this->apiBaseUrl}/$owner/$repo/releases";
            $response = Http::get($url);

            if ($response->failed()) {
                throw new Exception("Gagal mengambil rilis: " . $response->body());
            }

            $releases = $response->json();

            // Ambil maksimum 6 rilis terakhir
            return array_slice($releases, 0, 6);
        } catch (Exception $e) {
            throw new Exception("Terjadi kesalahan saat mengambil rilis: " . $e->getMessage());
        }
    }
}
