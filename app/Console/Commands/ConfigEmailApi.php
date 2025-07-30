<?php

namespace App\Console\Commands;

use App\Http\Controllers\Helpers\AttributeSiapPakaiController;
use App\Http\Controllers\Helpers\CommandController;
use App\Http\Controllers\Helpers\EnvController;
use App\Models\Aplikasi;
use App\Models\Pelanggan;
use Illuminate\Console\Command;

class ConfigEmailApi extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'siappakai:config-email-api {--kode_desa=} {--mail_host=} {--mail_user=} {--mail_pass=} {--mail_address=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Pengaturan Email untuk konfigurasi .env pada OpenSID API';

    private $att;
    private $comm;
    private $filesEnv;
    private $mail_host;
    private $mail_user;
    private $mail_pass;
    private $mail_address;

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
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $kodedesa = str_replace('.', '', $this->option('kode_desa'));
        $kodedesa_default = $this->option('kode_desa');
        $this->mail_host = $this->option('mail_host');
        $this->mail_user = $this->option('mail_user');
        $this->mail_pass = $this->option('mail_pass');
        $this->mail_address = $this->option('mail_address');

        $this->att->setSiteFolderApi($this->att->getMultisiteFolder() . $kodedesa . DIRECTORY_SEPARATOR . 'api-app');

        $pelanggan = $this->Pelanggan($kodedesa_default);

        if (file_exists($this->att->getSiteFolderApi()) && !is_null($pelanggan)) {
            $token_premium = $pelanggan->token_premium;
            $urlApp = $pelanggan->domain_opensid;

            $openkab = env('OPENKAB') == 'true' ? nama_database_gabungan() : $kodedesa;
            $this->filesEnv->envApi(
                $this->att->getHost(),
                $this->att->getTemplateFolderApi(),
                $this->att->getServerLayanan(),
                $this->att->getSiteFolderApi(),
                $kodedesa_default,
                $openkab,
                $token_premium,
                $this->mail_host,
                $this->mail_user,
                $this->mail_pass,
                $this->mail_address,
                $urlApp,
                $this->att->getFtpUser(),
                $this->att->getFtpPass(),
            );

            $this->comm->notifMessage('config email');
        } else {
            return die("Informasi: pelanggan tidak terdaftar");
        }
    }

    private function Pelanggan($kodedesa_default)
    {
        $data = Pelanggan::where('kode_desa', $kodedesa_default)->first();
        return $data;
    }
}
