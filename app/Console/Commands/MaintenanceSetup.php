<?php

namespace App\Console\Commands;

use App\Models\Aplikasi;
use App\Enums\ServerPanel;
use App\Services\ApacheService;
use App\Services\OpenLiteSpeedService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class MaintenanceSetup extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'siappakai:maintenance-setup';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Setup otomatis konfigurasi Global Maintenance Mode di level Web Server (Apache, OpenLiteSpeed)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🛠️  Memulai Setup Global Maintenance Mode...');

        $projectPath = base_path();
        $maintenanceHtmlPath = $projectPath . '/storage/app/halaman/maintenance.html';
        $maintenanceFlagPath = $projectPath . '/storage/app/.maintenance';

        // 1. Pastikan folder halaman ada
        if (!File::exists(dirname($maintenanceHtmlPath))) {
            File::makeDirectory(dirname($maintenanceHtmlPath), 0755, true);
            $this->info('📁 Folder halaman dibuat.');
        }

        // 2. Cek apakah file maintenance.html sudah ada
        if (!File::exists($maintenanceHtmlPath)) {
            $this->warn('⚠️  File maintenance.html tidak ditemukan!');
            $this->info('Silakan pastikan file maintenance.html ada di storage/app/halaman/');
            return 1;
        }

        // 3. Deteksi server panel dari database
        $serverPanel = Aplikasi::where('key', 'server_panel')->first();
        
        if (!$serverPanel) {
            $this->error('❌ Konfigurasi server_panel tidak ditemukan di database.');
            return 1;
        }

        $serverPanelValue = (int) $serverPanel->value;

        // 4. Setup berdasarkan server panel
        if ($serverPanelValue === ServerPanel::VPS_BIASA->value()) {
            // Apache
            $this->info('🌐 Mengonfigurasi Apache...');
            $apacheService = app(ApacheService::class);
            
            if ($apacheService->setupMaintenanceMode($maintenanceHtmlPath, $maintenanceFlagPath)) {
                $this->info('✅ Setup Apache Selesai.');
            } else {
                $this->warn('⚠️  Apache tidak terdeteksi atau tidak terpasang.');
            }
        } elseif ($serverPanelValue === ServerPanel::OPEN_LITE_SPEED->value()) {
            // OpenLiteSpeed
            $this->info('🌐 Mengonfigurasi OpenLiteSpeed...');
            $olsService = app(OpenLiteSpeedService::class);
            
            if ($olsService->setupMaintenanceMode($maintenanceHtmlPath, $maintenanceFlagPath)) {
                $this->info('✅ Setup OpenLiteSpeed Selesai.');
            } else {
                $this->error('❌ OpenLiteSpeed tidak terdeteksi.');
                return 1;
            }
        } else {
            $this->warn('⚠️  Server panel tidak didukung untuk auto-setup maintenance mode.');
            $this->info('Server panel saat ini: ' . ServerPanel::from($serverPanelValue)->label());
            return 1;
        }

        $this->newLine();
        $this->info('🚀 Global Maintenance Mode siap digunakan!');
        $this->info('Gunakan `php artisan siappakai:update-opensid` untuk menggunakannya secara otomatis.');
        
        return 0;
    }
}
