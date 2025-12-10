<?php

namespace App\Console\Commands;

use App\Http\Controllers\Helpers\AttributeSiapPakaiController;
use App\Http\Controllers\Helpers\CommandController;
use App\Http\Controllers\Helpers\IndexController;
use App\Models\Aplikasi;
use App\Services\ProcessService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Symfony\Component\Process\Process;
use App\Models\Pelanggan;

class UpdateApi extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'siappakai:update-api';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Perbarui versi Api (dapat dilakukan setiap hari melalui cronjob)';

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
        if (env('BETA_API') == 'true') {
            return die("Informasi: server menggunakan versi beta sehingga tidak di update");
        }

        $token = $this->att->tokenGithubInfo();
        $path_root = dirname(base_path(), 1);
        $response = Http::withHeaders([
            'Accept' => 'application/vnd.github.v3+json',
            'Authorization' => "token {$token}"
        ])->get('https://api.github.com/repos/OpenSID/rilis-opensid-api/releases/latest')->throw()->json();
        $version_git = preg_replace('/[^0-9]/', '', $response['tag_name']);
        $content_versi = 0;

        if (File::exists($path_root . '/master-api/opensid-api/artisan')) {
            $artisan = new Process(['php', $path_root . '/master-api/opensid-api/artisan', 'app:version']);
            $artisan->run();
            $content_versi = $artisan->getOutput();
        }

        $version_server = preg_replace('/[^0-9]/', '', $content_versi);

        if (!$version_server) {
            return $this->command->notifMessage('silakan cek manual menggunakan perintah php artisan app:version di master-api/opensid-api');
        }

        if (substr($version_git, 0, 6) > substr($version_server, 0, 6)) { // jika versi git lebih besar dibandingkan versi server. lakukan update
            $api = $path_root . DIRECTORY_SEPARATOR . 'master-api' . DIRECTORY_SEPARATOR;

            $rev = substr($version_git, 5, 1);
            $tags = 'v' . substr($version_git, 0, 4) . '.' . substr($version_git, 4, 1) . '.' . $rev;

            File::deleteDirectory($api . 'opensid-api_5'); // hapus folder api_5
            for ($i = 6; $i > 0; $i--) {
                if (File::isDirectory($api . 'opensid-api_' . $i)) {
                    rename($api . 'opensid-api_' . $i, $api . 'opensid-api_' . ($i + 1));
                }
            }

            if (File::isDirectory($api . 'opensid-api')) {
                rename($api . 'opensid-api', $api . 'opensid-api_1'); //rename ke opensid-api_1
            }

            //prosess clone rilis terbaru
            if (file_exists(rtrim($api, "/"))) {
                $url = 'https://oauth2:' . $token . '@github.com/OpenSID/rilis-opensid-api.git';
                $pull = new Process(['sudo', 'git', 'clone', '--branch', $tags, $url, 'opensid-api'], rtrim($api, "/"));
                $pull->setTimeout(null);
                $pull->run();

                $config_premium = 'sudo git config --global --add safe.directory ' . $api . 'opensid-api';
                exec($config_premium);
            }

            File::copy($api . 'opensid-api_1' . DIRECTORY_SEPARATOR . '.env', $api . 'opensid-api' . DIRECTORY_SEPARATOR . '.env'); // copy .env

            $multiphp = Aplikasi::pengaturan_aplikasi()['multiphp'];
            $htaccess = $api . DIRECTORY_SEPARATOR . 'opensid-api' . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . '.htaccess';
            $htaccess_from = $path_root . DIRECTORY_SEPARATOR . 'master-template' . DIRECTORY_SEPARATOR . 'template-api' . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . '.htaccess';

            // jika menggunakan multiphp hapus file htaccess dan buat symlink
            $this->command->setHtaccessMaster($multiphp, $htaccess_from, $htaccess);

            // perintah dari helper CommandController
            $this->command->composerUpdate($api . 'opensid-api');
            $this->command->keyGenerateCommand($api . 'opensid-api');
            $this->command->keyJwtSecretCommand($api . 'opensid-api');
            $this->command->migrateForce($api . 'opensid-api');
            $this->command->storageLink($api . 'opensid-api');

            // jika tidak berhasil menjalankan composer install / update maka salin vendor dari versi sebelumnya
            if (!file_exists($api . 'opensid-api' . DIRECTORY_SEPARATOR . 'vendor') || file_exists($api . 'opensid-api_1' . DIRECTORY_SEPARATOR . 'vendor')) {
                $this->command->copyDirectory($api . 'opensid-api_1' . DIRECTORY_SEPARATOR . 'vendor', $api . 'opensid-api' . DIRECTORY_SEPARATOR . 'vendor');
            }

            $this->command->chownCommandPanel($api);

            // Update Symlink
            $this->unlinkFolder();
        }
        ProcessService::aturKepemilikanDirektori(config('siappakai.root.folder'));
    }

    public function unlinkFolder()
    {
        $pelanggan = Pelanggan::get();

        foreach ($pelanggan as $item) {
            $folderApi = $this->att->getMultisiteFolder() . str_replace('.', '', $item->kode_desa);
            $folderApiApp = $folderApi . DIRECTORY_SEPARATOR . 'api-app';
            $this->att->setIndexApi($folderApiApp . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'index.php');

            // unlink api-app
            $this->command->unlinkCommandAppLaravel($folderApiApp);

            // ubah symlink di file index
            if ($item->langganan_opensid && file_exists($this->att->getIndexApi())) {
                $this->filesIndex->indexPhpApi(
                    $this->filesIndex->langganan_opensid($item->langganan_opensid, 'opensid-api'),  //apiFolderFrom,
                    $this->att->getRootFolder() . 'master-api' . DIRECTORY_SEPARATOR,                   //apiFolderFrom,
                    $folderApiApp,                                                                      //apiFolderTo,
                    $this->att->getIndexTemplateApi(),
                    $this->att->getIndexApi()
                );
            }

            if (File::exists($folderApiApp . DIRECTORY_SEPARATOR . 'artisan')) {
                $this->command->composerUpdate($folderApiApp);                                //composer update
                $this->command->composerDumpAutoload($folderApiApp);                          //composer dump-autoload
                $this->command->optimize($folderApiApp);                                      //optimize-clear
                $this->command->indexCommand($folderApiApp . DIRECTORY_SEPARATOR . 'public'); // index.php                                 // migrate
            }
        }
    }
}
