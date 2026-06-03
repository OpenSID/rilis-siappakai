<?php

namespace App\Console\Commands;

use App\Models\Aplikasi;
use App\Services\OpenLiteSpeedService;
use App\Http\Controllers\Helpers\AttributeSiapPakaiController;
use App\Http\Controllers\Helpers\CommandController;
use Illuminate\Console\Command;

class UpdateSsl extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'siappakai:update-ssl {--domain=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update SSL pada semua domain yang belum aktif.';

    private $att;
    private $comm;
    private $aplikasi;
    private $openLiteSpeed;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(
        AttributeSiapPakaiController $att,
        CommandController $comm,
        Aplikasi $aplikasi
    ) {
        parent::__construct();
        $this->att = $att;
        $this->comm = $comm;
        $this->aplikasi = $aplikasi;

        // Initialize OpenLiteSpeed service if dependencies are available
        try {
            $this->openLiteSpeed = app(OpenLiteSpeedService::class);
        } catch (\Exception $e) {
            $this->openLiteSpeed = null;
        }
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
            $server_panel = $this->aplikasi::pengaturan_aplikasi()['server_panel'];

            if ($server_panel == "3" && $this->openLiteSpeed) {
                $this->setVhostOpenLiteSpeed($domain);
            } else {
                $this->setVhostApache($domain);
            }
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

        exec("service apache2 restart");
    }

    private function setVhostOpenLiteSpeed($domain)
    {
        $sslCheck = $this->openLiteSpeed->checkSSLCertificate($domain);

        if (!$sslCheck['exists'] || !$sslCheck['valid']) {
            try {
                // Buat SSL
                $webroot = $this->att->getSiteFolder();
                $this->openLiteSpeed->generateSSLCertificate($domain, $webroot);
                $this->informasi('membuat', $domain);

                // Reload OLS
                $this->openLiteSpeed->reload();
            } catch (\Exception $e) {
                $this->error("Gagal membuat SSL OLS untuk {$domain}: " . $e->getMessage());
            }
        } else {
            $this->info("SSL OLS untuk domain {$domain} sudah aktif.");
        }
    }

    public function informasi($info, $domain)
    {
        var_dump('Informasi : Berhasil ' . $info . ' ssl ' . $domain);
    }
}
