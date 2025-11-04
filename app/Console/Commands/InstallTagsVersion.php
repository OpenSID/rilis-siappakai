<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\ProcessService;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use App\Http\Controllers\Helpers\CommandController;
use App\Http\Controllers\Helpers\AttributeSiapPakaiController;

class InstallTagsVersion extends Command
{
    /**
     * Nama dan tanda tangan dari perintah konsol.
     *
     * @var string
     */
    protected $signature = 'siappakai:install-tags-version';

    /**
     * Deskripsi perintah konsol.
     *
     * @var string
     */
    protected $description = 'Instal OpenSID Premium, OpenSID API, Aplikasi PBB versi 5 bulan sebelumnya';

    private $att;
    private $comm;

    /**
     * Buat instance perintah baru.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->att = new AttributeSiapPakaiController();
        $this->comm = new CommandController();
    }

    /**
     * Jalankan perintah konsol.
     *
     * @return int
     */
    public function handle()
    {
        $path_root = env('ROOT_OPENSID');
        $token = $this->att->tokenGithubInfo();

        foreach ($this->Repo() as $item) {
            $this->installTagsAplikasi(
                $item['master_folder'],
                $item['app_folder'],
                $item['repo'],
                $token,
                $path_root
            );
        }

        $this->installTagsOpensid(
            $token,
            $path_root
        );
    }

    /**
     * Instal tag aplikasi.
     *
     * @param string $master_folder
     * @param string $app_folder
     * @param string $repo
     * @param string $token
     * @param string $path_root
     * @return void
     */
    private function installTagsAplikasi($master_folder, $app_folder, $repo, $token, $path_root)
    {
        $response = Http::withHeaders([
            'Accept' => 'application/vnd.github.v3+json',
            'Authorization' => "token {$token}"
        ])->get("https://api.github.com/repos/OpenSID/{$repo}/releases/latest")->throw()->json();

        $version_git = preg_replace('/[^0-9]/', '', $response['tag_name']);
        $content_versi = 0;
        $dirMaster = $path_root . $master_folder . DIRECTORY_SEPARATOR;
        $dirArtisan = $dirMaster . $app_folder . DIRECTORY_SEPARATOR . 'artisan';

        if (File::exists($dirArtisan)) {
            $artisan = ['php', $dirArtisan, 'app:version'];
            $output =  ProcessService::runProcess($artisan, base_path());

            $content_versi = $output->getOutput();
        }

        $version_server = preg_replace('/[^0-9]/', '', $content_versi);

        if ($version_server == 0) {
            echo "Error : Tidak ada versi server\n";
            echo "Versi Server : $content_versi\n";
            die();
        }

        // Instal API dan PBB
        if (substr($version_git, 0, 4) == substr($version_server, 0, 4)) {
            for ($i = 1; $i < 6; $i++) {
                $tags = $this->monthVersion($version_server, $content_versi, $i);
                $url = "https://oauth2:{$token}@github.com/OpenSID/{$repo}.git";
                $this->cloneRepo($tags, $url, "{$app_folder}_{$i}", $dirMaster);
            }

            $this->comm->chownCommand($dirMaster);
        }
    }

    /**
     * Instal tag aplikasi Opensid.
     *
     * @param string $token
     * @param string $path_root
     * @return void
     */
    private function installTagsOpensid($token, $path_root)
    {
        $master_opensid = $path_root . 'master-opensid' . DIRECTORY_SEPARATOR;
        $folderPremium = $master_opensid . 'premium';

        if (File::exists($folderPremium)) {
            $tags_server = 'cd ' . $folderPremium .  ' && sudo git describe --tags';

            $content_versi = exec($tags_server);
        } else {
            echo "folder premium tidak ada\n";
            die();
        }

        $version_server = preg_replace('/[^0-9]/', '', $content_versi);

        if ($version_server == 0) {
            echo "Error : Tidak ada versi server\n";
            echo "Versi Server : $content_versi\n";
            die();
        }

        for ($i = 1; $i < 6; $i++) {
            $tags = $this->monthVersion($version_server, $content_versi, $i);
            $url = "https://oauth2:{$token}@github.com/OpenSID/rilis-premium.git";
            $this->cloneRepo($tags, $url, "premium_{$i}", $master_opensid);
        }

        $this->comm->chownCommand($folderPremium);
    }

    private function cloneRepo($tags, $url, $folder, $dirMaster)
    {
        $pull = ['sudo', 'git', 'clone', '-b', $tags, $url, $folder];
        ProcessService::runProcess($pull, $dirMaster);

        $config_premium = "sudo git config --global --add safe.directory {$dirMaster}{$folder}";
        exec($config_premium);
    }

    /**
     * Tambahkan Repo berbasis Laravel disini.
     *
     * @return array
     */
    private function Repo()
    {
        return [
            ['master_folder' => 'master-api', 'app_folder' => 'opensid-api', 'repo' => 'rilis-opensid-api'],
            ['master_folder' => 'master-pbb', 'app_folder' => 'pbb_desa', 'repo' => 'rilis-pbb'],
        ];
    }

    /**
     * Dapatkan versi bulan sebelumnya.
     *
     * @param string $version_server
     * @param string $content_versi
     * @param int $version
     * @return string
     */
    private function monthVersion($version_server, $content_versi, $version)
    {
        $month = substr($version_server, 2, 2);
        if ($month == 01) {
            $month = 12;
        } else if ($month <= 10) {
            $month = '0' . ($month - $version);
        } else {
            $month = $month - $version;
        }
        return substr($content_versi, 0, 3) . $month . '.0.0';
    }
}
