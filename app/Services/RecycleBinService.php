<?php

namespace App\Services;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Carbon;

class RecycleBinService
{
    protected string $recycleBinPath;

    /**
     * Konstruktor untuk RecycleBinService
     */
    public function __construct()
    {
        $this->recycleBinPath = storage_path('app/recycleBin');
        $this->ensureRecycleBinExists();
    }

    /**
     * Memastikan folder recycle bin exists
     */
    protected function ensureRecycleBinExists(): void
    {
        if (!File::exists($this->recycleBinPath)) {
            File::makeDirectory($this->recycleBinPath, 0755, true);
            Log::info('RecycleBin folder created: ' . $this->recycleBinPath);
        }
    }

    /**
     * Pindahkan file atau folder ke recycle bin
     * 
     * @param string $sourcePath Path file/folder yang akan dipindahkan
     * @return string Path file/folder di recycle bin
     * @throws \Exception Jika operasi gagal
     */
    public function moveToRecycleBin(string $sourcePath): string
    {
        if (!File::exists($sourcePath)) {
            throw new \Exception("File atau folder tidak ditemukan: {$sourcePath}");
        }

        // Buat nama unik untuk item di recycle bin
        $timestamp = now()->format('Y-m-d_H-i-s');
        $basename = basename($sourcePath);
        $recycleBinItemPath = $this->recycleBinPath . DIRECTORY_SEPARATOR . $timestamp . '_' . $basename;

        // Jika nama sudah ada, tambahkan suffix
        $counter = 1;
        $originalRecycleBinItemPath = $recycleBinItemPath;
        while (File::exists($recycleBinItemPath)) {
            $recycleBinItemPath = $originalRecycleBinItemPath . '_' . $counter;
            $counter++;
        }

        try {
            // Copy file/folder ke recycle bin
            if (File::isDirectory($sourcePath)) {
                File::copyDirectory($sourcePath, $recycleBinItemPath);
            } else {
                File::copy($sourcePath, $recycleBinItemPath);
            }

            // Hapus file/folder asli setelah berhasil dicopy
            if (File::isDirectory($sourcePath)) {
                File::deleteDirectory($sourcePath);
            } else {
                File::delete($sourcePath);
            }

            // Simpan metadata
            $this->saveMetadata($recycleBinItemPath, $sourcePath);

            Log::info("Item berhasil dipindahkan ke recycle bin: {$sourcePath} -> {$recycleBinItemPath}");
            
            return $recycleBinItemPath;

        } catch (\Exception $e) {
            // Jika terjadi error, bersihkan file yang mungkin sudah tercopy sebagian
            if (File::exists($recycleBinItemPath)) {
                if (File::isDirectory($recycleBinItemPath)) {
                    File::deleteDirectory($recycleBinItemPath);
                } else {
                    File::delete($recycleBinItemPath);
                }
            }
            
            Log::error("Gagal memindahkan item ke recycle bin: {$sourcePath}. Error: " . $e->getMessage());
            throw new \Exception("Gagal memindahkan item ke recycle bin: " . $e->getMessage());
        }
    }

    /**
     * Simpan metadata untuk item di recycle bin
     */
    protected function saveMetadata(string $recycleBinItemPath, string $originalPath): void
    {
        $metadata = [
            'original_path' => $originalPath,
            'deleted_at' => now()->toISOString(),
            'size' => $this->getItemSize($recycleBinItemPath),
            'type' => File::isDirectory($recycleBinItemPath) ? 'directory' : 'file'
        ];

        $metadataFile = $recycleBinItemPath . '.meta';
        File::put($metadataFile, json_encode($metadata, JSON_PRETTY_PRINT));
    }

    /**
     * Dapatkan ukuran file atau folder
     */
    protected function getItemSize(string $path): int
    {
        if (File::isDirectory($path)) {
            $size = 0;
            $files = File::allFiles($path);
            foreach ($files as $file) {
                $size += $file->getSize();
            }
            return $size;
        } else {
            return File::size($path);
        }
    }

    /**
     * Bersihkan file-file lama dari recycle bin
     * 
     * @param int $daysOld Hapus file yang lebih lama dari berapa hari
     * @return array Statistik pembersihan
     */
    public function cleanOldItems(int $daysOld = 30): array
    {
        $cutoffDate = Carbon::now()->subDays($daysOld);
        $deletedItems = [];
        $totalSize = 0;
        $errorCount = 0;

        if (!File::exists($this->recycleBinPath)) {
            return [
                'deleted_count' => 0,
                'total_size' => 0,
                'error_count' => 0,
                'deleted_items' => []
            ];
        }

        $items = File::files($this->recycleBinPath);
        $directories = File::directories($this->recycleBinPath);

        // Proses file-file
        foreach ($items as $item) {
            $itemPath = $item->getPathname();
            
            // Skip file metadata
            if (str_ends_with($itemPath, '.meta')) {
                continue;
            }

            try {
                $itemDate = $this->getItemDeletedDate($itemPath);
                
                if ($itemDate && $itemDate->lt($cutoffDate)) {
                    $size = $this->getItemSize($itemPath);
                    
                    // Hapus file dan metadata-nya
                    File::delete($itemPath);
                    $metadataFile = $itemPath . '.meta';
                    if (File::exists($metadataFile)) {
                        File::delete($metadataFile);
                    }
                    
                    $deletedItems[] = basename($itemPath);
                    $totalSize += $size;
                }
            } catch (\Exception $e) {
                Log::error("Error processing recycle bin item: {$itemPath}. Error: " . $e->getMessage());
                $errorCount++;
            }
        }

        // Proses direktori
        foreach ($directories as $directory) {
            try {
                $itemDate = $this->getItemDeletedDate($directory);
                
                if ($itemDate && $itemDate->lt($cutoffDate)) {
                    $size = $this->getItemSize($directory);
                    
                    // Hapus direktori dan metadata-nya
                    File::deleteDirectory($directory);
                    $metadataFile = $directory . '.meta';
                    if (File::exists($metadataFile)) {
                        File::delete($metadataFile);
                    }
                    
                    $deletedItems[] = basename($directory);
                    $totalSize += $size;
                }
            } catch (\Exception $e) {
                Log::error("Error processing recycle bin directory: {$directory}. Error: " . $e->getMessage());
                $errorCount++;
            }
        }

        Log::info("Recycle bin cleanup completed. Deleted: " . count($deletedItems) . " items, Size: " . number_format($totalSize) . " bytes");

        return [
            'deleted_count' => count($deletedItems),
            'total_size' => $totalSize,
            'error_count' => $errorCount,
            'deleted_items' => $deletedItems
        ];
    }

    /**
     * Dapatkan tanggal kapan item dihapus
     */
    protected function getItemDeletedDate(string $itemPath): ?Carbon
    {
        $metadataFile = $itemPath . '.meta';
        
        if (File::exists($metadataFile)) {
            try {
                $metadata = json_decode(File::get($metadataFile), true);
                return Carbon::parse($metadata['deleted_at']);
            } catch (\Exception $e) {
                Log::warning("Cannot parse metadata for: {$itemPath}");
            }
        }

        // Fallback ke file creation time jika metadata tidak ada
        try {
            return Carbon::createFromTimestamp(File::lastModified($itemPath));
        } catch (\Exception $e) {
            Log::warning("Cannot get date for: {$itemPath}");
            return null;
        }
    }

    /**
     * Dapatkan informasi recycle bin
     */
    public function getRecycleBinInfo(): array
    {
        if (!File::exists($this->recycleBinPath)) {
            return [
                'total_items' => 0,
                'total_size' => 0,
                'items' => []
            ];
        }

        $items = [];
        $totalSize = 0;
        
        $files = File::files($this->recycleBinPath);
        $directories = File::directories($this->recycleBinPath);

        // Process files
        foreach ($files as $file) {
            $filePath = $file->getPathname();
            
            // Skip metadata files
            if (str_ends_with($filePath, '.meta')) {
                continue;
            }

            $size = $this->getItemSize($filePath);
            $deletedDate = $this->getItemDeletedDate($filePath);
            
            $items[] = [
                'name' => basename($filePath),
                'path' => $filePath,
                'type' => 'file',
                'size' => $size,
                'deleted_at' => $deletedDate ? $deletedDate->toISOString() : null
            ];
            
            $totalSize += $size;
        }

        // Process directories
        foreach ($directories as $directory) {
            $size = $this->getItemSize($directory);
            $deletedDate = $this->getItemDeletedDate($directory);
            
            $items[] = [
                'name' => basename($directory),
                'path' => $directory,
                'type' => 'directory',
                'size' => $size,
                'deleted_at' => $deletedDate ? $deletedDate->toISOString() : null
            ];
            
            $totalSize += $size;
        }

        return [
            'total_items' => count($items),
            'total_size' => $totalSize,
            'items' => $items
        ];
    }
}