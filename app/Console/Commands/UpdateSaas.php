<?php

namespace App\Console\Commands;

use App\Http\Controllers\Helpers\CommandController;
use App\Http\Controllers\Helpers\AttributeSiapPakaiController;
use App\Models\Aplikasi;
use App\Services\ProcessService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Artisan;
use Symfony\Component\Process\Process;

class UpdateSaas extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'siappakai:update-siappakai';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Perbarui versi Dasbor SiapPakai (dapat dilakukan setiap hari melalui cronjob)';

    private $att;
    private $command;

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
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        // Ambil informasi repository dan token GitHub
        $repo = $this->att->getRepo();
        $token = $this->att->tokenGithubInfo();
        $path_root = dirname(base_path(), 1);

        // Ambil versi terbaru dari GitHub
        $response = Http::withHeaders([
            'Accept' => 'application/vnd.github.v3+json',
            'Authorization' => "token {$token}"
        ])->get('https://api.github.com/repos/OpenSID/rilis-siappakai/releases/latest')->throw()->json();
        $version_git = preg_replace('/[^0-9]/', '', $response['tag_name']);

        // Cek versi yang ada di server
        $content_versi = 0;
        if (File::exists($path_root . '/dasbor-siappakai/artisan')) {
            $content_versi = siappakai_version();
        }
        $version_server = preg_replace('/[^0-9]/', '', $content_versi);
        $dasbor_siappakai = $path_root . DIRECTORY_SEPARATOR . 'dasbor-siappakai';

        // Reset dan commit sebelum update
        $this->command->resetHard($dasbor_siappakai);
        $this->command->commitCommand('sebelum update siappakai', $dasbor_siappakai);

        // Jika versi Git lebih baru, lakukan update
        if (substr($version_git, 0, 6) > substr($version_server, 0, 6)) {
            $this->updateSiappakai($token, $repo, $dasbor_siappakai);
        }

        // Jika aplikasi mendukung multi PHP, update .htaccess
        if (Aplikasi::pengaturan_aplikasi()['multiphp'] == 1) {
            $this->updateHtaccess($dasbor_siappakai);
        }

        ProcessService::aturKepemilikanDirektori(config('siappakai.root.folder'));
    }

    private function updateSiappakai($token, $repo, $dasbor_siappakai)
    {
        $url = 'https://oauth2:' . $token . '@github.com/OpenSID/' . $repo . '.git';
        ProcessService::runProcess(['sudo', 'git', 'pull', $url, 'main'], $dasbor_siappakai, 'Git clone repo' . $repo);

        // Ubah kepemilikan file
        $this->command->chownCommand($dasbor_siappakai);

        // Update composer dan migrasi database
        $this->command->composerUpdate($dasbor_siappakai);
        $this->command->migrateForce($dasbor_siappakai);

        // Bersihkan konfigurasi
        Artisan::call('siappakai:config-clear', [], $this->getOutput());

        // Update indeks OpenSID, API, PBB
        Artisan::call('siappakai:update-index', [], $this->getOutput());

        // Install master tema pro
        Artisan::call('siappakai:install-master-tema', [], $this->getOutput());

        // Commit setelah update
        $this->command->commitCommand('update siappakai', $dasbor_siappakai);

        // Dump autoload composer
        $this->command->composerDumpAutoload($dasbor_siappakai);

        // Ubah kepemilikan file lagi
        $this->command->chownCommand($dasbor_siappakai);
    }

    private function updateHtaccess($dasbor_siappakai)
    {
        $htaccess_siappakai = $this->att->getTemplateFolderSiapPakai() . DIRECTORY_SEPARATOR . '.htaccess';
        $htaccess_public = $dasbor_siappakai . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . '.htaccess';
        if (File::exists($htaccess_siappakai)) {
            $this->command->removeFile($htaccess_public);
            $this->command->copyFile($htaccess_siappakai, $htaccess_public);
        }
    }
}
