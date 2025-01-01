<?php

use App\Models\Aplikasi;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Spatie\DbDumper\Databases\MySql;
use Illuminate\Database\Migrations\Migration;

class MigrateAllTablesWithBackup extends Migration
{
    /**
     * Perintah untuk menjalankan migrasi.
     *
     * @return void
     */
    public function up()
    {
        // Mendapatkan kode desa
        $kodeDesa = Aplikasi::where('key', 'kode_wilayah')->first();
        $dbLamaName = "db_" . str_replace('.', '', $kodeDesa->value);

        if (!$this->checkIfDatabaseExists($dbLamaName)) {
            echo "Database lama tidak ditemukan. Proses migrasi dihentikan.\n";
            return;
        }

        // Nama database baru
        $newDatabaseName = 'db_gabungan_premium';

        if (!$this->checkIfDatabaseExists($newDatabaseName)) {
            // Membuat database baru jika belum ada
            echo "Membuat database baru: $newDatabaseName...\n";

            DB::statement("CREATE DATABASE IF NOT EXISTS `$newDatabaseName`");
        }

        // Melakukan backup database lama
        if (!$this->backupDatabase($dbLamaName)) {
            echo "Backup database gagal. Proses migrasi dibatalkan.\n";
            return;
        }

        echo "Migrasi semua tabel selesai.\n";
    }



    public function restoreDatabase($backupFile)
    {

        $host = env('DB_HOST', '127.0.0.1');
        $username = env('DB_USERNAME', 'root');
        $password = env('DB_PASSWORD', '');
        $port = env('DB_PORT', '3306');

        // Perintah untuk melakukan restore database menggunakan mysql
        $command = "mysql -h $host -P $port -u $username -p'$password' 'db_gabungan_premium' < $backupFile";

        // Menjalankan perintah restore
        exec($command, $output, $resultCode);

        if ($resultCode === 0) {
            echo "Restore database berhasil.\n";
            return true;
        } else {
            echo "Restore database gagal.\n";
            return false;
        }
    }

    /**
     * Melakukan backup database menggunakan Spatie\DbDumper.
     *
     * @param string $dbName
     * @return bool
     */
    private function backupDatabase($dbName)
    {
        $backupDirectory = storage_path('app/backups');
        if (!file_exists($backupDirectory)) {
            mkdir($backupDirectory, 0775, true);
        }
        $backupFile = $backupDirectory . '/db_lama_backup_' . date('Y-m-d_H-i-s') . '.sql';
        $donotusecolumnstatistics = DB::table('pengaturan_aplikasi')->where('key', 'donotusecolumnstatistics')->first()->value;

        try {
            $mysqlDump = MySql::create()
                ->setHost(env('DB_HOST', '127.0.0.1'))
                ->setPort(env('DB_PORT', '3306'))
                ->setDbName($dbName)
                ->setUserName(env('DB_USERNAME', 'root'))
                ->setPassword(env('DB_PASSWORD', ''));

            // Tambahkan opsi jika column statistics dinonaktifkan.
            if ($donotusecolumnstatistics == 1) {
                $mysqlDump = $mysqlDump->addExtraOption('--column-statistics=0');
            }

            $mysqlDump->dumpToFile($backupFile);

            echo "Backup database berhasil: $backupFile\n";
            $this->restoreDatabase($backupFile);
            return true;
        } catch (\Exception $e) {
            Log::error('Backup database gagal: ' . $e->getMessage());
            echo "Backup database gagal. Error: " . $e->getMessage() . "\n";
            return false;
        }
    }

    /**
     * Memeriksa apakah database ada.
     *
     * @param string $databaseName
     * @return bool
     */
    private function checkIfDatabaseExists($databaseName)
    {
        $pdo = $this->createPDOConnection('information_schema');
        $result = $pdo->query("SELECT SCHEMA_NAME FROM SCHEMATA WHERE SCHEMA_NAME = '$databaseName'")->fetch();

        return $result !== false;
    }

    /**
     * Membuat koneksi PDO ke database.
     *
     * @param string $databaseName Nama database yang akan dihubungkan.
     * @return PDO Koneksi PDO yang telah dibuat.
     *
     * @throws PDOException jika koneksi gagal.
     */
    private function createPDOConnection($databaseName)
    {
        $host = env('DB_HOST', '127.0.0.1');
        $username = env('DB_USERNAME', 'root');
        $password = env('DB_PASSWORD', '');

        try {
            $dsn = "mysql:host=$host;dbname=$databaseName";
            return new PDO($dsn, $username, $password);
        } catch (PDOException $e) {
            die("Connection failed: " . $e->getMessage());
        }
    }

    /**
     * Rollback untuk migrasi.
     *
     * @return void
     */
    public function down()
    {
        // Tidak diperlukan rollback
    }
}
