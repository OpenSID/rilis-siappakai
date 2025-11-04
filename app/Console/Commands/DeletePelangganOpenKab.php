<?php

namespace App\Console\Commands;

use App\Http\Controllers\Helpers\AttributeSiapPakaiController;
use App\Http\Controllers\Helpers\KoneksiController;
use App\Models\Aplikasi;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class DeletePelangganOpenKab extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'siappakai:delete-pelanggan-openkab {--kode_desa=} {--domain_opensid=} {--port_domain=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Hapus Pelanggan Dasbor SiapPakai melalui Layanan';

    private $att;
    private $koneksi;
    private $ip_source_code;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->att = new AttributeSiapPakaiController();
        $this->koneksi = new KoneksiController();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $kodedesa = $this->option('kode_desa');
        $domain = $this->option('domain_opensid');
        $port_domain = $this->option('port_domain');
        $apacheDomain = $this->att->getApacheConfDir() . $domain . '.conf';
        $apacheSSL = $this->att->getApacheConfSym() . $domain . '.conf';
        $this->att->setSiteFolderOpensid($this->att->getMultisiteFolder() . $kodedesa);
        $this->ip_source_code = env('OPENKAB') == 'true' ? Aplikasi::pengaturan_aplikasi()['ip_source_code'] : 'localhost';

        $database = $this->koneksi->cekDatabasePbb($kodedesa);

        try {
            /** Proses database */
            if ($database == true) {
                // Hapus Database PBB
                DB::statement('DROP DATABASE db_' . $kodedesa . '_pbb');

                // Hapus Username
                DB::statement("DROP USER 'user_$kodedesa'@'$this->ip_source_code' ");
            }

            if (file_exists($this->att->getSiteFolderOpensid())) {
                // Hapus Folder Pelanggan
                exec('sudo rm -R ' . $this->att->getSiteFolderOpensid());
            }

            if (file_exists($apacheDomain)) {
                // Hapus Domain Pelanggan
                exec('sudo rm ' . $apacheDomain);

                // Hapus SSL
                exec('sudo unlink ' . $apacheSSL);

                //Hapus Firewall
                exec('sudo ufw delete allow ' . $port_domain);
            }

            exec("sudo service apache2 restart");

            return die("Informasi : berhasil menghapus pelanggan Dasbor SiapPakai!!!");
        } catch (Exception $ex) {
            return die("Peringatan : database tidak ditemukan !!!");
        }
    }
}
