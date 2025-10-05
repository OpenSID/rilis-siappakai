<?php

namespace App\Services;

use App\Contracts\SubscriptionExpiryServiceInterface;
use App\Models\Pelanggan;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class SubscriptionExpiryService implements SubscriptionExpiryServiceInterface
{
    public function __construct(
        private readonly PelangganService $pelangganService
    ) {}

    /**
     * Get all expired subscriptions
     *
     * @return Collection
     */
    public function getExpiredSubscriptions(): Collection
    {
        return Pelanggan::where(function ($query) {
            $query->where('tgl_akhir_premium', '<', now())
                  ->orWhere('tgl_akhir_saas', '<', now());
        })->get();
    }

    /**
     * Update subscription status to expired
     *
     * @param Pelanggan $pelanggan
     * @param bool $isDryRun
     * @return array
     */
    public function updateExpiredStatus(Pelanggan $pelanggan, bool $isDryRun = false): array
    {
        $updates = [];
        $messages = [];

        // Check premium subscription expiry
        if ($pelanggan->tgl_akhir_premium && Carbon::parse($pelanggan->tgl_akhir_premium)->isPast()) {
            if ($pelanggan->status_langganan_opensid != 3) {
                $updates['status_langganan_opensid'] = 3;
                $messages[] = "Premium subscription expired for {$pelanggan->domain_opensid}";
            }
        }

        // Check SaaS subscription expiry
        if ($pelanggan->tgl_akhir_saas && Carbon::parse($pelanggan->tgl_akhir_saas)->isPast()) {
            if ($pelanggan->status_langganan_saas != 3) {
                $updates['status_langganan_saas'] = 3;
                $messages[] = "SaaS subscription expired for {$pelanggan->domain_opensid}";
            }
        }

        // Apply updates if not dry run
        if (!$isDryRun && !empty($updates)) {
            $this->pelangganService->updatePelanggan($updates, $pelanggan->id);
        }

        return [
            'updates' => $updates,
            'messages' => $messages,
            'expired_count' => count($updates)
        ];
    }

    /**
     * Determine if website should be deactivated based on business rules
     *
     * @param Pelanggan $pelanggan
     * @return bool
     */
    public function shouldDeactivateWebsite(Pelanggan $pelanggan): bool
    {
        if (!$pelanggan->tgl_akhir_saas || !$pelanggan->tgl_akhir_premium) {
            return false;
        }

        $tglAkhirSaas = Carbon::parse($pelanggan->tgl_akhir_saas);
        $tglAkhirPremium = Carbon::parse($pelanggan->tgl_akhir_premium);

        // If tgl_akhir_saas year is 9999, use tgl_akhir_premium as reference
        if ($tglAkhirSaas->year == 9999) {
            return $tglAkhirPremium->isPast();
        }

        // Otherwise, use tgl_akhir_saas as reference
        return $tglAkhirSaas->isPast();
    }
}