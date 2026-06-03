<?php

namespace App\Repositories\Contracts;

use App\Models\ApiHealthCheck;
use App\Models\Pelanggan;
use Illuminate\Support\Collection;

interface ApiHealthCheckRepositoryInterface
{
    public function upsert(Pelanggan $pelanggan, array $data): ApiHealthCheck;

    public function getLatestPerPelanggan(): Collection;

    public function getByPelanggan(Pelanggan $pelanggan): ?ApiHealthCheck;

    public function getSummary(): array;
}
