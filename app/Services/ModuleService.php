<?php

namespace App\Services;

use App\Models\Pelanggan;
use App\Services\ZipService;
use App\Enums\StatusLangganan;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Symfony\Component\Process\Process;
use Illuminate\Support\Facades\Storage;
use App\Contracts\FileExtractorInterface;
use App\Contracts\FileDownloaderInterface;
use App\Contracts\VersionCheckerInterface;
use App\Services\ServerEnvironmentService;

class ModuleService implements VersionCheckerInterface, FileDownloaderInterface, FileExtractorInterface
{
    protected $apiUrl;
    protected $authToken;

    /**
     * Constructor.
     *
     * Menginisialisasi instance dari class ini dengan URL API
     * dan token premium yang diambil dari database pelanggan.
     *
     * Jika token premium tidak ditemukan maka akan dilempar
     * log dengan pesan "Tidak ada token premium".
     */
    public function __construct()
    {
        $Environment = new ServerEnvironmentService();
        $server_layanan = $Environment->getServerLayanan();

        $this->apiUrl = $server_layanan . '/api/v1/modules';
        $pelanggan = Pelanggan::select(['token_premium'])->orderBy('tgl_akhir_saas', 'desc')->whereNotNull('token_premium')->first();
        if ($pelanggan->token_premium) {
            $this->authToken =  $pelanggan->token_premium;
        } else {
            Log::info("message : Tidak ada token premium");
            return;
        }
    }

    /**
     * Mengambil versi modul yang terinstall berdasarkan nama modul.
     *
     * @param string $moduleName Nama modul yang akan diambil versinya.
     *
     * @return string Versi modul yang terinstall.
     *
     * @throws \RuntimeException Jika key 'version' tidak ditemukan di file module.json.
     */
    public function getCurrentVersion(string $moduleName): string
    {
        $rootPath = config('siappakai.root.folder');
        $modulePath = "{$rootPath}/Modules/{$moduleName}/module.json";

        if (!file_exists($modulePath)) {
            return "0";
        }

        $moduleData = json_decode(file_get_contents($modulePath), true);

        if (!isset($moduleData['version'])) {
            Log::error("Key 'version' tidak ditemukan di file module.json untuk modul {$moduleName}");
            throw new \RuntimeException("Key 'version' tidak ditemukan di file module.json untuk modul {$moduleName}");
        }

        return $moduleData['version'];
    }

    /**
     * Mengambil data versi modul terbaru dari API.
     *
     * @return array
     *
     * @throws \RuntimeException Jika gagal mengambil data dari API.
     */
    public function getModuleVersion(): array
    {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->authToken,
        ])->get($this->apiUrl, [
            'page' => 1,
            'tipe' => 'gratis',
        ]);

        if ($response->failed()) {
            throw new \RuntimeException('Failed to fetch the latest version from API.');
        }

        // Cek apakah respons API berhasil (status 200)
        if ($response->failed()) {
            throw new \RuntimeException('Failed to fetch the latest version from API.');
        }

        // Pastikan data yang diterima sesuai dengan format yang diharapkan
        $responseData = $response->json();

        if (!isset($responseData['data']) || empty($responseData['data'])) {
            throw new \RuntimeException('No data found in API response.');
        }

        return $responseData['data'];
    }

    /**
     * Mendownload file dari URL yang diberikan dan menyimpannya ke tujuan yang diberikan.
     *
     * @param string $url URL yang akan di-download.
     * @param string $destination Path tujuan untuk menyimpan file yang di-download.
     */
    public function download($url, $destination, $filename): void
    {
        /**
         * Pastikan direktori tujuan ada
         */
        if (!File::exists($destination)) {
            File::ensureDirectoryExists($destination, 0755, true);
        }


        // Check if the file already exists and delete it if it does
        if (Storage::exists($destination . $filename)) {
            Storage::delete($destination . $filename);
            echo "Existing file $destination $filename deleted.\n";
        }

        // Menggunakan facade Http Laravel untuk mengunduh file
        $response = Http::timeout(600)->get($url);

        if ($response->successful()) {
            $fileContent = $response->body();
            // dd(substr($fileContent, 0, 4));

            // Periksa apakah file adalah file ZIP yang valid (menggunakan 4 byte pertama, signature 'PK')
            // if (substr($fileContent, 0, 4) === 'PK\u0003\u0004') {
            // Simpan konten sebagai file ZIP di tujuan
            Storage::put($destination . $filename, $fileContent);
            echo "File ZIP berhasil diunduh dan disimpan ke $destination$filename\n";
            // } else {
            //     echo "Error: File yang diunduh bukan file ZIP yang valid.\n";
            // }
        } else {
            echo "Error: Tidak dapat mengunduh file. HTTP Status: " . $response->status() . "\n";
        }
    }

    /**
     * Mengekstrak file ZIP yang berisi modul Opensid premium.
     *
     * @param string $filePath Path file ZIP yang akan diekstrak.
     * @param string $modulName Nama modul yang akan diekstrak.
     */
    public function extract(string $filePath, string $modulName): void
    {
        $rootPath = config('siappakai.root.folder');
        $fullFilePath = storage_path("app/" . $filePath);
        
        $masterPremium = $rootPath . 'master-opensid' . DIRECTORY_SEPARATOR . 'premium' . DIRECTORY_SEPARATOR;
        $tempExtractPath = $masterPremium . 'Modules' . DIRECTORY_SEPARATOR . 'temp_extract';
        $folderModul =  $masterPremium . 'Modules' . DIRECTORY_SEPARATOR . $modulName . DIRECTORY_SEPARATOR;

        if (!File::exists($tempExtractPath)) {
            File::ensureDirectoryExists($tempExtractPath, 0755, true, true);
        }

        $zipservice = new ZipService();
        $zipservice->unzipFile($fullFilePath, $tempExtractPath);

        // hapus folder modul yang lama
        if (File::exists($folderModul)) {
            Storage::delete($folderModul);
        }

        // Pindahkan semua file dari folder di dalam ZIP ke $folderModul
        $extractedFolder = File::directories($tempExtractPath)[0] ?? null; // Ambil folder pertama dari hasil ekstraksi
        if ($extractedFolder) {
            File::ensureDirectoryExists($folderModul); // Membuat folder jika belum ada
            File::moveDirectory($extractedFolder, $folderModul);
        }

        // Hapus folder sementara
        File::deleteDirectory($tempExtractPath);
    }

    /**
     * Menginstall / Mengupdatemodul terbaru untuk setiap pelanggan yang aktif
     *
     * @return void
     */
    function install(): void
    {
        $modulservice = $this;
        $daftarModul = $modulservice->getModuleVersion();
        $pelanggan = Pelanggan::where('status_langganan_saas', StatusLangganan::AKTIF)->get();

        $rootMultisite = config('siappakai.root.folder_multisite');
        $fileservice = new FileService();
        try {
            foreach ($daftarModul as $modul) {
                $versi =  $modulservice->getCurrentVersion($modul['name']);

                $filePath = "temp/";
                $filename = $modul['name'] . ".zip";

                if ($versi != $modul['version']) {
                    $modulservice->download($modul['url'], $filePath, $filename);
                    $modulservice->extract($filePath . $filename, $modul['name']);
                }
            }

            foreach ($pelanggan as $value) {
                $folder = $rootMultisite . $value->kode_desa_without_dot;
                ProcessService::PasangModul($folder, $modul['name']);
                $fileservice->hapusChace($folder);
            }
        } catch (\Exception $e) {
            echo  $e->getMessage();
            Log::error($e);
        }
    }
}
