<?php

namespace App\Services;

use App\Models\Aplikasi;
use Symfony\Component\Process\Process;

final class ProcessService
{
    public static function runProcess(array $command, $folder, $message = null, $env = null)
    {
        echo "$message";
        $process = new Process($command, $folder, $env);
        $process->setTimeout(null);
        $process->run(function ($type, $buffer)  use ($command) {
            if (Process::ERR === $type) {
                echo "Proses: $buffer\n";
            } else {
                echo "Output Proses: $buffer\n";
            }
        });

        if (!$process->isSuccessful()) {
            echo "Proses gagal dijalankan.\n";
            echo "Command yang digunakan: " . implode(' ', $command) . "\n";
            echo "Error Code: " . $process->getExitCode() . "\n";
            echo "Error Message: " . $process->getErrorOutput() . "\n";
        }

        return $process;
    }

    public static function createSymlink(string $directory_from, string $directory_to): void
    {
        // Cek jika symlink sudah ada
        if (is_link($directory_to)) {
            echo "Symlink lama ditemukan, menghapus symlink yang lama...\n";

            // Hapus symlink lama
            unlink($directory_to);
            echo "Symlink lama berhasil dihapus.\n";
        }

        // Menyusun perintah 'ln -s' ke dalam array
        $command = ['sudo', 'ln', '-s', $directory_from, $directory_to];

        // Menjalankan perintah dengan menggunakan runProcess
        self::runProcess($command, base_path());
        echo "Symlink berhasil dibuat.\n";
    }

    public static function copyFile(string $file_from, string $file_to): void
    {
        // Periksa apakah file sumber ada
        if (!file_exists($file_from)) {
            echo "File sumber tidak ditemukan: $file_from\n";
            return;
        }

        // Cek apakah file tujuan sudah ada
        if (file_exists($file_to)) {
            echo "File tujuan sudah ada, akan ditimpa: $file_to\n";
        }

        // Menyusun perintah copy file dengan opsi force untuk menimpa file yang ada
        $command = ['sudo', 'cp', '-f', $file_from, $file_to];

        // Menjalankan perintah dengan runProcess
        self::runProcess($command, base_path(), "Menyalin file dari $file_from ke $file_to...\n");
    }

    public static function gitSafeDirectori(string $folderSite): void
    {
        if (!is_dir($folderSite)) {
            echo "Folder site tidak ditemukan: $folderSite\n";
            return;
        }

        // Menyusun perintah untuk menambahkan folder ke git safe directory
        $command = ['sudo', 'git', 'config', '--global', '--add', 'safe.directory', $folderSite];

        // Menjalankan perintah dengan runProcess
        self::runProcess($command, base_path(), "Mengonfigurasi Git untuk folder $folderSite...\n");
    }

    public static function aturKepemilikanDirektori(string $directory): void
    {
        // Validasi apakah direktori ada
        if (!is_dir($directory)) {
            echo "Direktori tidak ditemukan: $directory\n";
            return;
        }

        // Mendapatkan izin dari pengaturan aplikasi
        $permission = Aplikasi::pengaturan_aplikasi()['permission'] ?? 'www-data';

        // Menyusun perintah chown
        $command = ['sudo', 'chown', '-R', "$permission:$permission", $directory];

        // Menjalankan perintah dengan runProcess
        self::runProcess($command, base_path(), "Mengubah kepemilikan direktori: $directory\n");
        echo "Kepemilikan direktori berhasil diubah.\n";
        // Reload Apache jika RELOAD_APACHE diatur
        if (env('RELOAD_APACHE', false)) {
            self::reloadApache();
        }
    }

    public static function aturPermision(string $directory): void
    {
        // Validasi apakah direktori ada
        if (!is_dir($directory)) {
            echo "Direktori tidak ditemukan: $directory\n";
            return;
        }

        // Menyusun perintah chown
        $command = ['sudo', 'chmod', '-R', "775", $directory];
    }

    private function reloadApache(): void
    {
        // Menyusun perintah reload Apache
        $command = ['sudo', 'systemctl', 'reload', 'apache2'];

        // Menjalankan perintah dengan runProcess
        self::runProcess($command, base_path(), "Memuat ulang konfigurasi Apache...\n");
        echo "Konfigurasi Apache berhasil dimuat ulang.\n";
    }

    public static function BuatFolder(string $directory): void
    {
        // Periksa apakah direktori sudah ada
        if (!file_exists($directory)) {
            // Menyusun perintah mkdir untuk membuat folder
            $command = ['sudo', 'mkdir', $directory];
            // Buat direktori menggunakan perintah shell
            self::runProcess($command, base_path(), "Membuat direktori : $directory\n");
            echo "Direktori berhasil dibuat.\n";
        }

        // Atur kepemilikan direktori jika parameter permission diberikan
        self::aturKepemilikanDirektori($directory);
    }

    public static function PasangModul(string $directory,string $namaModul): void
    {

        if (!file_exists($directory)) {

            $command = ['sudo', 'php', 'index.php', 'modul', 'pasang', $namaModul];
            dd($command);

            self::runProcess($command, $directory, "Memasang Modul : $namaModul di direktori $directory\n");
            echo "Modul berhasil dipasang.\n";

        }

    }
}
