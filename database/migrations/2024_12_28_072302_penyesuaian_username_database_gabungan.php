<?php

use App\Models\Aplikasi;
use App\Services\FileService;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $openkab = env('OPENKAB', false);
        $fileservice = new FileService();

        if ($openkab) {
            $folderMultisite = config('siappakai.root.folder_multisite');
            DB::table('pelanggan')->get()->each(function ($value) use ($folderMultisite, $fileservice) {
                $kodeDesa = $value->kode_desa;
                $kodeDesaWithoutDot = str_replace('.', '', $kodeDesa);
                $folderDesa = $folderMultisite . $kodeDesaWithoutDot;
                $DBconfigDesa = $folderDesa . DIRECTORY_SEPARATOR . 'desa' . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'database.php';
                // Nama database baru

                $userdb = 'user_' . $kodeDesaWithoutDot;
                $passdb = 'pass_' . $kodeDesaWithoutDot;
                $this->createMysqlUser($userdb, $passdb);

                $replace = [
                    '{$kodedesa}' => $kodeDesaWithoutDot,
                    '{$db_host}' => Aplikasi::where('key', 'ip_source_code')->first()->value ?? env('DB_HOST'),
                    '{$database}' => 'gabungan_premium'
                ];

                // perbarui file database.php
                $templateFolder = base_path() . DIRECTORY_SEPARATOR . 'master-template' . DIRECTORY_SEPARATOR . 'template-opensid' . DIRECTORY_SEPARATOR;
                $templateDatabase = $templateFolder . 'desa' . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'database.php';
                $fileservice->processTemplate($templateDatabase, $DBconfigDesa, $replace);
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {}

    /**
     * Membuat user MySQL dengan hak akses penuh.
     *
     * @param string $username Nama user MySQL.
     * @param string $password Password untuk user MySQL.
     * @return void
     */
    private function createMysqlUser(string $username, string $password): void
    {
        $ipDatabase = env('DB_HOST');
        $databaseName = 'db_gabungan_premium';
        DB::statement("CREATE USER IF NOT EXISTS '$username'@'$ipDatabase' IDENTIFIED BY '$password';");
        DB::statement("GRANT ALL PRIVILEGES ON `$databaseName`.* TO '$username'@'$ipDatabase' WITH GRANT OPTION;");
        DB::statement("FLUSH PRIVILEGES;");
    }
};
