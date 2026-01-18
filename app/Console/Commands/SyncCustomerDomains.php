<?php

namespace App\Console\Commands;

use App\Models\Pelanggan;
use App\Services\DomainSyncService;
use Illuminate\Console\Command;

class SyncCustomerDomains extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'domains:sync
                            {--customer= : Sync specific customer by ID}
                            {--force : Force resync all domains}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sinkronisasi domain pelanggan dengan Cloudflare untuk identifikasi zone dan validasi DNS';

    private DomainSyncService $domainSyncService;

    /**
     * Create a new command instance.
     */
    public function __construct(DomainSyncService $domainSyncService)
    {
        parent::__construct();
        $this->domainSyncService = $domainSyncService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $startTime = microtime(true);

        $this->info('═══════════════════════════════════════════════════════');
        $this->info('   Cloudflare Domain Synchronization');
        $this->info('═══════════════════════════════════════════════════════');
        $this->newLine();

        // Check if syncing specific customer
        if ($customerId = $this->option('customer')) {
            return $this->syncSpecificCustomer($customerId, $startTime);
        }

        // Sync all customers
        return $this->syncAllCustomers($startTime);
    }

    /**
     * Sync specific customer
     */
    private function syncSpecificCustomer(int $customerId, float $startTime): int
    {
        $customer = Pelanggan::find($customerId);

        if (!$customer) {
            $this->error("✗ Customer dengan ID {$customerId} tidak ditemukan");
            return Command::FAILURE;
        }

        $this->info("Syncing domain untuk: {$customer->nama_desa}");
        $this->info("Domain: {$customer->domain_opensid}");
        $this->newLine();

        $result = $this->domainSyncService->syncCustomerDomain($customer);

        if ($result['success']) {
            $this->info("✓ Domain berhasil disinkronkan");
            $this->line("  - Jenis: " . ($result['domain_type'] ?? 'N/A'));
            $this->line("  - Status DNS: " . $result['dns_status']);
        } else {
            $this->error("✗ Gagal sinkronisasi domain");
            $this->line("  - Error: " . $result['error']);
        }

        $this->displayExecutionTime($startTime);

        return $result['success'] ? Command::SUCCESS : Command::FAILURE;
    }

    /**
     * Sync all customers
     */
    private function syncAllCustomers(float $startTime): int
    {
        $customers = Pelanggan::all();
        $total = $customers->count();

        $this->info("Total pelanggan: {$total}");
        $this->newLine();

        // Create progress bar
        $progressBar = $this->output->createProgressBar($total);
        $progressBar->setFormat(
            " %current%/%max% [%bar%] %percent:3s%% \n 🔄 %message%"
        );
        $progressBar->setMessage('Memulai sinkronisasi...');
        $progressBar->start();

        // Sync with progress callback
        $result = $this->domainSyncService->syncAllCustomers(
            function ($current, $total, $customer) use ($progressBar) {
                $progressBar->setMessage("Syncing: {$customer->domain_opensid}");
                $progressBar->advance();
            }
        );

        $progressBar->finish();
        $this->newLine(2);

        // Display summary
        $this->displaySummary($result, $startTime);

        return $result['success'] ? Command::SUCCESS : Command::FAILURE;
    }

    /**
     * Display sync summary
     */
    private function displaySummary(array $result, float $startTime): void
    {
        $this->info('═══════════════════════════════════════════════════════');
        $this->info('   Summary');
        $this->info('═══════════════════════════════════════════════════════');
        $this->newLine();

        // Success count
        $this->line("✓ <fg=green>Successfully synced:</> {$result['synced']}");

        // Failed count
        if ($result['failed'] > 0) {
            $this->line("✗ <fg=red>Failed:</> {$result['failed']}");
        }

        $this->newLine();

        // Display errors if any
        if (!empty($result['errors'])) {
            $this->warn('Failed domains:');
            $this->newLine();

            $errorCount = min(10, count($result['errors'])); // Show max 10 errors
            for ($i = 0; $i < $errorCount; $i++) {
                $error = $result['errors'][$i];
                $this->line("  • {$error['domain']} (Customer ID: {$error['customer_id']})");
                $this->line("    <fg=gray>└─ {$error['error']}</>");
            }

            if (count($result['errors']) > 10) {
                $remaining = count($result['errors']) - 10;
                $this->line("  ... dan {$remaining} error lainnya");
            }

            $this->newLine();
        }

        $this->displayExecutionTime($startTime);
    }

    /**
     * Display execution time
     */
    private function displayExecutionTime(float $startTime): void
    {
        $endTime = microtime(true);
        $executionTime = $endTime - $startTime;

        $minutes = floor($executionTime / 60);
        $seconds = $executionTime - ($minutes * 60);

        $timeString = $minutes > 0
            ? sprintf('%dm %ds', $minutes, $seconds)
            : sprintf('%.2fs', $seconds);

        $this->info("⏱  Sync completed in {$timeString}");
        $this->newLine();
    }
}
