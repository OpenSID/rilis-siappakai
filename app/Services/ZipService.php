<?php

namespace App\Services;

use ZipArchive;

class ZipService
{
    /**
     * Membuat file .zip dari file $sourceFile dan menyimpan hasilnya
     * ke file $outputFile
     *
     * @param string $outputFile nama file .zip yang akan dibuat
     * @param string $sourceFile nama file yang akan dikompresi
     *
     * @return void
     */
    public function zipFile($outputFile, $sourceFile)
    {
        if (!file_exists($sourceFile)) {
            echo "File $sourceFile tidak ditemukan\n";
            return;
        }

        $zip = new ZipArchive;

        if ($zip->open($outputFile, ZipArchive::CREATE) === TRUE) {
            // Buka file sumber untuk membaca dalam mode streaming
            $fileStream = fopen($sourceFile, 'rb');
            if ($fileStream === false) {
                echo "Gagal membuka file sumber\n";
                return;
            }

            $zip->addFromString(basename($sourceFile), stream_get_contents($fileStream));
            fclose($fileStream);
            $zip->close();
        } else {
            echo "Gagal membuat file zip\n";
            return;
        }

        // Menghapus file sumber
        unlink($sourceFile);
    }
}
