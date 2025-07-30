<?php

namespace App\Console\Commands;

use App\Http\Controllers\Helpers\AttributeSiapPakaiController;
use App\Http\Controllers\Helpers\CommandController;
use App\Http\Controllers\Helpers\IndexController;
use App\Http\Controllers\Helpers\KoneksiController;
use App\Models\Aplikasi;
use App\Models\Pelanggan;
use App\Services\ProcessService;
use Carbon\Carbon;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;

class MundurVersi extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'siappakai:mundur-versi {--kode_desa=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Mundur versi sebelumnya';

    /** Langkah yang dilakukan :
     * 1. Hapus Database,
     * 2. Buat Database,
     * 3. Restore Database dari Backup,
     * 4. Ubah versi pada tabel pelanggan,
     * 5. Hapus folder-desa yang lama,
     * 6. Restore Folder Desa.
     * 7. Ubah Symlink sesuai dengan versi,
     */

    private $att;
    private $comm;
    private $filesIndex;
    private $koneksi;
    private $ip_source_code;
    private $pelanggans;

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
        $this->filesIndex = new IndexController();
        $this->koneksi = new KoneksiController();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->pelanggans = Pelanggan::get();
        $kode_desa_default = $this->option('kode_desa');
        $kode_desa = str_replace('.', '', $kode_desa_default);
        $path_public = siappakai_storage() . DIRECTORY_SEPARATOR . "backup" . DIRECTORY_SEPARATOR;
        $openkab = env('OPENKAB') == 'true' ? nama_database_gabungan() : $kode_desa;
        $this->ip_source_code = env('OPENKAB') == 'true' ? Aplikasi::pengaturan_aplikasi()['ip_source_code'] : 'localhost';

        $this->att->setUsername('user_' . $openkab);
        $this->att->setPassword('pass_' . $openkab);
        $this->att->setDatabase('db_' . $openkab);

        try {
            $koneksi = mysqli_connect($this->att->getHost(), $this->att->getUsername(), $this->att->getPassword(), $this->att->getDatabase());

            // 1. hapus database lama
            if ($koneksi) {
                DB::statement('DROP DATABASE db_' . $openkab);
            }

            // 2. buat database baru
            $this->createDatabase($openkab);

            // 3. restore ke database baru
            if ($koneksi) {
                $this->restoreDatabase($path_public);
            }

            // kondisi dgn kode_desa_default
            $telat = 0;
            $pelanggan = Pelanggan::where('kode_desa', $kode_desa_default)->first();
            if ($pelanggan->tgl_akhir_premium < now()) {
                $tglAkhirPremium = Carbon::parse($pelanggan->tgl_akhir_premium);
                $sekarang = Carbon::now();

                $selisihBulan = $tglAkhirPremium->diffInMonths($sekarang);

                $telat = $selisihBulan;
            }

            $versi_sebelumnya = $this->versiSebelumnya($telat);

            if (env('OPENKAB') == 'true') {
                foreach ($this->pelanggans as $item) {
                    $this->updatePelanggan($versi_sebelumnya, $item->kode_desa);
                    $this->hapusFolderDesa(str_replace('.', '', $item->kode_desa));
                    $this->restoreFolderDesa($path_public, str_replace('.', '', $item->kode_desa));
                    $this->unlinkFolder($versi_sebelumnya, str_replace('.', '', $item->kode_desa));
                    $this->setFolderOpensid($versi_sebelumnya, str_replace('.', '', $item->kode_desa));
                }
            } else {
                // 4. Ubah versi pada tabel pelanggan
                $this->updatePelanggan($versi_sebelumnya, $kode_desa_default);

                // 5. Hapus folder-desa yang lama
                $this->hapusFolderDesa($kode_desa);

                // 6. Restore Folder Desa
                $this->restoreFolderDesa($path_public, $kode_desa);

                // 7. Ubah Symlink sesuai dengan versi
                $this->unlinkFolder($versi_sebelumnya, $kode_desa);
                $this->setFolderOpensid($versi_sebelumnya, $kode_desa);
            }
            ProcessService::aturKepemilikanDirektori(config('siappakai.root.folder'));
            return die("Informasi : berhasil memulihkan database dan mundur versi sebelumnya !!!");
        } catch (Exception $ex) {
            return die("Peringatan : database tidak ditemukan !!!");
        }
    }

    private function createDatabase($kodedesa)
    {
        $database = $this->koneksi->cekDatabase($kodedesa);

        if ($database == false) {
            DB::statement("CREATE DATABASE db_$kodedesa");
            DB::statement("GRANT ALL PRIVILEGES ON db_$kodedesa.* TO 'user_$kodedesa'@'$this->ip_source_code' WITH GRANT OPTION");
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

    private function versiSebelumnya($telat = 0)
    {
        $token = $this->att->tokenGithubInfo();

        $response = Http::withHeaders([
            'Accept' => 'application/vnd.github.v3+json',
            'Authorization' => "token {$token}"
        ])->get('https://api.github.com/repos/OpenSID/rilis-premium/releases')->throw()->json();
        $versi_opensid_tags = $response[1 + $telat];
        $versi_sebelumnya = $versi_opensid_tags['tag_name'];

        return $versi_sebelumnya;
    }

    private function updatePelanggan($versi_sebelumnya, $kode_desa)
    {
        if ($versi_sebelumnya) {
            $pelanggan = Pelanggan::where('kode_desa', $kode_desa)->first();
            $pelanggan->versi_opensid = $versi_sebelumnya;
            $pelanggan->save();
        }
    }

    private function hapusFolderDesa($kode_desa)
    {
        $multisite_desa = $this->att->getMultisiteFolder() . $kode_desa . DIRECTORY_SEPARATOR . 'desa';

        if (File::isDirectory($multisite_desa)) {
            // hapus folder desa existing
            $copy_desa = 'sudo rm -R ' . $multisite_desa;
            exec($copy_desa);
        }
    }

    private function restoreFolderDesa($path_public, $kode_desa)
    {
        $folder_desa = $path_public . 'folder-desa' . DIRECTORY_SEPARATOR . 'desa_' . $kode_desa;
        $multisite_desa = $this->att->getMultisiteFolder() . $kode_desa . DIRECTORY_SEPARATOR . 'desa';

        if (file_exists($folder_desa)) {
            $copy_desa = 'sudo cp -R ' . $folder_desa . ' ' . $multisite_desa;
            exec($copy_desa);
        }
    }

    public function unlinkFolder($versi_sebelumnya, $kode_desa)
    {
        $folderOpensid = $this->att->getMultisiteFolder() . $kode_desa;

        //symlink opensid
        if ($versi_sebelumnya) {
            $this->comm->unlinkCommand($folderOpensid . DIRECTORY_SEPARATOR . 'app');
            $this->comm->unlinkCommand($folderOpensid . DIRECTORY_SEPARATOR . 'donjo-app');
            $this->comm->unlinkCommand($folderOpensid . DIRECTORY_SEPARATOR . 'assets');
            $this->comm->unlinkCommand($folderOpensid . DIRECTORY_SEPARATOR . 'resources');
            $this->comm->unlinkCommand($folderOpensid . DIRECTORY_SEPARATOR . 'securimage');
            $this->comm->unlinkCommand($folderOpensid . DIRECTORY_SEPARATOR . 'template-surat');
            $this->comm->unlinkCommand($folderOpensid . DIRECTORY_SEPARATOR . 'vendor');
            $this->comm->unlinkCommand($folderOpensid . DIRECTORY_SEPARATOR . 'catatan_rilis.md');
            $this->comm->unlinkCommand($folderOpensid . DIRECTORY_SEPARATOR . 'favicon.ico');
            $this->comm->unlinkCommand($folderOpensid . DIRECTORY_SEPARATOR . 'LICENSE');
        }
    }

    private function setFolderOpensid($tags_versi_sebelumnya, $kodedesa)
    {
        if ($tags_versi_sebelumnya) {
            $this->att->setSiteFolderOpensid($this->att->getMultisiteFolder() . $kodedesa);
            $this->att->setIndexDesa($this->att->getSiteFolderOpensid() . DIRECTORY_SEPARATOR . 'index.php');

            $versi_sebelumnya = preg_replace('/[^0-9]/', '', $tags_versi_sebelumnya);
            $rev = substr($versi_sebelumnya, 5, 1);
            $versiFolder = '';
            if ($rev == "0") {
                $versiFolder = 'premium_rilis';
            } else if ($rev > 0) {
                $versiFolder = 'premium_rev0' . $rev;
            }

            $this->filesIndex->indexPhpOpensid(
                $this->att->getRootFolder() . 'master-opensid' . DIRECTORY_SEPARATOR . $versiFolder,
                $this->att->getMultisiteFolder() . $kodedesa,
                $this->att->getIndexTemplate(),
                $this->att->getIndexDesa()
            );

            // eksekusi index.php di opensid dibackgroung untuk menjalankan migrasi dan pembuatan symlink di
            $this->comm->migratePremium($this->att->getSiteFolderOpensid());
        }
    }
}
