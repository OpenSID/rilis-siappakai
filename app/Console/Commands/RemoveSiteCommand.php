<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\MultiTenantService;
use Exception;

class RemoveSiteCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'multitenant:remove-site
                            {kodeDesa : The village code (10 digits)}
                            {domain? : The domain name for the site}
                            {--force : Force removal without confirmation}';

    /**
     * The console command description.
     */
    protected $description = 'Remove an OpenSID multi-tenant site and cleanup all resources';

    public function __construct(
        private readonly MultiTenantService $multiTenantService
    ) {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $kodeDesa = $this->argument('kodeDesa');
        $domain = $this->argument('domain');
        $force = $this->option('force');

        // Validate input
        if (!preg_match('/^[0-9]{10}$/', $kodeDesa)) {
            $this->error("Invalid kode desa format. Must be 10 digits.");
            return 1;
        }

        // Get site information first
        $siteInfo = $this->multiTenantService->getSiteInfo($kodeDesa);
        
        if (!$siteInfo) {
            $this->error("Site for kode desa {$kodeDesa} does not exist.");
            return 1;
        }

        // If domain not provided, try to find it or ask for confirmation
        if (!$domain) {
            $domain = $this->ask("Please enter the domain name for this site");
            
            if (!$domain) {
                $this->error("Domain is required for site removal.");
                return 1;
            }
        }

        $this->info("Removing OpenSID multi-tenant site...");
        $this->info("Kode Desa: {$kodeDesa}");
        $this->info("Domain: {$domain}");
        $this->newLine();

        // Display site information
        $this->displaySiteInfo($siteInfo);
        $this->newLine();

        // Confirmation
        if (!$force) {
            $this->warn("⚠️  This action will permanently remove:");
            $this->line("   • Linux user: " . $siteInfo['user_info']['username']);
            $this->line("   • All site files in: " . $siteInfo['directories']['base']);
            $this->line("   • OpenLiteSpeed configurations");
            $this->line("   • PHP configuration files");
            $this->newLine();

            if (!$this->confirm("Are you sure you want to continue?")) {
                $this->info("Operation cancelled.");
                return 0;
            }
        }

        try {
            // Show progress
            $progressBar = $this->output->createProgressBar(5);
            $progressBar->setFormat('verbose');
            $progressBar->start();

            $progressBar->setMessage('Removing OpenLiteSpeed Virtual Host...');
            $progressBar->advance();

            $progressBar->setMessage('Removing OpenLiteSpeed External App...');
            $progressBar->advance();

            $progressBar->setMessage('Removing site files...');
            $progressBar->advance();

            $progressBar->setMessage('Removing Linux user...');
            $progressBar->advance();

            $progressBar->setMessage('Reloading OpenLiteSpeed...');
            
            // Remove the site
            $this->multiTenantService->removeSite($kodeDesa, $domain);
            
            $progressBar->advance();
            $progressBar->finish();

            $this->newLine(2);
            $this->info("✅ Site removed successfully!");
            
            $this->newLine();
            $this->info("📋 Summary of removed resources:");
            $this->line("   • Linux user: " . $siteInfo['user_info']['username']);
            $this->line("   • Site directory: " . $siteInfo['directories']['base']);
            $this->line("   • OpenLiteSpeed configurations for: {$domain}");
            
            return 0;

        } catch (Exception $e) {
            $this->newLine();
            $this->error("❌ Failed to remove site: " . $e->getMessage());
            
            $this->newLine();
            $this->warn("⚠️  Some resources may not have been completely removed.");
            $this->warn("   Please check manually and remove any remaining:");
            $this->line("   • Linux user: " . ($siteInfo['user_info']['username'] ?? 'opensid_' . $kodeDesa));
            $this->line("   • Site directory: " . ($siteInfo['directories']['base'] ?? config('multitenant.base_path') . '/' . $kodeDesa));
            $this->line("   • OpenLiteSpeed configurations");
            
            return 1;
        }
    }

    /**
     * Display site information table
     */
    private function displaySiteInfo(array $siteInfo): void
    {
        $this->info("📋 Current site information:");
        
        $this->table(
            ['Property', 'Value'],
            [
                ['Kode Desa', $siteInfo['kode_desa']],
                ['Linux User', $siteInfo['user_info']['username']],
                ['User ID', $siteInfo['user_info']['uid']],
                ['Group ID', $siteInfo['user_info']['gid']],
                ['Base Directory', $siteInfo['directories']['base']],
                ['Public Directory', $siteInfo['directories']['public']],
                ['Logs Directory', $siteInfo['directories']['logs']],
                ['Directory Size', $this->formatBytes($siteInfo['size'] ?? 0)],
                ['Created At', $siteInfo['created_at'] ?? 'Unknown'],
            ]
        );
    }

    /**
     * Format bytes to human readable format
     */
    private function formatBytes(int $bytes): string
    {
        if ($bytes == 0) {
            return '0 B';
        }

        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $exponent = floor(log($bytes) / log(1024));
        
        return round($bytes / pow(1024, $exponent), 2) . ' ' . $units[$exponent];
    }
}