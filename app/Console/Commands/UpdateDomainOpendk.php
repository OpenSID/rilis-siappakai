<?php

namespace App\Console\Commands;

use App\Http\Controllers\Helpers\AapanelController;
use App\Http\Controllers\Helpers\AttributeSiapPakaiController;
use App\Http\Controllers\Helpers\CommandController;
use App\Models\Aplikasi;
use App\Services\FileService;
use App\Services\ProcessService;
use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;

class UpdateDomainOpendk extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'siappakai:update-domain-opendk {--kode_kecamatan=} {--domain_opendk_lama=} {--domain_opendk=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Perbarui domain opendk';

    private $aapanel;
    private $aplikasi;
    private $att;
    private $comm;
    private $files;
    private $path;
    private $server_panel;
    private $fileService;

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
        $this->fileService = new FileService();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $kodekecamatan = $this->option('kode_kecamatan');
        $domain_lama = $this->option('domain_opendk_lama');
        $domain = $this->option('domain_opendk');
        $this->server_panel = $this->aplikasi::pengaturan_aplikasi()['server_panel'];
        $this->path = 'multisite' . DIRECTORY_SEPARATOR . $kodekecamatan;

        $this->att->setSiteFolderOpendk($this->att->getMultisiteFolder() . $kodekecamatan);

        if (file_exists($this->att->getSiteFolderOpendk())) {
            $this->setVhostUpdate($kodekecamatan, $domain_lama, $domain);
        }

        $this->comm->notifMessage('update domain');
        ProcessService::aturKepemilikanDirektori(config('siappakai.root.folder'));
    }

    public function setVhostUpdate($kodekecamatan, $domain_lama, $domain){
        if($this->server_panel == "1"){
            /** key dan url web di aaPanel */
            $phpversion = $this->aplikasi::aapanel()['aapanel_php'];
            $this->aapanel->key = $this->aplikasi::aapanel()['aapanel_key'];
            $this->aapanel->url = $this->aplikasi::aapanel()['aapanel_ip'];
            $this->att->setFtpUser($kodekecamatan . 'ftp');
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
            $this->setVhostOpendk($domain_lama, $domain);
            $this->setVhostApache($domain);
        }
    }

    private function setVhostOpendk($domain_lama, $domain)
    {
        // permission
        $vhost = $this->att->getSiteFolderOpendk() . DIRECTORY_SEPARATOR . 'vhost';
        $this->comm->chmodDirectoryCommand($vhost);

        if ($domain_lama) {
            $this->comm->removeFile($this->att->getApacheConfDir() . $domain_lama . '.conf');
            $this->comm->removeFile($this->att->getApacheConfDir() . $domain_lama . '-le-ssl.conf');
        }

        $dirPublic = $this->att->getSiteFolderOpendk() . DIRECTORY_SEPARATOR . 'public';
        $apacheTemplate = $this->files->get($this->att->getTemplateFolderOpendk() . DIRECTORY_SEPARATOR . 'vhost' . DIRECTORY_SEPARATOR . 'apache.conf');
        $content = str_replace(
            ['{$domain}', '{$domainLog}', '{$documentRoot}', '{$documentDirectory}'],
            [$domain, str_replace('.', '', $domain), $dirPublic, $dirPublic],
            $apacheTemplate
        );
        $this->files->replace($this->att->getSiteFolderOpendk() . DIRECTORY_SEPARATOR . 'vhost' . DIRECTORY_SEPARATOR . 'apache.conf', $content);

        $apacheTemplate = $this->files->get($this->att->getTemplateFolderOpendk() . DIRECTORY_SEPARATOR . 'vhost' . DIRECTORY_SEPARATOR . 'nginx');
        $content = str_replace(
            ['{$domain}', '{$domainLog}', '{$documentRoot}', '{$documentDirectory}'],
            [$domain, str_replace('.', '', $domain), $dirPublic, $dirPublic],
            $apacheTemplate
        );
        $this->files->replace($this->att->getSiteFolderOpendk() . DIRECTORY_SEPARATOR . 'vhost' . DIRECTORY_SEPARATOR . 'nginx', $content);
    }

    /**
     * Menulis file konfigurasi vhost Apache dan mengaktifkannya.
     *
     * @param string $domain Nama domain yang akan dibuatkan konfigurasi vhost-nya.
     */
    private function setVhostApache($domain)
    {
        // Remove http:// or https:// from domain if present

        $originalConf = $this->att->getSiteFolderOpendk() . DIRECTORY_SEPARATOR . 'vhost' . DIRECTORY_SEPARATOR . 'apache.conf';
        $this->comm->chownCommand($this->att->getApacheConfDir());

        $this->fileService->replaceFile($originalConf, $this->att->getApacheConfDir() . $domain . '.conf');
        exec("sudo a2ensite $domain.conf");

        // buat ssl
        $this->comm->certbotSsl($domain);

        exec("sudo service apache2 restart");
    }

}
