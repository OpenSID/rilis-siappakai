<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\MultiTenantService;
use Exception;

class TestMultiTenantCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'multitenant:test
                            {--dry-run : Show what would be done without actually doing it}
                            {--cleanup : Clean up test resources after running}';

    /**
     * The console command description.
     */
    protected $description = 'Test OpenSID multi-tenant system functionality';

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
        $dryRun = $this->option('dry-run');
        $cleanup = $this->option('cleanup');
        
        $testKodeDesa = '9999999999';
        $testDomain = 'test-opensid.local';

        $this->info("🧪 OpenSID Multi-Tenant System Test");
        $this->newLine();

        if ($dryRun) {
            $this->warn("🔍 DRY RUN MODE - No actual changes will be made");
            $this->newLine();
        }

        // Test 1: System Requirements
        $this->info("1. Testing System Requirements...");
        $this->testSystemRequirements();

        // Test 2: Configuration
        $this->info("2. Testing Configuration...");
        $this->testConfiguration();

        // Test 3: Services Instantiation
        $this->info("3. Testing Service Dependencies...");
        $this->testServices();

        if (!$dryRun) {
            // Test 4: Site Creation
            $this->info("4. Testing Site Creation...");
            $this->testSiteCreation($testKodeDesa, $testDomain);

            // Test 5: Site Info
            $this->info("5. Testing Site Information...");
            $this->testSiteInfo($testKodeDesa);

            // Test 6: Site Listing
            $this->info("6. Testing Site Listing...");
            $this->testSiteListing();

            if ($cleanup) {
                // Test 7: Site Removal
                $this->info("7. Testing Site Removal...");
                $this->testSiteRemoval($testKodeDesa, $testDomain);
            }
        }

        $this->newLine();
        $this->info("✅ All tests completed!");
        
        if (!$dryRun && !$cleanup) {
            $this->newLine();
            $this->warn("⚠️  Test site created with kode desa: {$testKodeDesa}");
            $this->warn("   Use --cleanup flag to remove test site after testing");
            $this->line("   Or manually remove with: php artisan multitenant:remove-site {$testKodeDesa} {$testDomain}");
        }

        return 0;
    }

    /**
     * Test system requirements
     */
    private function testSystemRequirements(): void
    {
        // PHP Version
        $phpVersion = PHP_VERSION;
        if (version_compare($phpVersion, '8.1.0', '>=')) {
            $this->line("  ✅ PHP Version: {$phpVersion}");
        } else {
            $this->line("  ❌ PHP Version: {$phpVersion} (requires 8.1+)");
        }

        // Extensions
        $requiredExtensions = ['mysqli', 'curl', 'gd', 'mbstring', 'xml', 'zip', 'json'];
        foreach ($requiredExtensions as $ext) {
            if (extension_loaded($ext)) {
                $this->line("  ✅ Extension: {$ext}");
            } else {
                $this->line("  ❌ Extension: {$ext} (missing)");
            }
        }

        // Base Directory
        $baseDir = config('multitenant.base_path');
        if (is_dir($baseDir)) {
            $this->line("  ✅ Base Directory: {$baseDir}");
        } else {
            $this->line("  ⚠️  Base Directory: {$baseDir} (not found - will be created)");
        }

        // Write Permissions
        $testFile = $baseDir . '/.test_write_' . time();
        if (@file_put_contents($testFile, 'test')) {
            @unlink($testFile);
            $this->line("  ✅ Write Permissions: OK");
        } else {
            $this->line("  ❌ Write Permissions: FAILED");
        }
    }

    /**
     * Test configuration
     */
    private function testConfiguration(): void
    {
        $config = config('multitenant');
        
        if ($config) {
            $this->line("  ✅ Configuration file loaded");
            
            // Test required config keys
            $requiredKeys = ['base_path', 'php', 'security', 'openlitespeed'];
            foreach ($requiredKeys as $key) {
                if (array_key_exists($key, $config)) {
                    $this->line("  ✅ Config key: {$key}");
                } else {
                    $this->line("  ❌ Config key: {$key} (missing)");
                }
            }
        } else {
            $this->line("  ❌ Configuration file not found");
        }
    }

    /**
     * Test service instantiation
     */
    private function testServices(): void
    {
        try {
            $service = app(\App\Services\MultiTenantService::class);
            $this->line("  ✅ MultiTenantService: OK");
        } catch (Exception $e) {
            $this->line("  ❌ MultiTenantService: " . $e->getMessage());
        }

        try {
            $service = app(\App\Services\LinuxUserService::class);
            $this->line("  ✅ LinuxUserService: OK");
        } catch (Exception $e) {
            $this->line("  ❌ LinuxUserService: " . $e->getMessage());
        }

        try {
            $service = app(\App\Services\OpenLiteSpeedService::class);
            $this->line("  ✅ OpenLiteSpeedService: OK");
        } catch (Exception $e) {
            $this->line("  ❌ OpenLiteSpeedService: " . $e->getMessage());
        }

        try {
            $service = app(\App\Services\TemplateService::class);
            $this->line("  ✅ TemplateService: OK");
        } catch (Exception $e) {
            $this->line("  ❌ TemplateService: " . $e->getMessage());
        }
    }

    /**
     * Test site creation
     */
    private function testSiteCreation(string $kodeDesa, string $domain): void
    {
        try {
            $result = $this->multiTenantService->createSite($kodeDesa, $domain);
            $this->line("  ✅ Site Creation: SUCCESS");
            $this->line("     User: " . $result['user_info']['username']);
            $this->line("     Directory: " . $result['directories']['base']);
        } catch (Exception $e) {
            $this->line("  ❌ Site Creation: " . $e->getMessage());
        }
    }

    /**
     * Test site information retrieval
     */
    private function testSiteInfo(string $kodeDesa): void
    {
        try {
            $info = $this->multiTenantService->getSiteInfo($kodeDesa);
            if ($info) {
                $this->line("  ✅ Site Info: Retrieved successfully");
                $this->line("     Size: " . $this->formatBytes($info['size'] ?? 0));
            } else {
                $this->line("  ❌ Site Info: Not found");
            }
        } catch (Exception $e) {
            $this->line("  ❌ Site Info: " . $e->getMessage());
        }
    }

    /**
     * Test site listing
     */
    private function testSiteListing(): void
    {
        try {
            $sites = $this->multiTenantService->listSites();
            $this->line("  ✅ Site Listing: Found " . count($sites) . " sites");
        } catch (Exception $e) {
            $this->line("  ❌ Site Listing: " . $e->getMessage());
        }
    }

    /**
     * Test site removal
     */
    private function testSiteRemoval(string $kodeDesa, string $domain): void
    {
        try {
            $this->multiTenantService->removeSite($kodeDesa, $domain);
            $this->line("  ✅ Site Removal: SUCCESS");
        } catch (Exception $e) {
            $this->line("  ❌ Site Removal: " . $e->getMessage());
        }
    }

    /**
     * Format bytes to human readable format
     */
    private function formatBytes(int $bytes): string
    {
        if ($bytes == 0) {
            return '0 B';
        }

        $units = ['B', 'KB', 'MB', 'GB'];
        $exponent = floor(log($bytes) / log(1024));
        
        return round($bytes / pow(1024, $exponent), 2) . ' ' . $units[$exponent];
    }
}