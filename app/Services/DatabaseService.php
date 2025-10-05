<?php

namespace App\Services;

use Exception;
use App\Services\ConsoleService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DatabaseService
{
    private $ip_source_code;

    /**
     * Konstruktor untuk DatabaseService.
     *
     * @param object $koneksi        Objek koneksi untuk mengecek keberadaan database.
     * @param string $ip_source_code IP source code yang digunakan untuk mengatur hak akses user.
     */
    public function __construct($ip_source_code)
    {
        $this->ip_source_code = $ip_source_code;
    }

    /**
     * Membuat database untuk PBB jika belum ada.
     *
     * @param string $siappakai_pbb Nama unik yang digunakan sebagai bagian dari nama database.
     * @return void
     */
    public function createDatabase(string $nama_database): void
    {
        $dbConfig = $this->getDatabaseConfig($nama_database);

        // Cek jika database tidak ada
        // Jika tidak ada, lakukan pembuatan database
        if ($this->cekDatabase($dbConfig) == false) {

            DB::statement("CREATE DATABASE " . $dbConfig['database']);
            ConsoleService::info('Berhasil buat database ' . $dbConfig['database'] . "\n");
        }
    }


    /**
     * Membuat user untuk database OpenSID.
     *
     * User akan dibuat jika belum ada di tabel mysql.user.
     * Jika user sudah ada, maka tidak akan dilakukan apa-apa.
     *
     * @param string $nama_database Nama database yang akan dibuatkan user.
     * @param string $kode_desa      Kode desa yang akan dibuatkan user.
     *                               Jika null, maka akan digunakan nama database
     *                               sebagai bagian dari nama user.
     */
    public function createUser(string $nama_database, $kode_desa = null): void
    {
        // Ambil konfigurasi database
        // Jika $kode_desa tidak null, maka akan digunakan sebagai bagian dari nama user
        // Jika $kode_desa null, maka akan digunakan nama database sebagai bagian dari nama user
        $dbConfig = $this->getDatabaseConfig($nama_database, $kode_desa);

        // Cek apakah user sudah ada di tabel mysql.user
        $userExists = DB::selectOne(
            "SELECT COUNT(*) AS count
             FROM mysql.user
             WHERE user = ?
               AND host = ?",
            [$dbConfig['user'], $this->ip_source_code]
        );

        if ($userExists->count > 0) {
            ConsoleService::info("User '" . $dbConfig['user'] . "' sudah ada.\n");
        } else {
            // Jika user belum ada, buat user
            DB::statement("CREATE USER '" . $dbConfig['user'] . "'@'$this->ip_source_code' IDENTIFIED BY '" . $dbConfig['pass'] . "'");
            ConsoleService::info("User '" . $dbConfig['user'] . "' berhasil dibuat.\n");
        }

        // berikan hak akses ke database

        DB::statement("GRANT ALL PRIVILEGES ON " . $dbConfig['database'] . ".* TO '" . $dbConfig['user'] . "'@'$this->ip_source_code' WITH GRANT OPTION");
        DB::statement("FLUSH PRIVILEGES");

        ConsoleService::info("User '" . $dbConfig['user'] . "' ip : " . $this->ip_source_code . " berhasil diberikan akses ke " . $dbConfig['database'] . ".\n");
    }

    /**
     * Mengecek keberadaan database berdasarkan nama yang diberikan.
     *
     * @param string $dbConfig  config database yang akan dicek.
     * @return bool True jika database ditemukan, false jika tidak.
     */
    public function cekDatabase(array $dbConfig): bool
    {
        // Lakukan pengecekan koneksi
        // gunakan username dan password yang ada di env
        // pastikan username dan password benar dan mempunya hak akses
        // keseluruhan database
        try {
            $resultCekDatabase = DB::select("SHOW DATABASES LIKE '{$dbConfig['database']}';");

            if (empty($resultCekDatabase)) {
                return false;
            } else {
                ConsoleService::info("Informasi: database {$dbConfig['database']} sudah ada!");
                return true;
            }
        } catch (\Throwable  $ex) {
            Log::notice($ex->getMessage());
            ConsoleService::info($ex->getMessage() . "\n");
            // hentikan proses
            ConsoleService::info("Proses dihentikan. \n");
            die();
        }
    }

    /**
     * Mengambil konfigurasi database berdasarkan nama.
     *
     * @param string $nama Nama database.
     * @return array Konfigurasi database (host, port, username, password, database).
     */
    public function getDatabaseConfig(string $nama, string $kode_desa = null): array
    {
        if (env('DB_DATABASE') == $nama) {
            return [
                'user' => env('DB_USERNAME'),
                'pass' => env('DB_PASSWORD'),
                'database' => env('DB_DATABASE'),
            ];
        }

        return [
            'user' => 'user_' . ($kode_desa ?? $nama),
            'pass' => 'pass_' . ($kode_desa ?? $nama),
            'database' => 'db_' . $nama,
        ];
    }

    public function tableExists(string $database, string $table): bool
    {
        try {
            $resultCekDatabase = $this->cekDatabase(['database' => $database]);
            if (!$resultCekDatabase) {
                return false;
            }

            $result = DB::select("SELECT TABLE_NAME FROM information_schema.TABLES WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ?", [$database, $table]);
            return !empty($result);
        } catch (Exception $ex) {
            Log::error($ex->getMessage());
            return false;
        }
    }
}
