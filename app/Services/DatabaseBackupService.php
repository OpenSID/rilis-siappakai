<?php

namespace App\Services;

use Exception;
use Carbon\Carbon;
use App\Enums\SiapPakai;
use App\Models\Aplikasi;
use App\Models\Pelanggan;
use App\Services\GitService;
use App\Services\ZipService;
use App\Enums\RepositoryEnum;
use Illuminate\Support\Facades\Log;
use Spatie\DbDumper\Databases\MySql;

class DatabaseBackupService
{
    /**
     * Membuat instance baru dari DatabaseBackupService.
     *
     * @param string $folder Lokasi folder untuk menyimpan file backup.
     */
    public function __construct() {}

    /**
     * Membuat file backup database OpenSID ke lokasi tertentu.
     *
     * @param string $dbName Nama database yang akan dibackup.
     * @param string $folder Lokasi folder untuk menyimpan file backup.
     *
     * @throws Exception Jika terjadi error saat proses backup.
     *
     * @return string Lokasi file backup yang berhasil dibuat.
     */
    public function backupDatabaseOpensid($dbName, $folder)
    {
        try {
            // Memastikan folder ada
            $this->ensureFolderExists($folder);

            $customers = Pelanggan::where('status_langganan_opensid', 1);

            if (env('OPENKAB') == true) {

                $this->processSingleCustomerBackup($customers->first(), $dbName, $folder);
            } else {
                foreach ($customers->get() as $customer) {
                    $this->processSingleCustomerBackup($customer, "db_{$customer->kode_desa}", $folder);
                }
            }
        } catch (Exception $ex) {
            Log::error("Error during backup for {$dbName}", [$ex]);
            throw $ex;
        }
    }

    /**
     * Membuat file backup database OpenSID untuk pelanggan tertentu.
     *
     * @param Pelanggan $customer Pelanggan yang akan dibackup.
     * @param string    $dbName  Nama database yang akan dibackup.
     * @param string    $folder  Lokasi folder untuk menyimpan file backup.
     */
    private function processSingleCustomerBackup($customer, $dbName, $folder)
    {
        if ($this->shouldSkipBackup($customer)) {
            echo "Pelanggan {$customer->kode_desa} Sudah melakukan backup pada tanggal {$customer->tgl_akhir_backup}\n";
            return;
        }

        $backup = $this->createBackupInstance($dbName);
        $dbFile = "{$folder}/{$dbName}.sql";
        $backup->dumpToFile($dbFile);
        $this->archiveBackup($folder, $dbName, $dbFile);
    }

    /**
     * Memeriksa apakah backup untuk pelanggan tertentu perlu di skip atau tidak.
     *
     * Backup akan di skip jika:
     * 1. Tanggal akhir backup pelanggan lebih besar dari tanggal pengaturan.
     * 2. Versi Git untuk OpenSID Umum belum terbaru.
     * 3. Versi Git untuk OpenSID Premium belum terbaru.
     *
     * @param Pelanggan $customer Pelanggan yang akan di cek.
     *
     * @return bool True jika backup perlu di skip, false jika tidak.
     */
    private function shouldSkipBackup($customer)
    {

        return $this->cekTglAkhirBackup($customer->tgl_akhir_backup) &&
            (!$this->cekVersiGitTerbaru('umum') ||
                !$this->cekVersiGitTerbaru('premium'));
    }

    /**
     * Membuat file backup database PBB untuk setiap pelanggan yang memiliki
     * status langganan OpenSID yang aktif.
     *
     * @param string $folder Lokasi folder untuk menyimpan file backup.
     *
     * @throws Exception Jika terjadi error saat proses backup.
     */
    public function backupDatabasePBB($folder)
    {
        try {
            // Memastikan folder ada
            $this->ensureFolderExists($folder);

            // Mendapatkan daftar pelanggan dengan status langganan OpenSID aktif
            $customers = Pelanggan::where('status_langganan_opensid', 1)->get();

            foreach ($customers as $customer) {
                // Mengecek apakah backup sudah dilakukan baru-baru ini
                if ($this->cekTglAkhirBackup($customer->tgl_akhir_backup) && !$this->cekVersiGitTerbaru('pbb')) {
                    echo "Pelanggan {$customer->kode_desa} Sudah melakukan backup pada tanggal {$customer->tgl_akhir_backup}\n";
                    continue;
                }

                echo "Memulai proses backup untuk pelanggan {$customer->kode_desa}\n";

                // Menghilangkan titik dari kode desa untuk nama database
                $kodeDesaWithoutDot = str_replace('.', '', $customer->kode_desa);
                $dbName = "db_{$kodeDesaWithoutDot}_pbb";
                $dbFilePbb = "{$folder}/{$dbName}.sql";

                // Membuat instance backup dan menyimpan file SQL
                $backup = $this->createBackupInstance($dbName);
                $backup->dumpToFile($dbFilePbb);

                // Mengarsipkan file SQL ke format tar.gz
                $this->archiveBackup($folder, $dbName, $dbFilePbb);
            }
        } catch (Exception $ex) {
            // Mencatat error ke log jika terjadi kesalahan
            Log::error("Error during backup for {$dbName}", [$ex]);
            throw $ex;
        }
    }

    /**
     * Membuat file backup database SiapPakai dan simpan hasilnya di folder $folder.
     *
     * @param string $folder Lokasi folder untuk menyimpan file backup.
     *
     * @throws Exception Jika terjadi error saat proses backup.
     */
    function backupDatabaseSiappakai($folder)
    {
        try {
            $dbName = SiapPakai::DB_NAME;
            // Memastikan folder ada
            $this->ensureFolderExists($folder);

            // Membuat instance backup untuk database SiapPakai
            $backup = $this->createBackupInstance($dbName);
            $backupFile = $folder . '/' . $dbName . '.sql';
            // Menyimpan file backup ke lokasi yang ditentukan
            $backup->dumpToFile($backupFile);

            // Mengarsipkan file SQL ke format tar.gz
            $this->archiveBackup($folder, $dbName, $backupFile);
        } catch (Exception $ex) {
            // Mencatat error ke log jika terjadi kesalahan
            Log::error("Error during backup for {$dbName}", [$ex]);
            throw $ex;
        }
    }

    /**
     * Membuat instance dari DatabaseBackupService berdasarkan nama database yang diberikan.
     *
     * @param string $dbName Nama database yang akan dibuatkan instance backup-nya.
     *
     * @return \Spatie\DbDumper\Databases\MySql Instance dari DatabaseBackupService.
     */
    private function createBackupInstance($dbName)
    {
        // Mendapatkan konfigurasi database dari file konfigurasi
        $dbConfig = config('database.connections.mysql');
        $donotusecolumnstatistics = Aplikasi::pengaturan_aplikasi()['donotusecolumnstatistics'];
        // Membuat instance MySql untuk backup
        $backup = MySql::create()
            ->setDbName($dbName)
            ->setUserName($dbConfig['username'])
            ->setPassword($dbConfig['password'])
            ->setHost($dbConfig['host'])
            ->setPort($dbConfig['port']);

        // Menonaktifkan penggunaan statistik kolom jika diatur
        if ($donotusecolumnstatistics == 1) {
            $backup = $backup->addExtraOption('--column-statistics=0');
        }

        return $backup;
    }

    /**
     * Membuat arsip dari file backup yang sudah dibuat menjadi file tar.gz.
     *
     * @param string $folder Folder tempat menyimpan arsip backup.
     * @param string $dbName Nama database yang dibuatkan arsip backup-nya.
     * @param string $backupFile Path ke file backup yang akan diarsipkan.
     */
    private function archiveBackup($folder, $dbName, $backupFile)
    {
        // Memastikan folder ada
        $this->ensureFolderExists($folder);

        // Membuat instance ZipService
        $zipService = new ZipService();
        // Membuat arsip file backup menjadi file zip
        $zipService->zipFile("{$folder}/{$dbName}.zip", $backupFile);
    }

    /**
     * Membuat folder jika belum ada.
     *
     * @param string $folder Nama folder yang akan dibuat.
     *
     * @return void
     */
    private function ensureFolderExists($folder)
    {
        if (!is_dir($folder)) {
            mkdir($folder, 0755, true);
        }
    }

    /**
     * Update tanggal akhir backup pelanggan yang aktif.
     *
     * @return void
     */
    function updateTerakhirBackup(): void
    {
        // update tanggal akhir pelanggan;
        Pelanggan::where('status_langganan_opensid', 1)->update([
            'tgl_akhir_backup' => Carbon::now()
        ]);
    }

    /**
     * Mengecek apakah backup sudah dilakukan lebih dari satu hari yang lalu.
     *
     * @param string $tglbackup Tanggal akhir backup.
     *
     * @return bool True jika selisih lebih dari pangaturan aplikasi, false jika tidak.
     */
    function cekTglAkhirBackup($tglbackup)
    {
        // Mendapatkan tanggal hari ini
        $today = Carbon::today();

        // Menghitung selisih hari antara hari ini dan tanggal backup
        $selisihHari = $today->diffInDays($tglbackup, false); // Parameter false agar mendapatkan nilai negatif jika tanggal sudah berlalu

        // Mengembalikan hasil pengecekan
        $result = $selisihHari >= Aplikasi::pengaturan_aplikasi()['waktu_backup'];

        return $result;
    }

    /**
     * Mengecek apakah versi Git terbaru lebih besar dari versi Server.
     *
     * @param string $app Nama aplikasi yang akan di cek.
     *
     * @return bool True jika versi Git terbaru lebih besar, false jika tidak.
     */
    function cekVersiGitTerbaru($app)
    {
        $gitService = new GitService();

        /**
         * Mendapatkan repository enum berdasarkan nama folder aplikasi
         */

        $repoEnum = RepositoryEnum::fromFolderName(strtolower($app));

        /**
         * Mengambil tag rilis terbaru dari GitHub
         */
        $versionTag = $gitService->getLastRelease($repoEnum, $app)['tag_name'];

        /**
         * Menyaring angka dari versi tag rilis Git
         */
        $versionGit = preg_replace('/[^0-9]/', '', $versionTag);

        /**
         * Mendapatkan nama folder master berdasarkan nama aplikasi
         */

        $folderMaster = RepositoryEnum::getFolderMaster(strtolower($app));

        if (!is_dir($folderMaster)) {
            return false;
        }

        /**
         * Menjalankan perintah git untuk mendapatkan tag versi dari server
         */
        $tags_server = exec('cd ' . $folderMaster . ' && git describe --tags');
        $content_versi = substr($tags_server, 0, 9);

        /**
         * Menyaring angka dari versi tag server
         */
        $versionServer = preg_replace('/[^0-9]/', '', $content_versi);

        /**
         * Membandingkan versi Git dan versi Server
         * @return bool True jika versi Git lebih baru, false jika tidak
         */
        return substr($versionGit, 0, 6) > substr($versionServer, 0, 6);
    }
}
