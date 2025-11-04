<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\MultiTenantService;
use Exception;

class AddSiteCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'multitenant:add-site
                            {kodeDesa : The village code (10 digits)}
                            {domain : The domain name for the site}
                            {--force : Force creation even if site exists}';

    /**
     * The console command description.
     */
    protected $description = 'Add a new OpenSID multi-tenant site with OpenLiteSpeed isolation';

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

        if (!filter_var('http://' . $domain, FILTER_VALIDATE_URL)) {
            $this->error("Invalid domain format: {$domain}");
            return 1;
        }

        $this->info("Creating new OpenSID multi-tenant site...");
        $this->info("Kode Desa: {$kodeDesa}");
        $this->info("Domain: {$domain}");
        $this->newLine();

        // Check if site already exists
        if (!$force && $this->multiTenantService->getSiteInfo($kodeDesa)) {
            $this->error("Site for kode desa {$kodeDesa} already exists. Use --force to recreate.");
            return 1;
        }

        try {
            // Show progress
            $progressBar = $this->output->createProgressBar(7);
            $progressBar->setFormat('verbose');
            $progressBar->start();

            $progressBar->setMessage('Creating Linux user...');
            $progressBar->advance();

            $progressBar->setMessage('Creating directory structure...');
            $progressBar->advance();

            $progressBar->setMessage('Setting file permissions...');
            $progressBar->advance();

            $progressBar->setMessage('Creating PHP configuration...');
            $progressBar->advance();

            $progressBar->setMessage('Configuring OpenLiteSpeed External App...');
            $progressBar->advance();

            $progressBar->setMessage('Configuring OpenLiteSpeed Virtual Host...');
            $progressBar->advance();

            $progressBar->setMessage('Reloading OpenLiteSpeed...');
            
            // Create the site
            $result = $this->multiTenantService->createSite($kodeDesa, $domain);
            
            $progressBar->advance();
            $progressBar->finish();

            $this->newLine(2);
            $this->info("✅ Site created successfully!");
            $this->newLine();

            // Display site information
            $this->displaySiteInfo($result);
            
            $this->newLine();
            $this->info("🔗 You can now access your site at: http://{$domain}");
            $this->info("📁 Site files are located at: " . config('multitenant.base_path') . "/{$kodeDesa}");
            
            return 0;

        } catch (Exception $e) {
            $this->newLine();
            $this->error("❌ Failed to create site: " . $e->getMessage());
            
            $this->newLine();
            $this->warn("🔄 Attempting to cleanup any partial changes...");
            
            try {
                $this->multiTenantService->removeSite($kodeDesa, $domain);
                $this->info("✅ Cleanup completed successfully.");
            } catch (Exception $cleanupError) {
                $this->error("❌ Cleanup failed: " . $cleanupError->getMessage());
                $this->warn("⚠️  Manual cleanup may be required.");
            }
            
            return 1;
        }
    }

    /**
     * Display site information table
     */
    private function displaySiteInfo(array $siteInfo): void
    {
        $this->table(
            ['Property', 'Value'],
            [
                ['Kode Desa', $siteInfo['kode_desa']],
                ['Domain', $siteInfo['domain']],
                ['Linux User', $siteInfo['user_info']['username']],
                ['User ID', $siteInfo['user_info']['uid']],
                ['Group ID', $siteInfo['user_info']['gid']],
                ['Base Directory', $siteInfo['directories']['base']],
                ['Public Directory', $siteInfo['directories']['public']],
                ['Logs Directory', $siteInfo['directories']['logs']],
                ['PHP Configuration', $siteInfo['directories']['php'] . '/php.ini'],
                ['External App', $siteInfo['external_app']['name']],
                ['Socket Path', $siteInfo['external_app']['address']],
                ['Virtual Host', $siteInfo['virtual_host']['name']],
                ['Status', $siteInfo['status']],
                ['Created At', $siteInfo['created_at']],
            ]
        );
    }
}