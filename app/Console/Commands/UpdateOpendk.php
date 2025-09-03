<?php

namespace App\Console\Commands;

use App\Models\Aplikasi;
use App\Models\Pelanggan;
use Illuminate\Console\Command;
use App\Services\ProcessService;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Symfony\Component\Process\Process;
use App\Http\Controllers\Helpers\IndexController;
use App\Http\Controllers\Helpers\CommandController;
use App\Http\Controllers\Helpers\AttributeSiapPakaiController;
use App\Models\Opendk;

/**
 * Kelas UpdateOpendk
 *
 * Perintah Artisan untuk memperbarui sumber OpenDK dan menyiapkan symlink
 * multisite. Kelas ini menggunakan helper controller dan service untuk
 * melakukan operasi yang berhubungan dengan sistem file, git, composer dan
 * migrasi basis data.
 *
 * Tanggung jawab utama:
 *  - Mengambil rilis terbaru OpenDK dari GitHub
 *  - Membandingkan dengan versi yang terpasang dan merotasi folder rilis
 *  - Menjalankan composer, migrasi, dan membuat storage link
 *  - Memperbarui symlink untuk setiap situs multisite
 *
 * @package App\Console\Commands
 */
class UpdateOpendk extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'siappakai:update-opendk';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Perbarui versi OpenDK';

    /**
     * Instance helper attribute.
     *
     * @var \App\Http\Controllers\Helpers\AttributeSiapPakaiController
     */
    private $att;

    /**
     * Instance helper perintah untuk mengeksekusi perintah tingkat sistem.
     *
     * @var \App\Http\Controllers\Helpers\CommandController
     */
    private $command;

    /**
     * Helper file index yang digunakan untuk memperbarui index.php pada
     * instalasi multisite.
     *
     * @var \App\Http\Controllers\Helpers\IndexController
     */
    private $filesIndex;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        $this->att = new AttributeSiapPakaiController();
        $this->command = new CommandController();
        $this->filesIndex = new IndexController();

    }

    /**
     * Menjalankan perintah console.
     *
     * Alur utama:
     *  - keluar lebih awal jika mode BETA aktif
     *  - mengambil rilis GitHub dan membandingkan versi
     *  - melakukan rotasi aman pada folder rilis lokal
     *  - menjalankan composer, migrate dan storage:link
     *  - memperbarui symlink untuk multisite
     *
     * @return int|null Nilai kembalian tidak digunakan pemanggil; `0` atau null
     *                  menandakan sukses. Nilai non-zero menandakan kesalahan.
     */
    public function handle()
    {
        if (env('BETA_OPENDK') == 'true') {
            return die("Informasi: server menggunakan versi beta sehingga tidak di update");
        }

        $token = $this->att->tokenGithubInfo();
        $path_root = config('siappakai.root.folder');

        $response = Http::withHeaders([
            'Accept' => 'application/vnd.github.v3+json',
            'Authorization' => "token {$token}"
        ])->get('https://api.github.com/repos/OpenSID/OpenDK/releases/latest')->throw()->json();
        $version_git = preg_replace('/[^0-9]/', '', $response['tag_name']);
        $content_versi = 0;

        $path_opendk = $path_root . '/master-opendk/opendk';
        if (File::exists($path_opendk)) {
            ProcessService::gitSafeDirectori($path_opendk);
            $artisan = new Process(['git', 'describe', '--tags', '--exact-match'], $path_opendk);
            $artisan->run();
            $content_versi = $artisan->getOutput();
        }

        $version_server = preg_replace('/[^0-9]/', '', $content_versi);

        if(!$version_server){
            return $this->command->notifMessage('silahkan cek path  master-opendk/opendk sudah ada');
        }

        if (substr($version_git, 0, 4) > substr($version_server, 0, 4)) { // jika versi git lebih besar dibandingkan versi server. lakukan update
            $opendk = $path_root . DIRECTORY_SEPARATOR . 'master-opendk' . DIRECTORY_SEPARATOR;

            $rev = substr($version_git, 5, 1);
            $tags = 'v' . substr($version_git, 0, 4) . '.' . substr($version_git, 4, 1) . '.' . $rev;

            File::deleteDirectory($opendk . 'opendk_5'); // hapus folder opendk_5
            for ($i = 4; $i > 0; $i--) {
                if (File::isDirectory($opendk . 'opendk_' . $i)) {
                    rename($opendk . 'opendk_' . $i, $opendk . 'opendk_' . ($i + 1));
                }
            }
            if (File::isDirectory($opendk . 'opendk')) {
                rename($opendk . 'opendk', $opendk . 'opendk_1'); //rename opendk > opendk_1
            }

            //prosess clone rilis terbaru
            if (file_exists(rtrim($opendk, "/"))) {
                $url = 'https://oauth2:' . $token . '@github.com/OpenSID/OpenDK.git';
                $pull = new Process(['sudo', 'git', 'clone', '--branch', $tags, $url, 'opendk'], rtrim($opendk, "/"));
                $pull->setTimeout(null);
                $pull->run();

                ProcessService::gitSafeDirectori($path_opendk);
            }

            File::copy($opendk . 'opendk_1' . DIRECTORY_SEPARATOR . '.env', $opendk . 'opendk' . DIRECTORY_SEPARATOR . '.env'); // copy .env

            // hapus dan salin file htaccess
            $multiphp = Aplikasi::pengaturan_aplikasi()['multiphp'];
            $htaccess = $opendk . DIRECTORY_SEPARATOR . 'opendk' . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . '.htaccess';
            $htaccess_from = $path_root . DIRECTORY_SEPARATOR . 'master-template' . DIRECTORY_SEPARATOR . 'template-opendk' . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . '.htaccess';

            // jika menggunakan multiphp hapus file htaccess dan buat symlink
            $this->command->setHtaccessMaster($multiphp, $htaccess_from, $htaccess);

            // perintah dari helper CommandController
            $this->command->composerInstall($opendk . DIRECTORY_SEPARATOR . 'opendk');
            $this->command->migrateForce($opendk . DIRECTORY_SEPARATOR . 'opendk');
            $this->command->storageLink($opendk . DIRECTORY_SEPARATOR . 'opendk');

            // jika tidak berhasil menjalankan composer install / update maka salin vendor dari versi sebelumnya
            if(!file_exists($opendk . 'opendk' . DIRECTORY_SEPARATOR . 'vendor') || file_exists($opendk . 'opendk_1' . DIRECTORY_SEPARATOR . 'vendor')){
                $this->command->copyDirectory($opendk . 'opendk_1' . DIRECTORY_SEPARATOR . 'vendor', $opendk . 'opendk' . DIRECTORY_SEPARATOR . 'vendor');
            }

            $this->command->chownCommandPanel($opendk);

            // Update Symlink
            $this->unlinkFolder();
        }
        ProcessService::aturKepemilikanDirektori($path_opendk);
    }

    /**
     * Memperbarui symlink dan melakukan aksi pasca-pembaruan per situs.
     *
     * Metode ini melakukan iterasi pada rekaman `Opendk` yang dikonfigurasi dan:
     *  - menentukan nama folder multisite
     *  - menghapus (unlink) symlink lama
     *  - menulis index.php baru melalui helper index jika langganan aktif
     *  - menjalankan composer update dan migrasi untuk setiap situs yang
     *    memiliki file `artisan`
     *
     * @return void
     */
    public function unlinkFolder()
    {
        $opendks = Opendk::get();

        foreach ($opendks as $item) {
            $folderOpendk = $this->att->getMultisiteFolder() . str_replace('.', '', $item->kode_provinsi) .str_replace('.', '', $item->kode_kabupaten) . str_replace('.', '', $item->kode_kecamatan);
            $this->att->setIndexOpendk($folderOpendk . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'index.php');

            //unlink opendk
            $this->command->unlinkCommandAppLaravel($folderOpendk);

            //unlink opendk/public
            $this->command->unlinkCommandAppLaravelPublic($folderOpendk);

            // ubah symlink di file index
            if ($item->langganan_opensid && file_exists($this->att->getIndexOpendk())) {
                $this->filesIndex->indexPhpOpendk(
                    'opendk',  //opendkFolder
                    $this->att->getRootFolder() . 'master-opendk' . DIRECTORY_SEPARATOR,           //opendkFolderFrom
                    $folderOpendk,                                                              //opendkFolderTo
                    $this->att->getIndexTemplateOpendk(),
                    $this->att->getIndexOpendk()
                );
            }

            if (File::exists($folderOpendk . DIRECTORY_SEPARATOR . 'artisan')) {
                $this->command->composerUpdate($folderOpendk);                                //composer update
                $this->command->indexCommand($folderOpendk . DIRECTORY_SEPARATOR . 'public'); // index.php
                $this->command->migrateForce($folderOpendk);                                  // migrate
            }
        }
    }
}
