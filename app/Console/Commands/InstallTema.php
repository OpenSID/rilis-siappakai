<?php

namespace App\Console\Commands;

use App\Http\Controllers\Helpers\AttributeSiapPakaiController;
use App\Http\Controllers\Helpers\CommandController;
use App\Http\Controllers\Helpers\ConfigController;
use App\Http\Controllers\Helpers\EnvController;
use App\Models\Aplikasi;
use App\Models\Pelanggan;
use App\Services\ProcessService;
use Illuminate\Console\Command;

class InstallTema extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'siappakai:install-tema {--tema=} {--kode_desa=} {--token_premium=} {--domain_opensid=} {--aktivasi_tema=} {--config_logo=} {--config_kode_kota=} {--config_fbadmin=} {--config_fbappid=} {--config_ip_address=} {--config_color=} {--config_fluid=} {--config_menu=} {--config_chats=} {--config_widget=} {--config_style=} {--config_hide_layanan=} {--config_hide_banner_layanan=} {--config_hide_banner_laporan=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Instal Tema Pro di desa tertentu berdasarkan kode desa';

    private $att;
    private $comm;
    private $email;
    private $filesEnv;
    private $filesOpenSID;

    //aktivasi tema pro
    private $config_temas;
    /** end */

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
        $this->filesEnv = new EnvController();
        $this->filesOpenSID = new ConfigController();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $tema = $this->option('tema');
        $kodedesa = str_replace('.', '', $this->option('kode_desa'));

        /** start --- melalui layanan ditambahkan berikut ... */
        $kodedesa_default = $this->option('kode_desa');
        $token_premium = $this->option('token_premium');
        $domain = $this->option('domain_opensid');
        $urlApp = substr($domain, 0, 8) == "https://" ? $domain : "https://" . $domain;

        $this->att->setSiteFolderOpensid($this->att->getMultisiteFolder() . $kodedesa);
        $this->att->setSiteFolderApi($this->att->getMultisiteFolder() . $kodedesa . DIRECTORY_SEPARATOR . 'api-app');
        $this->att->setSiteFolderPbb($this->att->getMultisiteFolder() . $kodedesa . DIRECTORY_SEPARATOR . 'pbb-app');

        // Aktivasi Tema
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
        /** end --- melalui layanan ...*/

        // Email
        $pelanggan = Pelanggan::where('kode_desa', $kodedesa_default)->first();
        $this->email = $this->filesOpenSID->konfigurasiEmailId($pelanggan->id);

        if (!file_exists($this->att->getSiteFolderOpensid())) {
            return die("Peringatan: kode desa tidak ditemukan");
        }

        if ($this->att->getSiteFolderOpensid()) {
            $this->PasangTemaPro($tema);

            /** start --- melalui layanan ditambahkan berikut ... */
            if ($token_premium != '') {
                // opensid
                $this->setConfigOpensid($kodedesa, $token_premium);

                // opensid api
                $this->setEnvApi($urlApp, $kodedesa, $kodedesa_default, $token_premium);

                // pbb
                $this->setEnvPbb($urlApp, $kodedesa, $kodedesa_default, $token_premium);
            }
            /** end */
        }

        $this->comm->notifMessage('install tema');
        ProcessService::aturKepemilikanDirektori(config('siappakai.root.folder'));
    }

    private function PasangTemaPro($tema)
    {
        $tema_pro = $this->att->getTemaProFolder() . DIRECTORY_SEPARATOR . $tema;
        $tema_desa = $this->att->getSiteFolderOpensid() . DIRECTORY_SEPARATOR . 'desa' . DIRECTORY_SEPARATOR . 'themes' . DIRECTORY_SEPARATOR . $tema;
        if (!file_exists($tema_pro)) {
            return die("Peringatan: tema pro tidak tersedia");
        }

        if (file_exists($tema_desa)) {
            $this->comm->removeDirectory($tema_desa);
        }

        if ($tema_pro) {
            $this->comm->symlinkDirectory($tema_pro, $tema_desa);
        }
    }

    /** start method --- melalui layanan ditambahkan berikut ... */
    private function setConfigOpensid($kodedesa, $token_premium)
    {
        // OpenSID premium
        $this->filesOpenSID->configDesa(
            $kodedesa,
            $token_premium, // diambil dari table pelanggan agar dapat digunakan di OpenKab
            $this->config_temas,
            $this->email['smtp_protocol'],
            $this->email['smtp_host'],
            $this->email['smtp_user'],
            $this->email['smtp_pass'],
            $this->email['smtp_port'],
            $this->att->getServerLayanan(),
            $this->att->getConfigSiteFolder(),                                           //configFolder
            $this->att->getConfigSiteFolder() . DIRECTORY_SEPARATOR . 'config.php',      //configSite
            $this->att->getConfigTemplateFolder() . DIRECTORY_SEPARATOR . 'config.php',  //configMaster
        );
    }

    private function setEnvApi($urlApp, $kodedesa, $kodedesa_default, $token_premium)
    {
        $openkab = env('OPENKAB') == 'true' ? nama_database_gabungan() : $kodedesa;
        if (file_exists($this->att->getSiteFolderApi())) {
            $this->filesEnv->envApi(
                $this->att->getHost(),
                $this->att->getTemplateFolderApi(),
                $this->att->getServerLayanan(),
                $this->att->getSiteFolderApi(),
                $kodedesa_default,
                $openkab,
                $token_premium, // diambil dari table pelanggan agar dapat digunakan di OpenKab
                $this->email['mail_host'],
                $this->email['mail_user'],
                $this->email['mail_pass'],
                $this->email['mail_address'],
                $urlApp,
                $this->att->getFtpUser(),
                $this->att->getFtpPass(),
            );
        }
    }

    private function setEnvPbb($urlApp, $kodedesa, $kodedesa_default, $token_premium)
    {
        if (file_exists($this->att->getSiteFolderPbb())) {
            $this->filesEnv->envPbb(
                $this->att->getHost(),
                $this->att->getTemplateFolderPbb(),
                $this->att->getServerLayanan(),
                $this->att->getSiteFolderPbb(),
                $kodedesa_default,
                $kodedesa,
                $urlApp . DIRECTORY_SEPARATOR . 'pbb',
                $token_premium,
            );
        }
    }
    /** end method --- melalui layanan ditambahkan berikut ... */
}
