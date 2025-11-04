<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\MultiTenantService;
use Exception;

class ListSitesCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'multitenant:list-sites
                            {--json : Output in JSON format}
                            {--detailed : Show detailed information}';

    /**
     * The console command description.
     */
    protected $description = 'List all OpenSID multi-tenant sites';

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
        $json = $this->option('json');
        $detailed = $this->option('detailed');

        try {
            $sites = $this->multiTenantService->listSites();

            if (empty($sites)) {
                if ($json) {
                    $this->line(json_encode([], JSON_PRETTY_PRINT));
                } else {
                    $this->info("No multi-tenant sites found.");
                }
                return 0;
            }

            if ($json) {
                $this->line(json_encode($sites, JSON_PRETTY_PRINT));
                return 0;
            }

            $this->displaySitesList($sites, $detailed);
            
            return 0;

        } catch (Exception $e) {
            $this->error("Failed to list sites: " . $e->getMessage());
            return 1;
        }
    }

    /**
     * Display sites list in table format
     */
    private function displaySitesList(array $sites, bool $detailed = false): void
    {
        $this->info("📋 OpenSID Multi-Tenant Sites (" . count($sites) . " total)");
        $this->newLine();

        if ($detailed) {
            $this->displayDetailedList($sites);
        } else {
            $this->displaySummaryList($sites);
        }

        $this->newLine();
        $this->displaySummaryStats($sites);
    }

    /**
     * Display summary list
     */
    private function displaySummaryList(array $sites): void
    {
        $rows = [];
        
        foreach ($sites as $site) {
            $rows[] = [
                $site['kode_desa'],
                $site['user_info']['username'],
                $this->formatBytes($site['size'] ?? 0),
                $site['created_at'] ? date('Y-m-d H:i', strtotime($site['created_at'])) : 'Unknown',
            ];
        }

        $this->table(
            ['Kode Desa', 'Linux User', 'Size', 'Created'],
            $rows
        );
    }

    /**
     * Display detailed list
     */
    private function displayDetailedList(array $sites): void
    {
        foreach ($sites as $index => $site) {
            if ($index > 0) {
                $this->newLine();
            }

            $this->info("🏢 Site: " . $site['kode_desa']);
            
            $rows = [
                ['Linux User', $site['user_info']['username']],
                ['User ID', $site['user_info']['uid']],
                ['Group ID', $site['user_info']['gid']],
                ['Base Directory', $site['directories']['base']],
                ['Public Directory', $site['directories']['public']],
                ['Logs Directory', $site['directories']['logs']],
                ['Size', $this->formatBytes($site['size'] ?? 0)],
                ['Created At', $site['created_at'] ?? 'Unknown'],
            ];

            $this->table(['Property', 'Value'], $rows);
        }
    }

    /**
     * Display summary statistics
     */
    private function displaySummaryStats(array $sites): void
    {
        $totalSites = count($sites);
        $totalSize = array_sum(array_column($sites, 'size'));
        
        // Calculate average size
        $avgSize = $totalSites > 0 ? $totalSize / $totalSites : 0;
        
        // Find largest site
        $largestSite = null;
        $largestSize = 0;
        
        foreach ($sites as $site) {
            if (($site['size'] ?? 0) > $largestSize) {
                $largestSize = $site['size'] ?? 0;
                $largestSite = $site['kode_desa'];
            }
        }

        $this->info("📊 Summary Statistics:");
        
        $stats = [
            ['Total Sites', $totalSites],
            ['Total Disk Usage', $this->formatBytes($totalSize)],
            ['Average Site Size', $this->formatBytes($avgSize)],
        ];
        
        if ($largestSite) {
            $stats[] = ['Largest Site', "{$largestSite} ({$this->formatBytes($largestSize)})"];
        }
        
        $this->table(['Metric', 'Value'], $stats);

        $this->newLine();
        $this->comment("💡 Tip: Use --detailed flag for more information about each site");
        $this->comment("💡 Tip: Use --json flag to get machine-readable output");
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