<?php

namespace App\Console\Commands;

use App\Http\Controllers\Helpers\TemaController;
use Illuminate\Console\Command;

class InstalTemaPremium extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'siappakai:install-tema-premium {--symlink=} {--unlink=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Instal Tema Pro di folder desa pada master opensid premium';

    private $rootFolder;
    private $temaProFolder;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->setRootFolder(env('ROOT_OPENSID'));
        $this->setTemaProFolder($this->getRootFolder()  . 'master-tema-pro');
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $symlink = $this->option('symlink');
        $unlink = $this->option('unlink');

        $folderMaster = $this->getRootFolder() . 'master-opensid' . DIRECTORY_SEPARATOR . 'premium';
        $folderDesa = $folderMaster . DIRECTORY_SEPARATOR . 'desa';

        $tema_pro = new TemaController;
        $jenis = $tema_pro->JenisTemaPro();

        foreach ($jenis as $item) {
            $tema = $item['tema'];

            if (file_exists($folderDesa) && $symlink == 'true') {
                $this->PasangTemaPro($tema, $folderDesa);
                var_dump("Informasi: berhasil membuat symlink tema " . $tema);
            }

            if (file_exists($folderDesa) && $unlink == 'true') {
                $this->HapusTemaPro($tema, $folderDesa);
                var_dump("Informasi: berhasil menghapus symlink tema " . $tema);
            }
        }
    }

    public function PasangTemaPro($tema, $folderDesa)
    {
        $tema_pro = $this->getTemaProFolder() . DIRECTORY_SEPARATOR . $tema;
        $tema_desa = $folderDesa . DIRECTORY_SEPARATOR . 'themes' . DIRECTORY_SEPARATOR . $tema;

        if (file_exists($tema_pro)) {
            $symlink = 'sudo ln -s ' . $tema_pro . ' ' . $tema_desa;
            exec($symlink);
        }
    }

    public function HapusTemaPro($tema, $folderDesa)
    {
        $tema_desa = $folderDesa . DIRECTORY_SEPARATOR . 'themes' . DIRECTORY_SEPARATOR . $tema;

        if (file_exists($tema_desa)) {
            $unlink = 'sudo unlink ' . $tema_desa;
            exec($unlink);
        }
    }


    /**
     * Get the value of rootFolder
     */
    public function getRootFolder()
    {
        return $this->rootFolder;
    }

    /**
     * Set the value of rootFolder
     *
     * @return  self
     */
    public function setRootFolder($rootFolder)
    {
        $this->rootFolder = $rootFolder;

        return $this;
    }

    /**
     * Get the value of temaProFolder
     */
    public function getTemaProFolder()
    {
        return $this->temaProFolder;
    }

    /**
     * Set the value of temaProFolder
     *
     * @return  self
     */
    public function setTemaProFolder($temaProFolder)
    {
        $this->temaProFolder = $temaProFolder;

        return $this;
    }
}
