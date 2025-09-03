<?php

namespace App\Console\Commands;

use App\Contracts\SubscriptionExpiryServiceInterface;
use App\Contracts\WebsiteDeactivationServiceInterface;
use App\Models\Pelanggan;
use Illuminate\Console\Command;

class CheckSubscriptionExpiry extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'siappakai:check-subscription-expiry {--dry-run : Show what would be done without making changes}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check subscription expiry dates and deactivate expired websites';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(
        private readonly SubscriptionExpiryServiceInterface $subscriptionExpiryService,
        private readonly WebsiteDeactivationServiceInterface $websiteDeactivationService
    ) {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Starting subscription expiry check...');
        $isDryRun = $this->option('dry-run');
        
        if ($isDryRun) {
            $this->warn('DRY RUN MODE - No changes will be made');
        }

        // Validate environment
        $validation = $this->websiteDeactivationService->validateEnvironment();
        if (!$validation['valid']) {
            foreach ($validation['errors'] as $error) {
                $this->error($error);
            }
            return 1;
        }

        $pelangganList = Pelanggan::all();
        $expiredCount = 0;
        $deactivatedCount = 0;
        $errorCount = 0;

        foreach ($pelangganList as $pelanggan) {
            try {
                // Check if website should be deactivated first
                if ($this->subscriptionExpiryService->shouldDeactivateWebsite($pelanggan)) {
                    if ($this->websiteDeactivationService->deactivateWebsite($pelanggan, $isDryRun)) {
                        $this->info(($isDryRun ? 'Would deactivate' : 'Deactivated') . " website: {$pelanggan->domain_opensid}");
                        $deactivatedCount++;
                    }
                }

                // Then update expired subscription statuses
                $result = $this->subscriptionExpiryService->updateExpiredStatus($pelanggan, $isDryRun);
                $expiredCount += $result['expired_count'];

                // Display messages
                foreach ($result['messages'] as $message) {
                    $this->info($message . ($isDryRun ? ' (DRY RUN)' : ''));
                }
            } catch (\Exception $e) {
                $this->error("Error processing pelanggan ID {$pelanggan->id} ({$pelanggan->domain_opensid}): " . $e->getMessage());
                $errorCount++;
                // Continue processing other customers
                continue;
            }
        }

        $this->info("Subscription expiry check completed.");
        $this->info("Total expired subscriptions: {$expiredCount}");
        $this->info("Total websites deactivated: {$deactivatedCount}");
        
        if ($errorCount > 0) {
            $this->warn("Errors encountered: {$errorCount}");
        }

        return $errorCount > 0 ? 1 : 0;
    }
}