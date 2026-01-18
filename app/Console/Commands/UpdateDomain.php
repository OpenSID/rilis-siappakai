<?php

namespace App\Console\Commands;

use App\Http\Controllers\Helpers\AapanelController;
use App\Http\Controllers\Helpers\AttributeSiapPakaiController;
use App\Http\Controllers\Helpers\CommandController;
use App\Http\Controllers\Helpers\ConfigController;
use App\Http\Controllers\Helpers\EnvController;
use App\Http\Controllers\Helpers\TemaController;
use App\Models\Aplikasi;
use App\Models\Pelanggan;
use App\Services\ProcessService;
use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;

class UpdateDomain extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'siappakai:update-domain {--kode_desa=} {--token_premium=} {--kode_desa_default=} {--domain_opensid_lama=} {--domain_opensid=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Perbarui domain pelanggan';

    private $aapanel;
    private $aplikasi;
    private $att;
    private $comm;
    private $files;
    private $filesEnv;
    private $filesOpenSID;
    private $path;
    private $server_panel;

    // opensid-api
    private $tema;

    // opensid-api
    private $email;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->aapanel = new AapanelController();
        $this->aplikasi = new Aplikasi();
        $this->att = new AttributeSiapPakaiController();
        $this->comm = new CommandController();
        $this->files = new Filesystem();
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
        $kodedesa = $this->option('kode_desa');
        $token_premium = $this->option('token_premium');
        $kodedesa_default = $this->option('kode_desa_default');
        $domain_lama = $this->option('domain_opensid_lama');
        $domain = $this->option('domain_opensid');
        $urlApp = substr($domain, 0, 8) == "https://" ? $domain : "https://" . $domain;
        $this->server_panel = $this->aplikasi::pengaturan_aplikasi()['server_panel'];
        $this->path = 'multisite' . DIRECTORY_SEPARATOR . $kodedesa;

        // Aktifasi Tema
        $aktivasi = new TemaController;
        $this->tema = $aktivasi->aktifasiTema($kodedesa_default);

        // Email
        $pelanggan = Pelanggan::where('kode_desa', $kodedesa_default)->first();
        $this->email = $this->filesOpenSID->konfigurasiEmailId($pelanggan->id);

        $this->att->setSiteFolderOpensid($this->att->getMultisiteFolder() . $kodedesa);
        $this->att->setSiteFolderApi($this->att->getMultisiteFolder() . $kodedesa . DIRECTORY_SEPARATOR . 'api-app');
        $this->att->setSiteFolderPbb($this->att->getMultisiteFolder() . $kodedesa . DIRECTORY_SEPARATOR . 'pbb-app');

        if (file_exists($this->att->getSiteFolderOpensid())) {
            $this->setConfigOpensid($kodedesa, $token_premium);
            $this->setVhostUpdate($kodedesa, $domain_lama, $domain);
        }

        if (file_exists($this->att->getSiteFolderApi())) {
            $this->setEnvApi($urlApp, $kodedesa, $kodedesa_default, $token_premium);
        }

        if (file_exists($this->att->getSiteFolderPbb())) {
            $this->setEnvPbb($urlApp, $kodedesa, $kodedesa_default, $token_premium);
        }

        $this->comm->notifMessage('update domain');
        ProcessService::aturKepemilikanDirektori(config('siappakai.root.folder'));

        ProcessService::runProcess(['sudo', 'php', 'index.php', 'koneksi_database', 'desaBaru'], $this->att->getSiteFolderOpensid());
    }

    public function setVhostUpdate($kodedesa, $domain_lama, $domain){
        if($this->server_panel == "1"){
            /** key dan url web di aaPanel */
            $phpversion = $this->aplikasi::aapanel()['aapanel_php'];
            $this->aapanel->key = $this->aplikasi::aapanel()['aapanel_key'];
            $this->aapanel->url = $this->aplikasi::aapanel()['aapanel_ip'];
            $this->att->setFtpUser($kodedesa . 'ftp');
            $this->att->setFtpPass(base64_encode(random_bytes(12)));

            // hapus site lama
            $idSite = $this->aapanel->siteList(1, 1, "php", $domain_lama);
            $this->aapanel->deleteSite($domain_lama, $idSite['data'][0]['id'], "0");

            /** buat site aaPanel
             * domain, path, desc, type_id, type, phpversion, port,
             * ftp, ftpuser, ftppass, db, dbuser, dbpass, setssl, forcessl
            */
            $this->aapanel->addSite(
                $domain, $this->path, str_replace('.', '_', $domain), 0, 'php', $phpversion, '80',
                null, null, null, null, null, null, 1, 1
            );

            // setDirectory disable anti xss
            $this->aapanel->userDir($this->path);
        }else{
            $this->setVhostOpensid($domain_lama, $domain);
            $this->setVhostApache($domain);
        }
    }

    private function setConfigOpensid($kodedesa, $token_premium)
    {
        // OpenSID premium
        $this->filesOpenSID->configDesa(
            $kodedesa,
            $token_premium, // diambil dari table pelanggan agar dapat digunakan di OpenKab
            $this->tema,
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

    private function setVhostOpensid($domain_lama, $domain)
    {
        // permission
        $vhost = $this->att->getSiteFolderOpensid() . DIRECTORY_SEPARATOR . 'vhost';
        $this->comm->chmodDirectoryCommand($vhost);

        if ($domain_lama) {
            $this->comm->removeFile($this->att->getApacheConfDir() . $domain_lama . '.conf');
            $this->comm->removeFile($this->att->getApacheConfDir() . $domain_lama . '-le-ssl.conf');
        }

        if ($domain) {
            $apacheTemplate = $this->files->get($this->att->getTemplateFolder() . DIRECTORY_SEPARATOR . 'vhost' . DIRECTORY_SEPARATOR . 'apache.conf');
            $content = str_replace(['{$domain}', '{$domainLog}', '{$documentRoot}', '{$documentDirectory}'], [$domain, str_replace('.', '', $domain), $this->att->getSiteFolderOpensid(), $this->att->getSiteFolderOpensid()], $apacheTemplate);
            $this->files->replace($this->att->getSiteFolderOpensid() . DIRECTORY_SEPARATOR . 'vhost' . DIRECTORY_SEPARATOR . 'apache.conf', $content);

            $apacheTemplate = $this->files->get($this->att->getTemplateFolder() . DIRECTORY_SEPARATOR . 'vhost' . DIRECTORY_SEPARATOR . 'nginx');
            $content = str_replace(['{$domain}', '{$domainLog}', '{$documentRoot}', '{$documentDirectory}'], [$domain, str_replace('.', '', $domain), $this->att->getSiteFolderOpensid(), $this->att->getSiteFolderOpensid()], $apacheTemplate);
            $this->files->replace($this->att->getSiteFolderOpensid() . DIRECTORY_SEPARATOR . 'vhost' . DIRECTORY_SEPARATOR . 'nginx', $content);
        }
    }

    private function setVhostApache($domain)
    {
        $originalConf = $this->att->getSiteFolderOpensid() . DIRECTORY_SEPARATOR . 'vhost' . DIRECTORY_SEPARATOR . 'apache.conf';
        $this->comm->copyFile($originalConf, $this->att->getApacheConfDir() . $domain . '.conf');
        exec("a2ensite $domain.conf");

        // buat ssl
        $this->comm->certbotSsl($domain);

        exec("sudo service apache2 restart");
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
}
