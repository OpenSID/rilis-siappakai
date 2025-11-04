<?php

namespace App\Console\Commands;

use App\Models\Aplikasi;
use Illuminate\Console\Command;
use App\Services\ProcessService;
use Symfony\Component\Process\Process;
use App\Http\Controllers\Helpers\TemaController;
use App\Http\Controllers\Helpers\CommandController;
use App\Http\Controllers\Helpers\AttributeSiapPakaiController;

class InstallMasterTema extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'siappakai:install-master-tema';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Instal Master Tema Pro';

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
        $this->att->tokenGithubInfo();

        if (env('OPENKAB') == 'true') {
            $this->installTemaBawaan();
        }

        $temas = new TemaController;
        $temas_pro = $temas->JenisTemaPro();
        $temas_gratis = $temas->JenisTemaGratis();

        // Tema Pro
        foreach ($temas_pro as $item) {
            $username = $item['username'];
            $tema = $item['tema'];
            $repo = $item['repo'];

            if ($this->att->getTemaProFolder()) {
                $this->installTema($username, $tema, $repo, $this->att->getTemaProFolder());
            }
        }

         ProcessService::aturKepemilikanDirektori($this->att->getTemaProFolder());
        
        // Tema Gratis
        foreach ($temas_gratis as $item) {
            $username = $item['username'];
            $tema = $item['tema'];
            $repo = $item['repo'];

            if ($this->att->getTemaGratisFolder()) {
                $this->installTema($username, $tema, $repo, $this->att->getTemaGratisFolder());
            }
        }
        $this->command->chownCommand($this->att->getTemaGratisFolder());
    }

    private function installTemaBawaan()
    {
        $web_theme = Aplikasi::pengaturan_aplikasi()['tema_bawaan'];

        if ($web_theme == '' || $web_theme == 'esensi' || $web_theme == 'natra') {
            $web_theme = false;
        }

        $username = 'OpenSID';
        $tema = $web_theme;
        $repo = 'tema-' . $web_theme;
        $dir_from = $this->att->getTemaProFolder() . DIRECTORY_SEPARATOR . $web_theme;
        $dir_to = $this->att->getRootFolder() . 'master-opensid' . DIRECTORY_SEPARATOR . 'premium' . DIRECTORY_SEPARATOR . 'desa' . DIRECTORY_SEPARATOR . 'themes' . DIRECTORY_SEPARATOR . $web_theme;
        $premium = $this->att->getRootFolder() . 'master-opensid' . DIRECTORY_SEPARATOR . 'premium';

        if ($this->att->getTemaProFolder() && $web_theme != false) {
            $this->installTema($username, $tema, $repo, $this->att->getTemaProFolder());
            $this->command->chownCommand($dir_from);
        }

        if (file_exists($dir_from) && !file_exists($dir_to) && file_exists($premium)) { // tambahkan pengecekan untuk folder premium
            // To Do
            // butuh perbaikan kode
            // - banyak kode yang mirip seperti fungi install tema di jalankan 2 kali di line 101 dan 67
            // - alur programnya harus di perbaiki karena fungsi ini harusnya berjalan setelah update atau install master opensid selesai
            
            $this->command->symlinkDirectory($dir_from, $dir_to);
            $this->command->chownCommand($dir_to);
        }
    }

    private function installTema($username, $tema, $repo, $getFolder)
    {
        $folder_tema = $getFolder . DIRECTORY_SEPARATOR . $tema;

        if (!file_exists($folder_tema)) {
            $url = 'https://oauth2:' . $this->att->getTokenGithub() . '@github.com/' . $username . '/' . $repo . '.git';
            $pull = new Process(['sudo', 'git', 'clone', $url, $tema], $getFolder);
            $pull->setTimeout(null);
            $pull->run();

            $safe_directory = 'sudo git config --global --add safe.directory ' . $folder_tema;
            exec($safe_directory);
        }
    }
}
