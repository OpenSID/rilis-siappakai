<?php

namespace App\Services;

final class ServerLayananService
{
     
    /**
     * Mengembalikan URL server layanan berdasarkan environment.
     * Jika environment production maka menggunakan URL yang di set di env SERVER_LAYANAN.
     * Jika environment development maka menggunakan URL https://devlayanan.opendesa.id.
     * Pastikan URL menggunakan HTTPS.
     *
     * @return string URL server layanan.
     */
    public static function getUrl(): string
    {
        $serverUrl = match (env('APP_ENV')) {
            'production' => env('SERVER_LAYANAN', 'https://layanan.opendesa.id'),
            default => 'https://devlayanan.opendesa.id',
        };

        // Pastikan URL menggunakan HTTPS
        if (str_starts_with($serverUrl, 'http://')) {
            $serverUrl = 'https://' . substr($serverUrl, 7);
        }

        return $serverUrl;
    }

}
