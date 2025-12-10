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

class UpdatePbb extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'siappakai:update-pbb';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Perbarui versi PBB (dapat dilakukan setiap hari melalui cronjob)';

    private $att;
    private $command;
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
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        if (env('BETA_PBB') == 'true') {
            return die("Informasi: server menggunakan versi beta sehingga tidak di update");
        }

        $token = $this->att->tokenGithubInfo();
        $path_root = dirname(base_path(), 1);
        $response = Http::withHeaders([
            'Accept' => 'application/vnd.github.v3+json',
            'Authorization' => "token {$token}"
        ])->get('https://api.github.com/repos/OpenSID/rilis-pbb/releases/latest')->throw()->json();
        $version_git = preg_replace('/[^0-9]/', '', $response['tag_name']);
        $content_versi = 0;
        if (File::exists($path_root . '/master-pbb/pbb_desa/artisan')) {
            $artisan = new Process(['php', $path_root . '/master-pbb/pbb_desa/artisan', 'app:version']);
            $artisan->run();
            $content_versi = $artisan->getOutput();
        }

        $version_server = preg_replace('/[^0-9]/', '', $content_versi);

        if(!$version_server){
            return $this->command->notifMessage('silakan cek manual menggunakan perintah php artisan app:version di master-pbb/pbb_desa');
        }

        if (substr($version_git, 0, 4) > substr($version_server, 0, 4)) { // jika versi git lebih besar dibandingkan versi server. lakukan update
            $pbb = $path_root . DIRECTORY_SEPARATOR . 'master-pbb' . DIRECTORY_SEPARATOR;

            $rev = substr($version_git, 5, 1);
            $tags = 'v' . substr($version_git, 0, 4) . '.' . substr($version_git, 4, 1) . '.' . $rev;

            File::deleteDirectory($pbb . 'pbb_desa_5'); // hapus folder pbb_desa_5
            for ($i = 4; $i > 0; $i--) {
                if (File::isDirectory($pbb . 'pbb_desa_' . $i)) {
                    rename($pbb . 'pbb_desa_' . $i, $pbb . 'pbb_desa_' . ($i + 1));
                }
            }
            if (File::isDirectory($pbb . 'pbb_desa')) {
                rename($pbb . 'pbb_desa', $pbb . 'pbb_desa_1'); //rename pbb_desa > pbb_desa_1
            }

            //prosess clone rilis terbaru
            if (file_exists(rtrim($pbb, "/"))) {
                $url = 'https://oauth2:' . $token . '@github.com/OpenSID/rilis-pbb.git';
                $pull = new Process(['sudo', 'git', 'clone', '--branch', $tags, $url, 'pbb_desa'], rtrim($pbb, "/"));
                $pull->setTimeout(null);
                $pull->run();

                $config_premium = 'sudo git config --global --add safe.directory ' . $pbb . 'pbb_desa';
                exec($config_premium);
            }

            File::copy($pbb . 'pbb_desa_1' . DIRECTORY_SEPARATOR . '.env', $pbb . 'pbb_desa' . DIRECTORY_SEPARATOR . '.env'); // copy .env

            // hapus dan salin file htaccess
            $multiphp = Aplikasi::pengaturan_aplikasi()['multiphp'];
            $htaccess = $pbb . DIRECTORY_SEPARATOR . 'pbb_desa' . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . '.htaccess';
            $htaccess_from = $path_root . DIRECTORY_SEPARATOR . 'master-template' . DIRECTORY_SEPARATOR . 'template-pbb' . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . '.htaccess';

            // jika menggunakan multiphp hapus file htaccess dan buat symlink
            $this->command->setHtaccessMaster($multiphp, $htaccess_from, $htaccess);

            // perintah dari helper CommandController
            $this->command->composerInstall($pbb . DIRECTORY_SEPARATOR . 'pbb_desa');
            $this->command->migrateForce($pbb . DIRECTORY_SEPARATOR . 'pbb_desa');
            $this->command->storageLink($pbb . DIRECTORY_SEPARATOR . 'pbb_desa');

            // jika tidak berhasil menjalankan composer install / update maka salin vendor dari versi sebelumnya
            if(!file_exists($pbb . 'pbb_desa' . DIRECTORY_SEPARATOR . 'vendor') || file_exists($pbb . 'pbb_desa_1' . DIRECTORY_SEPARATOR . 'vendor')){
                $this->command->copyDirectory($pbb . 'pbb_desa_1' . DIRECTORY_SEPARATOR . 'vendor', $pbb . 'pbb_desa' . DIRECTORY_SEPARATOR . 'vendor');
            }

            $this->command->chownCommandPanel($pbb);

            // Update Symlink
            $this->unlinkFolder();
        }
        ProcessService::aturKepemilikanDirektori(config('siappakai.root.folder'));
    }

    public function unlinkFolder()
    {
        $pelanggan = Pelanggan::get();

        foreach ($pelanggan as $item) {
            $folderPbb = $this->att->getMultisiteFolder() . str_replace('.', '', $item->kode_desa);
            $folderPbbApp = $folderPbb . DIRECTORY_SEPARATOR . 'pbb-app';
            $this->att->setIndexPbb($folderPbbApp . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'index.php');

            //unlink pbb-app
            $this->command->unlinkCommandAppLaravel($folderPbbApp);

            //unlink pbb-app/public
            $this->command->unlinkCommandAppLaravelPublic($folderPbbApp);

            // ubah symlink di file index
            if ($item->langganan_opensid && file_exists($this->att->getIndexPbb())) {
                $this->filesIndex->indexPhpPbb(
                    $this->filesIndex->langganan_opensid($item->langganan_opensid, 'pbb_desa'),  //pbbFolder
                    $this->att->getRootFolder() . 'master-pbb' . DIRECTORY_SEPARATOR,           //pbbFolderFrom
                    $folderPbbApp,                                                              //pbbFolderTo
                    $this->att->getIndexTemplatePbb(),
                    $this->att->getIndexPbb()
                );
            }

            if (File::exists($folderPbbApp . DIRECTORY_SEPARATOR . 'artisan')) {
                $this->command->composerUpdate($folderPbbApp);                                //composer update
                $this->command->composerDumpAutoload($folderPbbApp);                          //composer dump-autoload
                $this->command->optimize($folderPbbApp);                                      //optimize-clear
                $this->command->indexCommand($folderPbbApp . DIRECTORY_SEPARATOR . 'public'); // index.php
                $this->command->migrateForce($folderPbbApp);                                  // migrate
            }
        }
    }
}
