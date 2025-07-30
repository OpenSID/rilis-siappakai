<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\ProcessService;
use App\Http\Controllers\Helpers\ConfigController;
use App\Http\Controllers\Helpers\AttributeSiapPakaiController;

class UpdateTemaKonfigurasi extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'siappakai:update-konfigurasi-tema {--kode_desa=} {--token_premium=} {--aktivasi_tema=} {--config_logo=} {--config_kode_kota=} {--config_fbadmin=} {--config_fbappid=} {--config_ip_address=} {--config_color=} {--config_fluid=} {--config_menu=} {--config_chats=} {--config_hide_layanan=} {--config_widget=} {--config_style=} {--config_hide_banner_layanan=} {--config_hide_banner_laporan=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Pembaruan Aktivasi dan Konfigurasi Tema Pro di desa tertentu berdasarkan kode desa';

    private $att;
    private $config_temas;
    private $files;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->files = new ConfigController();
        $this->att = new AttributeSiapPakaiController();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $kodedesa = str_replace('.', '', $this->option('kode_desa'));
        $kode_desa_default = $this->option('kode_desa');

        $this->att->setSiteFolderOpensid($this->att->getMultisiteFolder() . $kodedesa);

        if (!file_exists($this->att->getSiteFolderOpensid())) {
            return die("Peringatan: kode desa tidak ditemukan");
        }

        $this->config_temas = [
            'aktivasi_tema' => $this->option('aktivasi_tema'),
            'logo' => $this->option('config_logo'),
            'kode_kota' => $this->option('config_kode_kota'),
            'fbadmin' => $this->option('config_fbadmin'),
            'fbappid' => $this->option('config_fbappid'),
            'ip_address' => $this->option('config_ip_address'),
            'color' => $this->option('config_color'),
            'fluid' => $this->option('config_fluid'),
            'menu' => $this->option('config_menu'),
            'chats' => $this->option('config_chats'),
            'widget' => $this->option('config_widget'),
            'style' => $this->option('config_style'),
            'hide_layanan' => $this->option('config_hide_layanan'),
            'hide_banner_layanan' => $this->option('config_hide_banner_layanan'),
            'hide_banner_laporan' => $this->option('config_hide_banner_laporan'),
        ];

        $email = $this->files->konfigurasi($kode_desa_default);

        if ($this->att->getSiteFolderOpensid()) {
            $this->files->configDesa(
                $kodedesa,
                $this->option('token_premium'),
                $this->config_temas,
                $email['smtp_protocol'],
                $email['smtp_host'],
                $email['smtp_user'],
                $email['smtp_pass'],
                $email['smtp_port'],
                $this->att->getServerLayanan(),
                $this->att->getConfigSiteFolder(),                                           //configFolder
                $this->att->getConfigSiteFolder() . DIRECTORY_SEPARATOR . 'config.php',      //configSite
                $this->att->getConfigTemplateFolder() . DIRECTORY_SEPARATOR . 'config.php',  //configMaster
            );
        }
        ProcessService::aturKepemilikanDirektori(config('siappakai.root.folder'));
    }
}
