<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\ProcessService;
use App\Http\Controllers\Helpers\TemaController;
use App\Http\Controllers\Helpers\CommandController;
use App\Http\Controllers\Helpers\AttributeSiapPakaiController;

class UpdateTema extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'siappakai:update-tema';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Perbarui Tema Pro (dapat dilakukan setiap hari melalui cronjob)';

    private $att;
    private $comm;
    private $temas;

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
        $this->temas = new TemaController;
    }

    /**
     * Execute the console command.
     *
     * @return int
     */

    public function handle()
    {
        $tema_pro = $this->temas->JenisTemaPro();
        $tema_gratis = $this->temas->JenisTemaGratis();

        foreach ($tema_pro as $item) {
            $tema = $item['tema'];
            $repo = $item['repo'];
            $branch = $item['branch'];
            $folder_tema = $this->att->getTemaProFolder() . DIRECTORY_SEPARATOR . $item['tema'];

            $this->updateTema($tema, $repo, $branch, $folder_tema);
        }

        foreach ($tema_gratis as $item) {
            $tema = $item['tema'];
            $repo = $item['repo'];
            $branch = $item['branch'];
            $folder_tema = $this->att->getTemaGratisFolder() . DIRECTORY_SEPARATOR . $item['tema'];

            $this->updateTema($tema, $repo, $branch, $folder_tema);
        }
        ProcessService::aturKepemilikanDirektori(config('siappakai.root.folder'));
    }

    private function updateTema($tema, $repo, $branch, $folder_tema)
    {
        $this->att->tokenGithubInfo();

        if (!file_exists($folder_tema)) {
            return die('Peringatan: tema ' . $tema . ' tidak ditemukan.');
        }

        if (file_exists($folder_tema)) {
            $this->comm->gitPullFetch($folder_tema, $repo, $branch);
            $this->comm->chownCommand($folder_tema);
        }
    }
}
