<?php

namespace App\Repositories\Contracts;

use App\Models\MasterCloudflare;
use Illuminate\Support\Collection;

interface MasterCloudflareRepositoryInterface
{
    public function all(): Collection;

    public function find(int $id): ?MasterCloudflare;

    public function findByAccountName(string $accountName): ?MasterCloudflare;

    public function create(array $data): MasterCloudflare;

    public function update(MasterCloudflare $cloudflare, array $data): MasterCloudflare;

    public function delete(MasterCloudflare $cloudflare): bool;

    public function deleteMultiple(array $ids): bool;
}
