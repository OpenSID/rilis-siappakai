<?php

namespace App\Console\Commands;

use App\Models\Aplikasi;
use App\Models\Pelanggan;
use Illuminate\Console\Command;
use App\Services\ProcessService;
use App\Http\Controllers\Helpers\EnvController;
use App\Http\Controllers\Helpers\TemaController;
use App\Http\Controllers\Helpers\IndexController;
use App\Http\Controllers\Helpers\ConfigController;
use App\Http\Controllers\Helpers\CommandController;
use App\Http\Controllers\Helpers\AttributeSiapPakaiController;

class UpdatePelanggan extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'siappakai:update-pelanggan {--kode_desa=} {--token_premium=} {--kode_desa_default=} {--domain_opensid=} {--langganan_opensid=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Perbarui pelanggan premium atau pelanggan Dasbor SiapPakai';

    private $att;
    private $comm;
    private $filesEnv;
    private $filesOpenSID;
    private $filesIndex;

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
        $this->att = new AttributeSiapPakaiController();
        $this->comm = new CommandController();
        $this->filesEnv = new EnvController();
        $this->filesOpenSID = new ConfigController();
        $this->filesIndex = new IndexController();
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
        $domain = $this->option('domain_opensid');
        $langganan_opensid = $this->option('langganan_opensid');
        $urlApp = substr($domain, 0, 8) == "https://" ? $domain : "https://" . $domain;

        // Aktifasi Tema
        $aktivasi = new TemaController;
        $this->tema = $aktivasi->aktifasiTema($kodedesa_default);

        // Email
        $pelanggan = Pelanggan::where('kode_desa', $kodedesa_default)->first();
        $this->email = $this->filesOpenSID->konfigurasiEmailId($pelanggan->id);

        // folder
        $this->att->setSiteFolderOpensid($this->att->getMultisiteFolder() . $kodedesa);
        $this->att->setSiteFolderApi($this->att->getMultisiteFolder() . $kodedesa . DIRECTORY_SEPARATOR . 'api-app');
        $this->att->setSiteFolderPbb($this->att->getMultisiteFolder() . $kodedesa . DIRECTORY_SEPARATOR . 'pbb-app');

        // index.php
        $this->att->setIndexDesa($this->att->getSiteFolderOpensid() . DIRECTORY_SEPARATOR . 'index.php');
        $this->att->setIndexApi($this->att->getSiteFolderApi() . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'index.php');
        $this->att->setIndexPbb($this->att->getSiteFolderPbb() . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'index.php');

        if (file_exists($this->att->getSiteFolderOpensid())) {
            // opensid
            $this->setConfigOpensid($kodedesa, $token_premium, $langganan_opensid);

            // opensid api
            $this->setEnvApi($urlApp, $kodedesa, $kodedesa_default, $token_premium, $langganan_opensid);

            // pbb
            $this->setEnvPbb($urlApp, $kodedesa, $kodedesa_default, $token_premium, $langganan_opensid);
        }

        $this->comm->notifMessage('update pelanggan');
        ProcessService::aturKepemilikanDirektori(config('siappakai.root.folder'));
    }

    private function setConfigOpensid($kodedesa, $token_premium, $langganan_opensid)
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

        // unlink
        $this->comm->unlinkCommandOpenSid($this->att->getSiteFolderOpensid());

        // ubah symlink di file index
        if (file_exists($this->att->getIndexDesa())) {
            $this->filesIndex->indexPhpOpensid(
                $this->att->getRootFolder() . 'master-opensid' . DIRECTORY_SEPARATOR . $langganan_opensid,
                $this->att->getSiteFolderOpensid(),
                $this->att->getIndexTemplate(),
                $this->att->getIndexDesa()
            );
        }

        // buat symlink dengan menjalankan file index.php
        $this->comm->migratePremium($this->att->getSiteFolderOpensid());
    }

    private function setEnvApi($urlApp, $kodedesa, $kodedesa_default, $token_premium, $langganan_opensid)
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

            // unlink
            $this->comm->unlinkCommandAppLaravel($this->att->getSiteFolderApi());

            // ubah symlink di file index
            if (file_exists($this->att->getIndexApi())) {
                $this->filesIndex->indexPhpApi(
                    $this->filesIndex->langganan_opensid($langganan_opensid, 'opensid-api'),  //apiFolderFrom,
                    $this->att->getRootFolder() . 'master-api' . DIRECTORY_SEPARATOR,                   //apiFolderFrom,
                    $this->att->getSiteFolderApi(),                                                                      //apiFolderTo,
                    $this->att->getIndexTemplateApi(),
                    $this->att->getIndexApi()
                );
            }

            // buat symlink dengan menjalankan file index.php
            $this->comm->indexCommand($this->att->getSiteFolderApi() . DIRECTORY_SEPARATOR . 'public'); // index.php
        }
    }

    private function setEnvPbb($urlApp, $kodedesa, $kodedesa_default, $token_premium, $langganan_opensid)
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

            // unlink
            $this->comm->unlinkCommandAppLaravel($this->att->getSiteFolderPbb());
            $this->comm->unlinkCommandAppLaravelPublic($this->att->getSiteFolderPbb());

            // ubah symlink di file index
            if (file_exists($this->att->getIndexPbb())) {
                $this->filesIndex->indexPhpPbb(
                    $this->filesIndex->langganan_opensid($langganan_opensid, 'pbb_desa'),  //pbbFolder
                    $this->att->getRootFolder() . 'master-pbb' . DIRECTORY_SEPARATOR,      //pbbFolderFrom
                    $this->att->getSiteFolderPbb(),                                                              //pbbFolderTo
                    $this->att->getIndexTemplatePbb(),
                    $this->att->getIndexPbb()
                );
            }

            // buat symlink dengan menjalankan file index.php
            $this->comm->indexCommand($this->att->getSiteFolderPbb() . DIRECTORY_SEPARATOR . 'public'); // index.php
        }
    }
}
