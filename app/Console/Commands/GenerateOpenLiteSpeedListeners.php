<?php

namespace App\Console\Commands;

use App\Services\OpenLiteSpeedListenerService;
use Illuminate\Console\Command;

class GenerateOpenLiteSpeedListeners extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'ols:generate-listeners 
                           {--sync : Sync with existing vhosts}
                           {--write : Write to configuration file}
                           {--backup : Create backup before writing}
                           {--discover : Only discover and show vhosts}';

    /**
     * The console command description.
     */
    protected $description = 'Generate OpenLiteSpeed listeners configuration from existing vhosts';

    public function __construct(
        private readonly OpenLiteSpeedListenerService $listenerService
    ) {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('OpenLiteSpeed Listeners Generator');
        $this->line('=====================================');

        try {
            // Only discover vhosts
            if ($this->option('discover')) {
                return $this->discoverVhosts();
            }

            // Sync with existing vhosts
            if ($this->option('sync')) {
                return $this->syncWithVhosts();
            }

            // Default: generate from discovered vhosts
            return $this->generateListeners();

        } catch (\Exception $e) {
            $this->error('Error: ' . $e->getMessage());
            return 1;
        }
    }

    /**
     * Discover and display vhosts
     */
    private function discoverVhosts(): int
    {
        $this->info('Discovering vhosts from file system...');
        
        $vhosts = $this->listenerService->discoverVhostsFromFileSystem();
        
        if (empty($vhosts)) {
            $this->warn('No vhosts found in the file system.');
            return 0;
        }

        $this->info("Found " . count($vhosts) . " vhosts:");
        $this->line('');

        $headers = ['VHost Name', 'Domain', 'Config File'];
        $rows = [];

        foreach ($vhosts as $vhost) {
            $rows[] = [
                $vhost['vhost_name'],
                $vhost['domain'],
                basename($vhost['config_file'])
            ];
        }

        $this->table($headers, $rows);
        
        return 0;
    }

    /**
     * Sync listeners with existing vhosts
     */
    private function syncWithVhosts(): int
    {
        $this->info('Syncing listeners with existing vhosts...');
        
        $config = $this->listenerService->syncListenersWithVhosts();
        
        if (empty($config)) {
            $this->warn('No configuration generated.');
            return 1;
        }

        $this->line('Generated configuration:');
        $this->line('');
        $this->line($config);

        if ($this->option('write')) {
            return $this->writeConfiguration($config);
        }

        $this->comment('Use --write option to save configuration to file.');
        return 0;
    }

    /**
     * Generate listeners configuration
     */
    private function generateListeners(): int
    {
        $this->info('Generating listeners configuration...');
        
        $config = $this->listenerService->generateListenersFromVhosts();
        
        if (empty($config)) {
            $this->warn('No configuration generated. No vhosts found.');
            return 1;
        }

        $this->line('Generated configuration:');
        $this->line('');
        $this->line($config);

        if ($this->option('write')) {
            return $this->writeConfiguration($config);
        }

        $this->comment('Use --write option to save configuration to file.');
        return 0;
    }

    /**
     * Write configuration to file
     */
    private function writeConfiguration(string $config): int
    {
        // Create backup if requested
        if ($this->option('backup')) {
            $this->info('Creating backup...');
            $backupPath = $this->listenerService->backupConfiguration();
            $this->line("Backup created: $backupPath");
        }

        // Confirm before writing
        if (!$this->confirm('Do you want to write this configuration to the listeners file?')) {
            $this->info('Operation cancelled.');
            return 0;
        }

        $this->info('Writing configuration to file...');
        
        $success = $this->listenerService->writeListenersConfig($config);
        
        if ($success) {
            $this->info('✓ Configuration written successfully!');
            
            // Show status
            $status = $this->listenerService->getListenersStatus();
            $this->line("Mappings count: {$status['mappings_count']}");
            
            return 0;
        } else {
            $this->error('✗ Failed to write configuration.');
            return 1;
        }
    }
}