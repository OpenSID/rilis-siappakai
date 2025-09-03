<?php

namespace App\Contracts;

use App\Models\Pelanggan;

interface WebsiteDeactivationServiceInterface
{
    /**
     * Deactivate website by redirecting to expired page
     *
     * @param Pelanggan $pelanggan
     * @param bool $isDryRun
     * @return bool
     */
    public function deactivateWebsite(Pelanggan $pelanggan, bool $isDryRun = false): bool;

    /**
     * Validate environment requirements
     *
     * @return array
     */
    public function validateEnvironment(): array;
}