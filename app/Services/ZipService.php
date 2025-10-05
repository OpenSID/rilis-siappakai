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

    /**
     * Mengekstrak file .zip ke folder tujuan
     *
     * @param string $zipFile nama file zip yang akan diekstrak
     * @param string $destinationFolder folder tujuan untuk ekstraksi
     *
     * @return void
     */
    public function unzipFile($zipFile, $destinationFolder)
    {
        if (!file_exists($zipFile)) {
            echo "File ZIP $zipFile tidak ditemukan\n";
            return;
        }

        // Cek apakah file benar-benar merupakan file zip
        if (!is_file($zipFile) || mime_content_type($zipFile) !== 'application/zip') {
            echo "File $zipFile bukan file ZIP yang valid\n";
            return;
        }

        // Pastikan folder tujuan ada, buat jika belum ada
        if (!file_exists($destinationFolder)) {
            mkdir($destinationFolder, 0755, true);
        }

        $zip = new ZipArchive;
        $zip->open($zipFile);

        if ($zip->open($zipFile) === TRUE) {
            // Ekstrak file ZIP ke folder tujuan
            $zip->extractTo($destinationFolder);
            $zip->close();

            echo "File ZIP berhasil diekstrak ke $destinationFolder\n";

            // Menghapus file ZIP setelah ekstraksi (jika diperlukan)
            // unlink($zipFile);
        } else {
            echo "Gagal membuka file ZIP\n";
        }
        
        // Menghapus file sumber
        unlink($zipFile);
    }
}
