<?php

namespace App\Console\Commands;

use App\Http\Controllers\Helpers\AttributeSiapPakaiController;
use App\Http\Controllers\Helpers\ConfigController;
use App\Http\Controllers\Helpers\EnvController;
use App\Models\Aplikasi;
use App\Models\Pelanggan;
use Illuminate\Console\Command;

class ConfigFtpApi extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'siappakai:config-ftp-api';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Konfigurasi FTP untuk OpenSID API ';

    private $att;
    private $filesEnv;
    private $emails;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->filesEnv = new EnvController();
        $this->att = new AttributeSiapPakaiController();
        $this->emails = new ConfigController();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $pelanggans = Pelanggan::get();
        foreach ($pelanggans as $item) {
            $this->configFTP($item);
        }
    }

    private function configFTP($item)
    {
        $kodedesa = str_replace('.', '', $item->kode_desa);
        $urlApp = substr($item->domain_opensid, 0, 8) == "https://" ? $item->domain_opensid : "https://" . $item->domain_opensid;
        $this->att->setSiteFolderOpensid($this->att->getMultisiteFolder() . $kodedesa);

        $openkab = env('OPENKAB') == 'true' ? nama_database_gabungan() : $kodedesa;

        if (file_exists($this->att->getSiteFolderOpensid())) {
            $this->att->setSiteFolderApi($this->att->getSiteFolderOpensid() . DIRECTORY_SEPARATOR . 'api-app');
            $this->att->setIndexApi($this->att->getSiteFolderApi() . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'index.php');

            $email = $this->emails->konfigurasiEmailId($item->id);

            if (file_exists($this->att->getSiteFolderApi())) {
                $this->filesEnv->envApi(
                    $this->att->getHost(),
                    $this->att->getTemplateFolderApi(),
                    $this->att->getServerLayanan(),
                    $this->att->getSiteFolderApi(),
                    $item->kode_desa,
                    $openkab,
                    $item->token_premium,
                    $email['mail_host'],
                    $email['mail_user'],
                    $email['mail_pass'],
                    $email['mail_address'],
                    $urlApp,
                    $this->att->getFtpUser(),
                    $this->att->getFtpPass(),
                );
            }
        }
    }
}
