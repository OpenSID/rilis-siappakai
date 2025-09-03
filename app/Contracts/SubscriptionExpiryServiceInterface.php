<?php

namespace App\Contracts;

use App\Models\Pelanggan;
use Illuminate\Support\Collection;

interface SubscriptionExpiryServiceInterface
{
    /**
     * Get all expired subscriptions
     *
     * @return Collection
     */
    public function getExpiredSubscriptions(): Collection;

    /**
     * Update subscription status to expired
     *
     * @param Pelanggan $pelanggan
     * @param bool $isDryRun
     * @return array
     */
    public function updateExpiredStatus(Pelanggan $pelanggan, bool $isDryRun = false): array;

    /**
     * Determine if website should be deactivated based on business rules
     *
     * @param Pelanggan $pelanggan
     * @return bool
     */
    public function shouldDeactivateWebsite(Pelanggan $pelanggan): bool;
}