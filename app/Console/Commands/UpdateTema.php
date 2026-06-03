<?php

namespace App\Console\Commands;

use App\Http\Controllers\Helpers\AttributeSiapPakaiController;
use App\Http\Controllers\Helpers\CommandController;
use App\Http\Controllers\Helpers\TemaController;
use App\Services\RecycleBinService;
use App\Services\ServerLayananService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Throwable;
use ZipArchive;

class UpdateTema extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'siappakai:update-tema';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Perbarui tema dengan paket terbaru dari layanan OpenDesa';

    private AttributeSiapPakaiController $att;

    private CommandController $comm;

    private TemaController $temas;

    private readonly RecycleBinService $recycleBinService;

    private array $marketThemes = [];

    /**
     * Create a new command instance.
     */
    public function __construct(
        AttributeSiapPakaiController $att,
        CommandController $comm,
        TemaController $temas,
        RecycleBinService $recycleBinService
    ) {
        parent::__construct();
        $this->att = $att;
        $this->comm = $comm;
        $this->temas = $temas;
        $this->recycleBinService = $recycleBinService;
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->marketThemes = $this->fetchMarketplaceThemes();

        if (empty($this->marketThemes)) {
            $this->error('Data tema dari layanan kosong atau tidak dapat dimuat.');

            return Command::FAILURE;
        }

        $this->updateThemeCollection($this->temas->JenisTemaPro(), $this->att->getTemaProFolder());
        $this->updateThemeCollection($this->temas->JenisTemaGratis(), $this->att->getTemaGratisFolder());

        return Command::SUCCESS;
    }

    private function updateThemeCollection(array $collection, string $baseFolder): void
    {
        foreach ($collection as $item) {
            $folderTema = $baseFolder.DIRECTORY_SEPARATOR.$item['tema'];

            if (! file_exists($folderTema)) {
                $this->warn('Peringatan: tema '.$item['tema'].' tidak ditemukan di '.$folderTema);

                continue;
            }

            $this->updateTemaFromMarketplace($item, $folderTema);
        }
    }

    private function updateTemaFromMarketplace(array $tema, string $folderTema): void
    {
        $remoteTema = $this->findRemoteTheme($tema);

        if (! $remoteTema) {
            $this->warn('Tema '.$tema['tema'].' tidak tersedia di layanan. Melewati pembaruan.');

            return;
        }

        $oldVersion = $this->getLocalThemeVersion($folderTema);
        $newVersion = $remoteTema['version'] ?? 'terbaru';

        $this->info("Memperbarui tema {$tema['tema']}: [{$oldVersion}] -> [{$newVersion}]");

        $zipPath = $this->downloadThemeArchive($remoteTema['url'] ?? '', $tema['tema']);

        if (! $zipPath) {
            $this->warn('Gagal mengunduh arsip untuk tema '.$tema['tema'].'.');

            return;
        }

        $extractPath = $this->extractArchive($zipPath);

        if (! $extractPath) {
            $this->warn('Gagal mengekstrak arsip untuk tema '.$tema['tema'].'.');

            return;
        }

        $sourcePath = $this->resolveExtractedSource($extractPath);

        $this->recycleBinService->moveToRecycleBin($folderTema);
        $this->comm->makeDirectory($folderTema);
        $this->comm->copyDirectory($sourcePath.DIRECTORY_SEPARATOR.'.', $folderTema);
        File::deleteDirectory($extractPath);

        try {
            $this->comm->chownCommand($folderTema);
        } catch (Throwable $th) {
            $this->warn("Peringatan: Gagal mengubah kepemilikan direktori {$folderTema}.");
        }

        $currentVersion = $this->getLocalThemeVersion($folderTema);
        $this->info("Berhasil memperbarui tema {$tema['tema']}. Versi saat ini: {$currentVersion}");
    }

    private function fetchMarketplaceThemes(): array
    {
        $token = config('siappakai.themes.token');

        if (empty($token)) {
            $this->error('Environment LAYANAN_THEME_API_TOKEN belum diatur.');

            return [];
        }

        $endpoint = $this->resolveThemeEndpoint();

        try {
            $response = Http::timeout(120)
                ->withHeaders([
                    'Authorization' => 'Bearer '.$token,
                    'Accept' => 'application/json',
                    'User-Agent' => 'Dasbor-SiapPakai',
                ])
                ->get($endpoint)
                ->throw();

            return $response->json('data', []);
        } catch (Throwable $th) {
            $this->error('Gagal mengambil daftar tema: '.$th->getMessage());

            return [];
        }
    }

    private function resolveThemeEndpoint(): string
    {
        $endpoint = config('siappakai.themes.endpoint', '/api/v1/themes');

        if (Str::startsWith($endpoint, ['http://', 'https://'])) {
            return $endpoint;
        }

        return rtrim(ServerLayananService::getUrl(), '/').'/'.ltrim($endpoint, '/');
    }

    private function findRemoteTheme(array $localTema): ?array
    {
        $slug = $this->normalizeThemeIdentifier($localTema['tema'] ?? '');

        foreach ($this->marketThemes as $remoteTema) {
            if ($this->isMatchingTheme($slug, $localTema, $remoteTema)) {
                return $remoteTema;
            }
        }

        return null;
    }

    private function isMatchingTheme(string $slug, array $localTema, array $remoteTema): bool
    {
        $candidates = array_filter([
            $remoteTema['alias'] ?? null,
            $remoteTema['name'] ?? null,
            $remoteTema['keywords'] ?? null,
            $remoteTema['repo'] ?? null,
        ]);

        $bestMatch = false;

        foreach ($candidates as $value) {
            $normalized = $this->normalizeThemeIdentifier($value);

            if ($normalized === '') {
                continue;
            }

            if ($normalized === $slug) {
                return true;
            }

            if (str_contains($normalized, $slug)) {
                $bestMatch = true;
            }
        }

        return $bestMatch;
    }

    private function normalizeThemeIdentifier(?string $value): string
    {
        if (! $value) {
            return '';
        }

        return (string) Str::of($value)
            ->lower()
            ->replace(['tema-', 'tema_', 'tema'], ' ')
            ->squish()
            ->replace(' ', '-')
            ->trim('-');
    }

    private function downloadThemeArchive(string $url, string $slug): ?string
    {
        if (empty($url)) {
            return null;
        }

        $tempDir = storage_path('app/theme-updates');
        File::ensureDirectoryExists($tempDir);

        $filePath = $tempDir.DIRECTORY_SEPARATOR.Str::slug($slug.'-'.Str::uuid()).'.zip';

        try {
            $response = Http::timeout(300)
                ->withHeaders([
                    'Accept' => '*/*',
                    'User-Agent' => 'Dasbor-SiapPakai',
                ])
                ->get($url);

            if (! $response->successful()) {
                $this->error("Gagal mengunduh tema {$slug} : HTTP request returned status code {$response->status()}");

                return null;
            }

            file_put_contents($filePath, $response->body());
        } catch (Throwable $th) {
            $this->error('Gagal mengunduh tema '.$slug.' : '.$th->getMessage());

            return null;
        }

        return $filePath;
    }

    private function extractArchive(string $zipPath): ?string
    {
        $extractPath = storage_path('app/theme-updates/'.pathinfo($zipPath, PATHINFO_FILENAME));
        File::ensureDirectoryExists($extractPath);

        $realExtractPath = realpath($extractPath);

        $zip = new ZipArchive;

        if ($zip->open($zipPath) !== true) {
            $this->error('Tidak dapat membuka arsip tema: '.$zipPath);
            File::delete($zipPath);

            return null;
        }

        // Validasi setiap entry untuk mencegah Zip Slip (path traversal)
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $entry = $zip->getNameIndex($i);

            if ($entry === false) {
                continue;
            }

            // Tolak entry dengan path absolut atau mengandung komponen ".."
            if (str_starts_with($entry, '/') || str_starts_with($entry, '\\') || preg_match('/(?:^|[\\/])\.\.(?:[\\/]|$)/', $entry)) {
                $this->error('Arsip tema ditolak karena mengandung entri berbahaya: '.$entry);
                $this->cleanupFailedExtraction($zip, $zipPath, $extractPath);

                return null;
            }

            // Tolak entry yang mengandung drive letter Windows (mis. C:)
            if (preg_match('/^[A-Za-z]:/', $entry)) {
                $this->error('Arsip tema ditolak karena mengandung entri berbahaya: '.$entry);
                $this->cleanupFailedExtraction($zip, $zipPath, $extractPath);

                return null;
            }

            // Pastikan path yang dinormalisasi berada di dalam folder target
            $normalizedEntry = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $entry);
            $targetPath = $realExtractPath.DIRECTORY_SEPARATOR.$normalizedEntry;
            $normalizedTarget = $this->normalizePath($targetPath);

            if (! str_starts_with($normalizedTarget, $realExtractPath.DIRECTORY_SEPARATOR) && $normalizedTarget !== $realExtractPath) {
                $this->error('Arsip tema ditolak karena entri berada di luar direktori target: '.$entry);
                $this->cleanupFailedExtraction($zip, $zipPath, $extractPath);

                return null;
            }
        }

        $zip->extractTo($extractPath);
        $zip->close();
        File::delete($zipPath);

        return $extractPath;
    }

    /**
     * Normalisasi path tanpa bergantung pada keberadaan file/folder di filesystem.
     */
    private function normalizePath(string $path): string
    {
        $parts = explode(DIRECTORY_SEPARATOR, str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $path));
        $normalized = [];

        foreach ($parts as $part) {
            if ($part === '' || $part === '.') {
                continue;
            }

            if ($part === '..') {
                array_pop($normalized);
            } else {
                $normalized[] = $part;
            }
        }

        $result = implode(DIRECTORY_SEPARATOR, $normalized);

        // Kembalikan leading separator untuk path absolut di Unix
        if (str_starts_with($path, DIRECTORY_SEPARATOR)) {
            $result = DIRECTORY_SEPARATOR.$result;
        }

        return $result;
    }

    /**
     * Bersihkan file sementara saat ekstraksi gagal.
     */
    private function cleanupFailedExtraction(ZipArchive $zip, string $zipPath, string $extractPath): void
    {
        $zip->close();
        File::delete($zipPath);
        File::deleteDirectory($extractPath);
    }

    private function resolveExtractedSource(string $extractPath): string
    {
        $items = array_values(array_filter(scandir($extractPath), fn ($item) => ! in_array($item, ['.', '..'])));

        // Cari folder yang berisi template.php atau composer.json secara rekursif (maks 2 level)
        foreach ($items as $item) {
            $path = $extractPath.DIRECTORY_SEPARATOR.$item;
            if (is_dir($path)) {
                if (file_exists($path.DIRECTORY_SEPARATOR.'template.php') || file_exists($path.DIRECTORY_SEPARATOR.'composer.json')) {
                    return $path;
                }

                // Cek satu level lebih dalam
                $subItems = array_values(array_filter(scandir($path), fn ($i) => ! in_array($i, ['.', '..'])));
                foreach ($subItems as $subItem) {
                    $subPath = $path.DIRECTORY_SEPARATOR.$subItem;
                    if (is_dir($subPath) && (file_exists($subPath.DIRECTORY_SEPARATOR.'template.php') || file_exists($subPath.DIRECTORY_SEPARATOR.'composer.json'))) {
                        return $subPath;
                    }
                }
            }
        }

        // Fallback: jika hanya ada satu folder, masuk ke dalamnya meskipun tidak ada file kunci
        if (count($items) === 1) {
            $single = $extractPath.DIRECTORY_SEPARATOR.$items[0];
            if (is_dir($single)) {
                return $single;
            }
        }

        return $extractPath;
    }

    private function getLocalThemeVersion(string $folderTema): string
    {
        $version = $this->searchVersionInFolder($folderTema);

        if ($version === 'tidak diketahui') {
            // Jika tidak ditemukan di root, cari di subfolder (antisipasi kegagalan resolve struktur saat copy)
            if (is_dir($folderTema)) {
                $items = array_values(array_filter(scandir($folderTema), fn ($item) => ! in_array($item, ['.', '..'])));
                foreach ($items as $item) {
                    $path = $folderTema.DIRECTORY_SEPARATOR.$item;
                    if (is_dir($path)) {
                        $subVersion = $this->searchVersionInFolder($path);
                        if ($subVersion !== 'tidak diketahui') {
                            return $subVersion;
                        }
                    }
                }
            }
        }

        return $version;
    }

    private function searchVersionInFolder(string $folder): string
    {
        // Cari di globals.blade.php
        $globalsPath = $folder.DIRECTORY_SEPARATOR.'resources'.DIRECTORY_SEPARATOR.'views'.DIRECTORY_SEPARATOR.'commons'.DIRECTORY_SEPARATOR.'globals.blade.php';
        if (file_exists($globalsPath)) {
            $content = file_get_contents($globalsPath);
            if (preg_match("/\\\$theme_version\s*=\s*['\"]([^'\"]+)['\"]/", $content, $matches)) {
                return $matches[1];
            }
        }

        // Cari di composer.json
        $composerPath = $folder.DIRECTORY_SEPARATOR.'composer.json';
        if (file_exists($composerPath)) {
            $content = file_get_contents($composerPath);
            $composer = json_decode($content, true);
            if (isset($composer['version'])) {
                return $composer['version'];
            }
        }

        return 'tidak diketahui';
    }
}
