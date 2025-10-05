<?php

namespace App\Services;

use Directory;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File;
use Illuminate\Filesystem\Filesystem;
use App\Services\RecycleBinService;

class FileService
{
    protected Filesystem $files;
    protected RecycleBinService $recycleBinService;

    /**
     * Konstruktor. Membuat instance dari class Filesystem.
     */
    public function __construct()
    {
        $this->files = new Filesystem();
        $this->recycleBinService = new RecycleBinService();
    }

    /**
     * Membuat symlink folder.
     *
     * @param string $targetFolder Path folder asli
     * @param string $linkFolder Path symlink yang akan dibuat
     * @return bool True jika berhasil, false jika gagal
     */
    public function createSymlink(string $targetFolder, string $linkFolder): bool
    {
        // Hapus symlink lama jika sudah ada
        if (is_link($linkFolder) || file_exists($linkFolder)) {
            if (!unlink($linkFolder)) {
                return false;
            }
        }
        // Buat symlink baru
        return symlink($targetFolder, $linkFolder);
    }

    /**
     * Replace file dengan file baru.
     *
     * @param string $sourceFile Path file sumber (file baru).
     * @param string $targetFile Path file target (file lama yang akan diganti).
     * @return bool True jika berhasil, false jika gagal.
     * @throws \Exception Jika file sumber tidak ditemukan.
     */
    public function replaceFile(string $sourceFile, string $targetFile): bool
    {
        if (!File::exists($sourceFile)) {
            throw new \Exception("File sumber tidak ditemukan: $sourceFile");
        }
        // Hapus file target ke recycle bin jika ada
        if (File::exists($targetFile)) {
            $this->recycleBinService->moveToRecycleBin($targetFile);
        }
        // Salin file sumber ke target
        return File::copy($sourceFile, $targetFile);
    }

    function replaceFolderContents(string $sourceFolder, string $targetFolder)
    {
        // Pastikan folder sumber dan target ada
        if (!File::exists($sourceFolder)) {
            throw new \Exception("Folder sumber tidak ada: $sourceFolder");
        }

        // Hapus semua isi dari folder target
        File::deleteDirectory($targetFolder, true);

        // Salin semua isi folder sumber ke folder target
        File::copyDirectory($sourceFolder, $targetFolder);

        return "Isi folder berhasil diganti!";
    }

    /**
     * Rename folder di sistem file.
     *
     * @param string $currentPath Path folder saat ini.
     * @param string $newPath Path baru untuk folder.
     * @return bool True jika rename berhasil, false jika folder target sudah ada.
     */
    public function renameFolder(string $currentPath, string $newPath): bool
    {
        // Periksa apakah folder lama ada
        if (!File::exists($currentPath)) {
            return false; // Folder lama tidak ditemukan
        }

        // Jika folder target sudah ada, lewati proses tanpa error
        if (File::exists($newPath)) {
            return true; // Anggap berhasil karena folder target sudah ada
        }

        // Lakukan operasi rename
        return File::move($currentPath, $newPath);
    }

    /**
     * Rename folder ke folder sementara (tmp-[timestamp]) dan kembalikan nama folder sementara.
     *
     * @param string $folderPath Path folder yang akan di-rename.
     * @return string Path folder sementara.
     * @throws \Exception Jika folder tidak ditemukan atau operasi gagal.
     */
    public function renameToTmp(string $folderPath): string
    {
        if (!File::exists($folderPath) || !File::isDirectory($folderPath)) {
            throw new \Exception("Folder tidak ada atau bukan direktori: $folderPath");
        }

        $tmpFolderPath = $folderPath . '-tmp-' . time();

        if (!File::move($folderPath, $tmpFolderPath)) {
            throw new \Exception("Gagal mengganti nama folder ke folder sementara: $tmpFolderPath");
        }

        return $tmpFolderPath;
    }

    /**
     * Kembalikan folder dari folder sementara ke nama asli.
     *
     * @param string $tmpFolderPath Path folder sementara.
     * @param string $originalFolderPath Path folder asli.
     * @return void
     * @throws \Exception Jika operasi gagal.
     */
    public function restoreFromTmp(string $tmpFolderPath, string $originalFolderPath): void
    {
        if (!File::exists($tmpFolderPath) || !File::isDirectory($tmpFolderPath)) {
            throw new \Exception("Folder sementara tidak ada atau bukan direktori: $tmpFolderPath");
        }
        // dd(!File::move($tmpFolderPath, $originalFolderPath));
        if (!File::move($tmpFolderPath, $originalFolderPath)) {
            throw new \Exception("Gagal memulihkan folder dari folder sementara: $tmpFolderPath");
        }
    }

    /**
     * Hapus folder sementara jika sudah tidak diperlukan.
     *
     * @param string $tmpFolderPath Path folder sementara.
     * @return void
     * @throws \Exception Jika operasi gagal.
     */
    public function deleteFolder(string $tmpFolderPath): void
    {
        if (!File::exists($tmpFolderPath) || !File::isDirectory($tmpFolderPath)) {
            return;
        }

        if (!File::deleteDirectory($tmpFolderPath)) {
            throw new \Exception("Gagal menghapus folder sementara: $tmpFolderPath");
        }
    }

    /**
     * Hapus file atau folder ke recycle bin (soft delete)
     *
     * @param string $path Path file atau folder yang akan dihapus
     * @return string Path item di recycle bin
     * @throws \Exception Jika operasi gagal
     */
    public function deleteToRecycleBin(string $path): string
    {
        return $this->recycleBinService->moveToRecycleBin($path);
    }

    /**
     * Hapus file atau folder secara permanent (hard delete)
     * Gunakan dengan hati-hati!
     *
     * @param string $path Path file atau folder yang akan dihapus permanent
     * @return bool True jika berhasil
     */
    public function deletePermanent(string $path): bool
    {
        if (!File::exists($path)) {
            return false;
        }

        try {
            if (File::isDirectory($path)) {
                return File::deleteDirectory($path);
            } else {
                return File::delete($path);
            }
        } catch (\Exception $e) {
            Log::error("Gagal menghapus permanent: {$path}. Error: " . $e->getMessage());
            throw new \Exception("Gagal menghapus permanent: " . $e->getMessage());
        }
    }

    /**
     * Hapus folder dengan awalan tertentu di direktori.
     *
     * @param string $directory Direktori tempat pencarian folder.
     * @param string $prefix Awalan nama folder yang akan dihapus.
     * @return array Daftar folder yang berhasil dihapus.
     */
    public function deleteFoldersByPrefix(string $directory, string $prefix): array
    {
        // Pastikan direktori ada
        if (!File::exists($directory)) {
            throw new \Exception("Direktori tidak ada: $directory");
        }

        // Ambil daftar semua folder di dalam direktori
        $folders = File::directories($directory);

        // Folder yang berhasil dihapus
        $deletedFolders = [];

        foreach ($folders as $folder) {
            // Dapatkan nama folder
            $folderName = basename($folder);

            // Periksa apakah folder memiliki awalan tertentu
            if (str_starts_with($folderName, $prefix)) {
                // Hapus folder
                File::deleteDirectory($folder);
                $deletedFolders[] = $folderName;
            }
        }

        return $deletedFolders;
    }

    /**
     * Hapus semua symlink di dalam folder tertentu.
     *
     * @param string $directory Direktori tempat pencarian symlink.
     * @return array Daftar symlink yang berhasil dihapus.
     * @throws \Exception Jika direktori tidak ditemukan.
     */
    public function deleteAllSymlinks(string $directory): array
    {
        // Pastikan direktori ada
        if (!File::exists($directory)) {
            return [];
        }

        // Ambil semua file dan folder di direktori
        $items = File::allFiles($directory, true);

        // Daftar symlink yang berhasil dihapus
        $deletedSymlinks = [];

        foreach ($items as $item) {
            $filePath = $item->getPathname();

            // Periksa apakah item adalah symlink
            if (is_link($filePath)) {
                unlink($filePath); // Hapus symlink
                $deletedSymlinks[] = $filePath;
            }
        }

        return $deletedSymlinks;
    }

    /**
     * Hapus semua symlink di dalam folder tertentu.
     *
     * @param string $directory Direktori tempat pencarian symlink.
     * @return array Daftar symlink yang berhasil dihapus.
     * @throws \Exception Jika direktori tidak ditemukan.
     */
    public function deleteSymlinks(string $directory): array
    {
        // Pastikan direktori ada
        if (!File::exists($directory)) {
            return [];
        }

        // Ambil semua file dan folder di direktori
        $items = File::files($directory, true);

        // Daftar symlink yang berhasil dihapus
        $deletedSymlinks = [];

        foreach ($items as $item) {
            $filePath = $item->getPathname();

            // Periksa apakah item adalah symlink
            if (is_link($filePath)) {
                unlink($filePath); // Hapus symlink
                $deletedSymlinks[] = $filePath;
            }
        }

        return $deletedSymlinks;
    }


    /**
     * Proses template file dengan menggantikan placeholder tertentu dengan nilai yang sesuai.
     *
     * @param string $templatePath Path file template yang akan di-proses.
     * @param string $destinationPath Path file yang akan dihasilkan setelah di-proses.
     * @param array $replacements Daftar placeholder beserta nilai yang akan digantikan.
     * @return bool True jika proses berhasil, false jika gagal.
     * @throws \Exception Jika template file tidak ditemukan.
     */
    public function processTemplate(string $templatePath, string $destinationPath, array $replacements): bool
    {
        if (!$this->files->exists($templatePath)) {
            throw new \Exception("File template tidak ditemukan: {$templatePath}");
        }

        $templateContent = $this->files->get($templatePath);

        $processedContent = str_replace(
            array_keys($replacements),
            array_values($replacements),
            $templateContent
        );

        if (file_exists($destinationPath)) {
            $this->files->replace($destinationPath, $processedContent);
            return true;
        } else {
            return false;
        }
    }

    public function hapusChace(string $path): void
    {
        $folderFramework = $path . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'framework';
        if (!$this->files->exists($folderFramework)) {
            throw new \Exception("Folder Chace tidak ditemukan: {$folderFramework}");
        }

        File::deleteDirectory($folderFramework);
        echo "Folder Chace berhasil dihapus.";
    }

    /**
     * Hapus symlink file/folder bermasalah di direktori tertentu
     */
    public function hapusSymlinkBermasalah($files, $dirs, $logPrefix = '')
    {
        // Hapus file symlink yang rusak
        foreach ($files as $file) {
            $path = method_exists($file, 'getPathname') ? $file->getPathname() : $file;
            if (is_link($path) && !file_exists(realpath($path))) {
                File::delete($path);
                Log::warning("Symlink file bermasalah dihapus dari {$logPrefix}: " . $path);
            }
        }
        // Hapus folder symlink yang rusak
        foreach ($dirs as $dir) {
            if (is_link($dir) && !file_exists(realpath($dir))) {
                File::delete($dir);
                Log::warning("Symlink folder bermasalah dihapus dari {$logPrefix}: " . $dir);
            }
        }
        // Hapus file symlink yang mengandung //
        foreach ($files as $file) {
            $path = method_exists($file, 'getPathname') ? $file->getPathname() : $file;
            if (is_link($path) && strpos($path, '//') !== false) {
                File::delete($path);
                Log::warning("Symlink file dengan path mengandung // dihapus dari {$logPrefix}: " . $path);
            }
        }
        // Hapus folder symlink yang mengandung //
        foreach ($dirs as $dir) {
            if (is_link($dir) && strpos($dir, '//') !== false) {
                File::delete($dir);
                Log::warning("Symlink folder dengan path mengandung // dihapus dari {$logPrefix}: " . $dir);
            }
        }
    }

    /**
     * Hapus symlink .htaccess jika ada
     */
    public function hapusSymlinkHtaccess($path, $logPrefix = '')
    {
        $htaccess = $path . DIRECTORY_SEPARATOR . '.htaccess';
        if (is_link($htaccess)) {
            File::delete($htaccess);
            Log::warning("Symlink .htaccess dihapus dari {$logPrefix}: " . $htaccess);
        }
    }
}
