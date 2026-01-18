<?php

namespace App\Repositories\Contracts;

use App\Models\CloudflareRuleMapping;
use Illuminate\Support\Collection;

interface CloudflareRuleMappingRepositoryInterface
{
    /**
     * Get all mappings for a specific domain keyed by rule_master_id
     */
    public function getByDomainKeyedByMaster(int $domainId): Collection;

    /**
     * Update or create a mapping
     */
    public function updateOrCreate(int $ruleMasterId, int $domainId, string $cloudflareRuleId): CloudflareRuleMapping;

    /**
     * Delete mappings not in the provided master rule IDs
     */
    public function deleteOrphanedMappings(int $domainId, array $masterRuleIds): int;
}
