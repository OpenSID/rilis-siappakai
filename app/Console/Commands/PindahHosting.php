<?php

namespace App\Console\Commands;

use App\Http\Controllers\Helpers\AttributeSiapPakaiController;
use App\Http\Controllers\Helpers\CommandController;
use App\Http\Controllers\Helpers\KoneksiController;
use App\Models\Aplikasi;
use App\Services\ProcessService;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class PindahHosting extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'siappakai:pindah-hosting {--kode_desa=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Pindah hosting ke Dasbor SiapPakai';

    /** Langkah yang dilakukan pada siappakai:
     * 1. Hapus Database yang dibuat ketika generate Pelanggan Baru,
     * 2. Buat Database baru,
     * 3. Restore Database dari Pindahan,
     * 4. Hapus Database OpenSID pada directory database,
     * 5. Hapus Folder Desa yang dibuat ketika generate Pelanggan Baru,
     * 6. Restore Folder Desa,
     * 7. Hapus Folder Desa pada directory folder-desa,
     * 8. Sesuaikan konfigurasi config.php dan database.php, termasuk aktivasi tema pro (dilakukan di Layanan),
     */

    /** Langkah yang dilakukan pada Aplikasi PBB:
     * 1. Hapus Database yang dibuat ketika generate Pelanggan Baru,
     * 2. Buat Database Baru,
     * 3. Restore Database dari Pindahan,
     * 4. Hapus Database PBB pada directory database
     */

    private $att;
    private $comm;
    private $koneksi;
    private $ip_source_code;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->att = new AttributeSiapPakaiController();
        $this->comm = new CommandController();
        $this->koneksi = new KoneksiController();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $kode_desa_default = $this->option('kode_desa');
        $kode_desa = str_replace('.', '', $kode_desa_default);
        $path_public = public_path() . DIRECTORY_SEPARATOR . "backup" . DIRECTORY_SEPARATOR;
        $path_database = $path_public . 'database' . DIRECTORY_SEPARATOR;
        $this->ip_source_code = env('OPENKAB') == 'true' ? Aplikasi::pengaturan_aplikasi()['ip_source_code'] : 'localhost';

        $this->att->setSiteFolderOpensid($this->att->getMultisiteFolder() . $kode_desa);
        $this->att->setIndexDesa($this->att->getSiteFolderOpensid() . DIRECTORY_SEPARATOR . 'index.php');

        $this->att->setUsername('user_' . $kode_desa);
        $this->att->setPassword('pass_' . $kode_desa);

        if (env('OPENKAB') == 'true') {
            $this->att->setDatabase('db_' . $kode_desa . '_pbb');
            $this->att->setDatabasePbb('db_' . $kode_desa . '_pbb');
        } else {
            $this->att->setDatabase('db_' . $kode_desa);
            $this->att->setDatabasePbb('db_' . $kode_desa . '_pbb');
        }

        try {
            $koneksi = mysqli_connect($this->att->getHost(), $this->att->getUsername(), $this->att->getPassword(), $this->att->getDatabase());
            /** Proses database */
            if ($koneksi) {
                if (file_exists($path_database . 'db_' . $kode_desa . '.sql') && env('OPENKAB') == 'false') {
                    /** 1. hapus database opensid lama */
                    DB::statement('DROP DATABASE db_' . $kode_desa);

                    /** 2. buat database opensid baru */
                    $this->createDatabaseOpenSID($kode_desa);

                    /** 3. restore ke database baru */
                    $this->restoreDatabase($path_public);

                    /** 4. Hapus file database opensid */
                    $this->comm->removeFile($path_database . 'db_' . $kode_desa . '.sql');
                }
                if (file_exists($path_database . 'db_' . $kode_desa . '_pbb.sql')) {
                    /** 1. hapus database pbb lama */
                    DB::statement('DROP DATABASE db_' . $kode_desa . "_pbb");

                    /** 2. buat database pbb baru */
                    $this->createDatabasePbb($kode_desa);

                    /** 3. restore ke database baru */
                    $this->restoreDatabasePbb($path_public);

                    /** 4. Hapus file database pbb */
                    $this->comm->removeFile($path_database . 'db_' . $kode_desa . '_pbb.sql');
                }
            }

            /** 5. Hapus Folder Desa yang dibuat ketika generate Pelanggan Baru*/
            $this->hapusFolderDesa($kode_desa);
            $this->info('hapus folder desa');

            /** 6. Restore Folder Desa */
            $this->restoreFolderDesa($path_public, $kode_desa);
            $this->info('restore folder desa');

            /** 7. Hapus Folder Desa pada directory folder-desa */
            $this->hapusBackupFolderDesa($path_public, $kode_desa);
            $this->info('hapus backup folder desa');

            /** Hapus folder tmp */
            if (file_exists(Storage::path('livewire-tmp'))) {
                $this->comm->removeDirectory(Storage::path('livewire-tmp'));
            }

            ProcessService::aturKepemilikanDirektori(config('siappakai.root.folder'));
            return die("Informasi : berhasil memindahkan hosting ke Dasbor SiapPakai!!!");
        } catch (Exception $ex) {
            Log::error($ex->getMessage());
            return die("Peringatan : database tidak ditemukan !!!");
        }
    }

    private function createDatabaseOpenSID($kodedesa)
    {
        $database = $this->koneksi->cekDatabase($kodedesa);

        if ($database == false) {
            DB::statement("CREATE DATABASE db_$kodedesa");
            DB::statement("GRANT ALL PRIVILEGES ON db_$kodedesa.* TO 'user_$kodedesa'@'$this->ip_source_code' WITH GRANT OPTION");
            DB::statement("FLUSH PRIVILEGES");
        }
    }

    private function createDatabasePbb($kodedesa)
    {
        $database = $this->koneksi->cekDatabasePbb($kodedesa);

        if ($database == false) {
            $namadbuser = $kodedesa . "_pbb";
            DB::statement("CREATE DATABASE db_" . $namadbuser);
            DB::statement("GRANT ALL PRIVILEGES ON db_$namadbuser.* TO 'user_$kodedesa'@'$this->ip_source_code' WITH GRANT OPTION");
            DB::statement("FLUSH PRIVILEGES");
        }
    }

    private function restoreDatabase($path_public)
    {
        $path = $path_public . "database" . DIRECTORY_SEPARATOR;

        // perlu diperhatikan spasi karena akan mempengaruhi
        $restore = "mysql --no-defaults --host=" . $this->att->getHost() .
            " --port=" . $this->att->getPort() .
            " --user=" . $this->att->getUsername() .
            " --password=" . $this->att->getPassword() .
            " -v " . $this->att->getDatabase() .
            " < " . $path . $this->att->getDatabase() . ".sql";
        exec($restore);
    }

    private function restoreDatabasePbb($path_public)
    {
        $path = $path_public . "database" . DIRECTORY_SEPARATOR;

        if (file_exists($path . $this->att->getDatabasePbb() . ".sql")) {
            // perlu diperhatikan spasi karena akan mempengaruhi
            $restore = "mysql --no-defaults --host=" . $this->att->getHost() .
                " --port=" . $this->att->getPort() .
                " --user=" . $this->att->getUsername()() .
                " --password=" . $this->att->getPassword() .
                " -v " . $this->att->getDatabasePbb() .
                " < " . $path . $this->att->getDatabasePbb() . ".sql";
            exec($restore);
        } else {
            return die("Peringatan : database tidak ditemukan !!!");
        }
    }

    private function hapusFolderDesa($kode_desa)
    {
        $multisite_desa = $this->att->getMultisiteFolder() . $kode_desa . DIRECTORY_SEPARATOR . 'desa';

        if (File::isDirectory($multisite_desa)) {
            // pindah sementara folder config untuk backup (update menjadi config_desa)
            $this->comm->moveFile($multisite_desa . DIRECTORY_SEPARATOR . 'config', $this->att->getMultisiteFolder() . $kode_desa . DIRECTORY_SEPARATOR . 'config_desa');

            // pindah sementara file app_key untuk backup
            $this->comm->moveFile($multisite_desa . DIRECTORY_SEPARATOR . 'app_key', $this->att->getMultisiteFolder() . $kode_desa . DIRECTORY_SEPARATOR . 'app_key');

            // hapus folder desa existing
            $this->comm->removeDirectory($multisite_desa);
        }
    }

    private function restoreFolderDesa($path_public, $kode_desa)
    {
        $folder_desa = $path_public . 'folder-desa' . DIRECTORY_SEPARATOR . 'desa_' . $kode_desa;
        $multisite_desa = $this->att->getMultisiteFolder() . $kode_desa . DIRECTORY_SEPARATOR . 'desa';

        $this->comm->copyDirectory($folder_desa, $multisite_desa);

        if (file_exists($multisite_desa)) {
            // hapus folder config pindahan
            $this->comm->removeDirectory($multisite_desa . DIRECTORY_SEPARATOR . 'config');

            // pindah dari backup sementara (update ambil dari config_desa)
            $this->comm->moveFile($this->att->getMultisiteFolder() . $kode_desa . DIRECTORY_SEPARATOR . 'config_desa', $multisite_desa . DIRECTORY_SEPARATOR . 'config');

            // hapus app_key dari restore
            $this->comm->removeFile($multisite_desa . DIRECTORY_SEPARATOR . 'app_key');

            // pindahkan app_key dari backup sementara
            $this->comm->moveFile($this->att->getMultisiteFolder() . $kode_desa . DIRECTORY_SEPARATOR . 'app_key', $multisite_desa . DIRECTORY_SEPARATOR . 'app_key');
        }

        $this->comm->chownCommand($this->att->getMultisiteFolder() . $kode_desa);
        $this->comm->chmodDirectoryDesa($this->att->getMultisiteFolder() . $kode_desa);
    }

    public function hapusBackupFolderDesa($path_public, $kode_desa)
    {
        $folder = $path_public . 'folder-desa' . DIRECTORY_SEPARATOR . 'desa_' . $kode_desa;
        $this->comm->removeDirectory($folder);
    }
}
