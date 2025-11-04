<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\ProcessService;
use Symfony\Component\Process\Process;
use App\Http\Controllers\Helpers\AttributeSiapPakaiController;

class InstallTemaBawaan extends Command
{
    /**
     * Nama dan signature dari console command.
     *
     * @var string
     */
    protected $signature = 'siappakai:install-tema-bawaan {--tema=} {--kode_desa=}';

    /**
     * Deskripsi dari console command.
     *
     * @var string
     */
    protected $description = 'Instal Tema Bawaan di desa tertentu berdasarkan kode desa';

    private $att;
    private $siteFolder;
    private $temaProFolder;

    /**
     * Buat instance baru dari command.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->att = new AttributeSiapPakaiController();
        $this->setTemaProFolder($this->att->getRootFolder() . 'master-tema-pro');
    }

    /**
     * Eksekusi console command.
     *
     * @return int
     */
    public function handle()
    {
        $tema = $this->option('tema');
        $kodeDesa = str_replace('.', '', $this->option('kode_desa'));

        $this->setSiteFolder($this->att->getMultisiteFolder() . $kodeDesa);

        if (!file_exists($this->getSiteFolder())) {
            return $this->error("Peringatan: kode desa tidak ditemukan");
        }

        $this->pasangTemaPro($tema);
        ProcessService::runProcess(['git', 'add', '.'], $this->att->getRootFolder() . 'dasbor-siappakai',  'Menambahkan perubahan ke git');
        ProcessService::runProcess(['git', 'commit', '-m', 'install tema desa ' . $kodeDesa], $this->att->getRootFolder() . 'dasbor-siappakai',  'Menambahkan perubahan ke git');
    }

    /**
     * Pasang tema pro ke folder desa.
     *
     * @param string $tema
     * @return void
     */
    private function pasangTemaPro(string $tema): void
    {
        $temaPro = $this->getTemaProFolder() . DIRECTORY_SEPARATOR . $tema;
        $temaDesa = $this->getSiteFolder() . DIRECTORY_SEPARATOR . 'desa' . DIRECTORY_SEPARATOR . 'themes' . DIRECTORY_SEPARATOR . $tema;

        if (!file_exists($temaPro)) {
            $this->error("Peringatan: tema pro tidak tersedia");
            return;
        }

        if (file_exists($temaDesa)) {
            ProcessService::runProcess(['sudo', 'unlink', $temaDesa], $this->att->getRootFolder() . 'dasbor-siappakai', 'Menghapus link tema desa');
        }

        ProcessService::runProcess(['sudo', 'ln', '-s', $temaPro, $temaDesa], $this->att->getRootFolder() . 'dasbor-siappakai', 'Membuat link tema desa');
    }

    /**
     * Dapatkan nilai dari siteFolder.
     *
     * @return string
     */
    public function getSiteFolder()
    {
        return $this->siteFolder;
    }

    /**
     * Set nilai dari siteFolder.
     *
     * @param string $siteFolder
     * @return self
     */
    public function setSiteFolder(string $siteFolder): self
    {
        $this->siteFolder = $siteFolder;
        return $this;
    }

    /**
     * Dapatkan nilai dari temaProFolder.
     *
     * @return string
     */
    public function getTemaProFolder()
    {
        return $this->temaProFolder;
    }

    /**
     * Set nilai dari temaProFolder.
     *
     * @param string $temaProFolder
     * @return self
     */
    public function setTemaProFolder(string $temaProFolder): self
    {
        $this->temaProFolder = $temaProFolder;
        return $this;
    }
}
