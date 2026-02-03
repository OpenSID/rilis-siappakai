<?php

namespace App\Console\Commands;

use App\Models\Aplikasi;
use App\Enums\ServerPanel;
use Illuminate\Console\Command;
use App\Jobs\SecureMasterFolderJob;
use Illuminate\Support\Facades\Config;

class SecureMasterFolder extends Command
{
    protected $signature = 'siappakai:master-secure
                            {--owner=developer : Owner user}
                            {--group-prefix= : Group prefix}
                            {--sync : Run synchronously instead of queuing}';

    protected $description = 'Dispatch jobs to secure all master folders';

    public function handle()
    {
        if (Aplikasi::where('key', 'server_panel')->first()->value != ServerPanel::OPEN_LITE_SPEED->value()) {
            $this->error("❌ This command is only applicable for OpenLiteSpeed server panel.");
            return Command::FAILURE;
        }

        $basePath = rtrim(Config::get('siappakai.root.folder'), '/');
        $owner    = $this->option('owner');
        $prefix   = $this->option('group-prefix') ?? '';
        $sync     = $this->option('sync');

        if (!$basePath || !is_dir($basePath)) {
            $this->error("❌ Base path not found: {$basePath}");
            return Command::FAILURE;
        }

        $this->info("🔐 Dispatching secure jobs for master folders");
        $this->line("Base path : {$basePath}");
        $this->line("Owner     : {$owner}");
        $this->line("Mode      : " . ($sync ? 'Synchronous' : 'Queued'));

        $dispatched = 0;
        $skipped = 0;

        foreach (master_folders() as $item) {
            $folder = $item['folder'];
            $path   = "{$basePath}/{$folder}";
            $group  = "{$prefix}{$folder}";

            if (!is_dir($path)) {
                $this->warn("⚠️ Skip: {$path}");
                $skipped++;
                continue;
            }

            try {
                $job = new SecureMasterFolderJob($path, $owner, $group, $folder);

                if ($sync) {
                    $job->handle();
                    $this->info("✅ {$folder} secured (sync)");
                } else {
                    dispatch($job);
                    $this->info("📤 {$folder} job dispatched");
                }

                $dispatched++;
            } catch (\Throwable $e) {
                $this->error("❌ Failed to dispatch job for: {$folder}");
                $this->error($e->getMessage());
                return Command::FAILURE;
            }
        }

        $this->newLine();
        $this->info("🎉 Dispatched: {$dispatched} jobs");
        if ($skipped > 0) {
            $this->line("⚠️  Skipped: {$skipped} folders");
        }

        if (!$sync) {
            $this->line("💡 Monitor progress: php artisan queue:work");
        }

        return Command::SUCCESS;
    }
}
