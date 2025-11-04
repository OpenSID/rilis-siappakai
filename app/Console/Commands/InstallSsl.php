<?php

namespace App\Console\Commands;

use App\Http\Controllers\Helpers\AttributeSiapPakaiController;
use App\Http\Controllers\Helpers\CommandController;
use Illuminate\Console\Command;

class InstallSsl extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'siappakai:install-ssl {--domain=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Instal SSL pada domain tertentu';

    private $att;
    private $comm;

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
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $domain = $this->option('domain');

        if ($this->att->getSiteFolder()) {
            $this->setVhostApache($domain);
        }
    }

    // method konfigurasi vhost domain dan ssl
    private function setVhostApache($domain)
    {
        $ssl = $this->att->getApacheConfDir() . $domain . '-le-ssl.conf';

        if (file_exists($ssl)) {
            $this->comm->chownFileCommand($ssl);
            $this->comm->removeFile($ssl);

            $this->informasi('menghapus', $domain);
        }

        if (!file_exists($ssl)) {
            // buat ssl
            $this->comm->certbotSsl($domain);

            $this->informasi('membuat', $domain);
        }
    }

    public function informasi($info, $domain)
    {
        var_dump('Informasi : Berhasil ' . $info . ' ssl ' . $domain);
        return exec("sudo service apache2 restart");
    }
}
