<?php

namespace App\Services;

use App\Services\ProcessService;

class TarGzService
{
    /**
     * Konstruktor kelas BackupTarGzService
     *
     * @param CommandController $command
     *
     * Konstruktor kelas ini digunakan untuk membuat instance kelas BackupTarGzService
     * dan mengatur nilai properti $command
     */
    public function __construct() {}

    /**
     * Membuat file .tar.gz dari file $sourceFile dan menyimpan hasilnya
     * ke file $outputFile
     *
     * @param string $outputFile nama file .tar.gz yang akan dibuat
     * @param string $sourceFile nama file yang akan dikompresi
     *
     * @return void
     */
    public function tarGzFile($outputFile, $sourceFile)
    {
        if (!file_exists($sourceFile)) {
            echo "File $sourceFile tidak ditemukan\n";
            return;
        }

        // Membuat file .tar.gz menggunakan tar dan gzip
        $tarCommand = ['tar', '-czf', $outputFile, '-C', dirname($sourceFile), basename($sourceFile)];
        ProcessService::runProcess($tarCommand, dirname($sourceFile));

        // Menghapus file sumber
        unlink($sourceFile);
    }
}
