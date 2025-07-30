<?php

namespace App\Console\Commands;

use App\Http\Controllers\Helpers\AttributeSiapPakaiController;
use App\Http\Controllers\Helpers\CommandController;
use App\Http\Controllers\Helpers\ConfigController;
use App\Http\Controllers\Helpers\TemaController;
use Illuminate\Console\Command;

class ConfigEmailOpensid extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'siappakai:config-email-opensid {--kode_desa=} {--smtp_protocol=} {--smtp_host=} {--smtp_user=} {--smtp_pass=} {--smtp_port=} {--token_premium=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Pengaturan Email untuk konfigurasi config.php pada OpenSID';

    private $att;
    private $comm;
    private $filesOpenSID;
    private $kode_desa_default;
    private $smtp_protocol;
    private $smtp_host;
    private $smtp_user;
    private $smtp_pass;
    private $smtp_port;
    private $token_premium;
    private $tema;

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
        $this->filesOpenSID = new ConfigController();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $kodedesa = str_replace('.', '', $this->option('kode_desa'));
        $this->kode_desa_default = $this->option('kode_desa');
        $this->smtp_protocol = $this->option('smtp_protocol');
        $this->smtp_host = $this->option('smtp_host');
        $this->smtp_user = $this->option('smtp_user');
        $this->smtp_pass = $this->option('smtp_pass');
        $this->smtp_port = $this->option('smtp_port');
        $this->token_premium = $this->option('token_premium');

        // Aktifasi Tema
        $aktivasi = new TemaController;
        $this->tema = $aktivasi->aktifasiTema($this->kode_desa_default);

        $this->att->setSiteFolderOpensid($this->att->getMultisiteFolder() . $kodedesa);

        if (!file_exists($this->att->getSiteFolderOpensid())) {
            return die("Peringatan: kode desa tidak ditemukan");
        }

        if (file_exists($this->att->getSiteFolderOpensid())) {
            $this->setConfigOpensid($kodedesa);
        }

        $this->comm->notifMessage('update pelanggan');
    }

    private function setConfigOpensid($kodedesa)
    {
        $this->filesOpenSID->configDesa(
            $kodedesa,
            $this->token_premium,
            $this->tema,
            $this->smtp_protocol,
            $this->smtp_host,
            $this->smtp_user,
            $this->smtp_pass,
            $this->smtp_port,
            $this->att->getServerLayanan(),
            $this->att->getConfigSiteFolder(),                                           //configFolder
            $this->att->getConfigSiteFolder() . DIRECTORY_SEPARATOR . 'config.php',      //configSite
            $this->att->getConfigTemplateFolder() . DIRECTORY_SEPARATOR . 'config.php',  //configMaster
        );
    }
}
