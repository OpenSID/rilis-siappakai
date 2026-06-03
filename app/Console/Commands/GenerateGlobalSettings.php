<?php

namespace App\Console\Commands;

use App\Models\Pelanggan;
use App\Services\GlobalSettingsService;
use Illuminate\Console\Command;

class GenerateGlobalSettings extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'siappakai:generate-global-settings
                            {--kode_desa= : Kode desa tertentu, pisahkan dengan koma untuk multiple (opsional)}
                            {--force : Timpa file global.json yang sudah ada}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate file global.json untuk semua pelanggan atau pelanggan tertentu';

    public function __construct(
        private readonly GlobalSettingsService $settingsService
    ) {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $kodeDesa = $this->option('kode_desa');
        $force = $this->option('force');

        $pelangganList = $this->getPelangganList($kodeDesa);

        if ($pelangganList->isEmpty()) {
            $this->warn('Tidak ada pelanggan yang ditemukan.');

            return self::FAILURE;
        }

        $this->info("Memproses {$pelangganList->count()} pelanggan...");
        $this->newLine();

        $bar = $this->output->createProgressBar($pelangganList->count());
        $bar->start();

        $success = 0;
        $skipped = 0;
        $failed = 0;
        $errors = [];

        foreach ($pelangganList as $pelanggan) {
            try {
                // Skip jika sudah ada dan tidak force
                if (! $force && $this->settingsService->hasSettings($pelanggan)) {
                    $skipped++;
                    $bar->advance();
                    continue;
                }

                $this->settingsService->generateDefaultSettings($pelanggan);
                $success++;
            } catch (\Exception $e) {
                $failed++;
                $errors[] = [
                    'kode_desa' => $pelanggan->kode_desa,
                    'nama_desa' => $pelanggan->nama_desa,
                    'error'     => $e->getMessage(),
                ];
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        // Tampilkan hasil
        $this->info("✅ Berhasil: {$success}");
        if ($skipped > 0) {
            $this->comment("⏭️  Dilewati (sudah ada): {$skipped}");
        }
        if ($failed > 0) {
            $this->error("❌ Gagal: {$failed}");

            $this->newLine();
            $this->table(
                ['Kode Desa', 'Nama Desa', 'Error'],
                collect($errors)->map(fn ($e) => [$e['kode_desa'], $e['nama_desa'], $e['error']])->toArray()
            );
        }

        $this->newLine();
        $this->info('Selesai.');

        return $failed > 0 ? self::FAILURE : self::SUCCESS;
    }

    /**
     * Ambil daftar pelanggan berdasarkan filter kode_desa atau semua.
     */
    private function getPelangganList(?string $kodeDesa)
    {
        $query = Pelanggan::whereNotNull('domain_opensid')
            ->where('domain_opensid', '!=', '-');

        if ($kodeDesa) {
            $kodeDesaList = array_map('trim', explode(',', $kodeDesa));
            $query->whereIn('kode_desa', $kodeDesaList);
        }

        return $query->orderBy('nama_desa')->get();
    }
}
