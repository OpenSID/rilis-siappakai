<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\RecycleBinService;
use Illuminate\Support\Facades\Log;

class CleanRecycleBin extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'recycle-bin:clean {--days=30 : Number of days to keep files in recycle bin}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean old files from recycle bin that are older than specified days';

    protected RecycleBinService $recycleBinService;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->recycleBinService = new RecycleBinService();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $days = (int) $this->option('days');
        
        if ($days < 1) {
            $this->error('Days must be at least 1');
            return 1;
        }

        $this->info("Starting recycle bin cleanup for files older than {$days} days...");

        try {
            $result = $this->recycleBinService->cleanOldItems($days);
            
            $this->info("Cleanup completed successfully:");
            $this->info("- Deleted items: {$result['deleted_count']}");
            $this->info("- Total size freed: " . $this->formatBytes($result['total_size']));
            $this->info("- Errors encountered: {$result['error_count']}");

            if (!empty($result['deleted_items'])) {
                $this->info("Deleted items:");
                foreach ($result['deleted_items'] as $item) {
                    $this->line("  - {$item}");
                }
            }

            // Log the cleanup activity
            Log::info("Recycle bin cleanup completed via command", [
                'days' => $days,
                'deleted_count' => $result['deleted_count'],
                'total_size' => $result['total_size'],
                'error_count' => $result['error_count']
            ]);

            return 0;

        } catch (\Exception $e) {
            $this->error("Failed to clean recycle bin: " . $e->getMessage());
            Log::error("Recycle bin cleanup failed via command", [
                'days' => $days,
                'error' => $e->getMessage()
            ]);
            return 1;
        }
    }

    /**
     * Format bytes into human readable format
     */
    private function formatBytes(int $bytes): string
    {
        if ($bytes === 0) {
            return '0 B';
        }

        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(log($bytes) / log(1024));
        $pow = min($pow, count($units) - 1);

        $bytes /= (1 << (10 * $pow));

        return round($bytes, 2) . ' ' . $units[$pow];
    }
}