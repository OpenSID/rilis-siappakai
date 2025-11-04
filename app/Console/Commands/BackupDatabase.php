<?php

namespace App\Console\Commands;

use Exception;
use App\Services\AplikasiService;
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
    protected $description = 'Melakukan backup database OpenSID (dapat dilakukan melalui cronjob jika ada rilis terbaru)';

    private $aplikasiService;
    private $backupDatabaseService;

    /**
     * Membuat instance perintah baru.
     *
     * @param BackupFolderService $backupFolderService
     * @param DatabaseBackupService $backupDatabaseService
     * @param TarGzService $tarGzService
     * @return void
     */
    public function __construct(AplikasiService $aplikasiService,
        DatabaseBackupService $backupDatabaseService
    ) {
        parent::__construct();
        $this->backupDatabaseService = $backupDatabaseService;
        $this->aplikasiService = $aplikasiService;
    }

    /**
     * Menjalankan perintah konsol untuk melakukan backup database.
     *
     * @return int Status kode eksekusi, 0 jika sukses, 1 jika gagal.
     */
    public function handle()
    {
        // Menentukan direktori untuk menyimpan backup
        $folder_backup = siappakai_storage() . DIRECTORY_SEPARATOR . 'backup';
        $folder_backup_database = $folder_backup . DIRECTORY_SEPARATOR . 'database';
        $backup = false;

        try {
            // Membuat backup untuk database Siappakai
            echo "Memulai backup database Siappakai\n";
            $this->backupDatabaseService->backupDatabaseSiappakai($folder_backup_database);
            echo "Backup database Siappakai selesai\n";

            if ($this->aplikasiService->cekPengaturanOpensid('umum')) {
                // Membuat file backup database umum
                // dengan nama file berupa db_gabungan_umum.sql
                // dan menyimpannya di folder $folder_backup_database
                echo "Memulai backup database Umum\n";
                $this->backupDatabaseService->backupDatabaseOpensid('db_gabungan_umum', $folder_backup_database);
                echo "Backup database umum selesai\n";

                // Setelah backup selesai, maka variabel $backup di setelai menjadi true
                $backup == true;
            }

            if ($this->aplikasiService->cekPengaturanOpensid('premium')) {
                // Membuat file backup database premium
                // dengan nama file berupa db_gabungan_premium.sql
                // dan menyimpannya di folder $folder_backup_database
                echo "Memulai backup database premium\n";
                $this->backupDatabaseService->backupDatabaseOpensid('db_gabungan_premium', $folder_backup_database);
                echo "Backup database premium selesai\n";

                // Membuat backup untuk database PBB
                echo "Memulai backup database Pbb\n";
                $this->backupDatabaseService->backupDatabasePBB($folder_backup_database);
                echo "Backup database Pbb selesai\n";

                // Setelah backup selesai, maka variabel $backup di setelai menjadi true
                $backup = true;
            }

            // Jika backup berhasil, maka update tanggal akhir backup
            if ($backup) {
                // Mengupdate tanggal akhir backup
                $this->backupDatabaseService->updateTerakhirBackup();
            }

        } catch (Exception $ex) {
            // Mencatat error ke log dan menampilkan pesan error
            Log::error($ex->getMessage());
            echo "Terjadi error saat backup database: " . $ex->getMessage();
            return 1;
        }

        return 0;
    }
}
