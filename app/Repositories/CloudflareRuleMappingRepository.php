<?php

namespace App\Repositories;

use App\Models\CloudflareRuleMapping;
use App\Repositories\Contracts\CloudflareRuleMappingRepositoryInterface;
use Illuminate\Support\Collection;

class CloudflareRuleMappingRepository implements CloudflareRuleMappingRepositoryInterface
{
    public function getByDomainKeyedByMaster(int $domainId): Collection
    {
        return CloudflareRuleMapping::where('customer_domain_id', $domainId)
            ->get()
            ->keyBy('rule_master_id');
    }

    public function updateOrCreate(int $ruleMasterId, int $domainId, string $cloudflareRuleId): CloudflareRuleMapping
    {
        return CloudflareRuleMapping::updateOrCreate(
            [
                'rule_master_id' => $ruleMasterId,
                'customer_domain_id' => $domainId
            ],
            [
                'cloudflare_rule_id' => $cloudflareRuleId,
                'synced_at' => now(),
                'status' => 'synced'
            ]
        );
    }

    public function deleteOrphanedMappings(int $domainId, array $masterRuleIds): int
    {
        return CloudflareRuleMapping::where('customer_domain_id', $domainId)
            ->whereNotIn('rule_master_id', $masterRuleIds)
            ->delete();
    }
}
