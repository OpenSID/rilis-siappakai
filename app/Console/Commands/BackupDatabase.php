<?php

namespace App\Console\Commands;

use Exception;
use App\Services\DatabaseBackupService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class BackupDatabase extends Command
{
    /**
     * Nama dan tanda tangan dari perintah konsol.
     *
     * @var string
     */
    protected $signature = 'siappakai:backup-database';

    /**
     * Deskripsi perintah konsol.
     *
     * @var string
     */
    protected $description = 'Melakukan backup semua database di server (kecuali database bawaan MySQL/MariaDB)';

    /**
     * Membuat instance perintah baru.
     */
    public function __construct(
        private readonly DatabaseBackupService $backupDatabaseService
    ) {
        parent::__construct();
    }

    /**
     * Menjalankan perintah konsol untuk melakukan backup semua database.
     *
     * Alur:
     * 1. SHOW DATABASES → ambil semua database di server
     * 2. Exclude: information_schema, mysql, performance_schema, sys
     * 3. Loop per database: mysqldump → compress ZIP → simpan
     * 4. Jika satu database gagal, proses database lain tetap lanjut
     *
     * @return int Status kode eksekusi, 0 jika sukses, 1 jika gagal.
     */
    public function handle(): int
    {
        $folderBackup = rtrim(env('ROOT_OPENSID'), '/\\') . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'backup' . DIRECTORY_SEPARATOR . 'database';

        $this->info('==============================================');
        $this->info('  Backup Semua Database - Mulai');
        $this->info('==============================================');
        $this->info("Direktori backup: {$folderBackup}");
        $this->newLine();

        try {
            $summary = $this->backupDatabaseService->backupAllDatabases($folderBackup);

            $this->newLine();
            $this->info('==============================================');
            $this->info('  Ringkasan Backup');
            $this->info('==============================================');
            $this->info("Total database : {$summary['total']}");
            $this->info("Berhasil       : {$summary['success']}");

            if ($summary['failed'] > 0) {
                $this->error("Gagal          : {$summary['failed']}");
            } else {
                $this->info("Gagal          : {$summary['failed']}");
            }

            $this->newLine();

            // Tampilkan detail per database
            if (!empty($summary['details'])) {
                $tableData = [];
                foreach ($summary['details'] as $detail) {
                    $tableData[] = [
                        $detail['database'],
                        $detail['status'] === 'success' ? '✓ Berhasil' : '✗ Gagal',
                        $detail['file'] ?? ($detail['error'] ?? '-'),
                    ];
                }

                $this->table(['Database', 'Status', 'File / Error'], $tableData);
            }

            // Jika ada yang gagal, return 1 tapi proses tetap selesai
            if ($summary['failed'] > 0) {
                $this->warn("Backup selesai dengan {$summary['failed']} database gagal. Cek log untuk detail.");
                return 1;
            }

            // Semua database berhasil dibackup, update metadata tanggal terakhir backup
            $this->backupDatabaseService->updateTerakhirBackup();
            $this->info('Backup semua database selesai dengan sukses.');
            return 0;

        } catch (Exception $ex) {
            Log::error("Backup database fatal error: {$ex->getMessage()}");
            $this->error("Terjadi error fatal saat backup database: {$ex->getMessage()}");
            return 1;
        }
    }
}
