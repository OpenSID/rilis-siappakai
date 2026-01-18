<?php

namespace App\Repositories;

use App\Models\MasterCloudflare;
use App\Repositories\Contracts\MasterCloudflareRepositoryInterface;
use Illuminate\Support\Collection;

class MasterCloudflareRepository implements MasterCloudflareRepositoryInterface
{
    public function all(): Collection
    {
        return MasterCloudflare::all();
    }

    public function find(int $id): ?MasterCloudflare
    {
        return MasterCloudflare::find($id);
    }

    public function findByAccountName(string $accountName): ?MasterCloudflare
    {
        return MasterCloudflare::where('account_name', $accountName)->first();
    }

    public function create(array $data): MasterCloudflare
    {
        return MasterCloudflare::create($data);
    }

    public function update(MasterCloudflare $cloudflare, array $data): MasterCloudflare
    {
        $cloudflare->update($data);
        return $cloudflare->fresh();
    }

    public function delete(MasterCloudflare $cloudflare): bool
    {
        return $cloudflare->delete();
    }

    public function deleteMultiple(array $ids): bool
    {
        return MasterCloudflare::whereIn('id', $ids)->delete() > 0;
    }
}
