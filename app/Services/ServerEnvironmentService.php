<?php

namespace App\Services;

class ServerEnvironmentService
{
    private string $defaultDevDomain = 'https://devlayanan.opendesa.id';
    private string $defaultProdDomain = 'https://layanan.opendesa.id';

    public function getServerLayanan(): string
    {
        // Prioritaskan nilai dari `SERVER_LAYANAN` di file .env
        $serverLayanan = env('SERVER_LAYANAN');

        if ($serverLayanan) {
            return $serverLayanan;
        }

        // Tentukan domain berdasarkan environment
        return self::isProduction() ? $this->defaultProdDomain : $this->defaultDevDomain;
    }

    private function isProduction(): bool
    {
        return env('APP_ENV') === 'production';
    }
}
