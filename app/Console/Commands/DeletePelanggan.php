<?php

namespace App\Console\Commands;

use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class DeletePelanggan extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'siappakai:delete-pelanggan {--kode_desa=} {--domain_opensid=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Hapus Pelanggan Dasbor SiapPakai melalui Layanan';

    private $multisiteFolder;
    private $rootFolder;
    private $siteFolder;
    private $apacheConfDir = "/etc/apache2/sites-available/";

    private $host;
    private $port;
    private $user;
    private $password;
    private $database;
    private $database_pbb;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->setMultisiteFolder(env('MULTISITE_OPENSID'));
        $this->setRootFolder(env('ROOT_OPENSID'));
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
        $apacheDomain = $this->apacheConfDir . $domain . '.conf';
        $apacheSSL = $this->apacheConfDir . $domain . '-le-ssl.conf';
        $this->setSiteFolder($this->getMultisiteFolder() . $kodedesa);

        $this->setHost(env('DB_HOST'));
        $this->setPort(env('DB_PORT'));
        $this->setUser('user_' . $kodedesa);
        $this->setPassword('pass_' . $kodedesa);
        $this->setDatabase('db_' . $kodedesa);
        $this->setDatabasePbb('db_' . $kodedesa . '_pbb');

        try {
            $koneksi = mysqli_connect($this->getHost(), $this->getUser(), $this->getPassword(), $this->getDatabase());

            /** Proses database */
            if ($koneksi) {
                // Hapus Database OpenSID dan PBB
                DB::statement('DROP DATABASE db_' . $kodedesa);
                DB::statement('DROP DATABASE db_' . $kodedesa . '_pbb');

                // Hapus Username
                DB::statement("DROP USER 'user_$kodedesa'@'localhost' ");
            }

            if (file_exists($this->getSiteFolder())) {
                // Hapus Folder Pelanggan
                exec('sudo rm -R ' . $this->getSiteFolder());
            }

            if (file_exists($apacheDomain)) {
                // Hapus Domain Pelanggan
                exec('sudo rm ' . $apacheDomain);
            }

            if (file_exists($apacheSSL)) {
                // Hapus SSL
                exec('sudo rm ' . $apacheSSL);
            }

            exec("sudo service apache2 restart");

            return die("Informasi : berhasil menghapus pelanggan Dasbor SiapPakai!!!");
        } catch (Exception $ex) {
            return die("Peringatan : database tidak ditemukan !!!");
        }
    }

    /**
     * Get the value of multisiteFolder
     */
    public function getMultisiteFolder()
    {
        return $this->multisiteFolder;
    }

    /**
     * Set the value of multisiteFolder
     *
     * @return  self
     */
    public function setMultisiteFolder($multisiteFolder)
    {
        $this->multisiteFolder = $multisiteFolder;

        return $this;
    }

    /**
     * Get the value of rootFolder
     */
    public function getRootFolder()
    {
        return $this->rootFolder;
    }

    /**
     * Set the value of rootFolder
     *
     * @return  self
     */
    public function setRootFolder($rootFolder)
    {
        $this->rootFolder = $rootFolder;

        return $this;
    }

    /**
     * Get the value of siteFolder
     */
    public function getSiteFolder()
    {
        return $this->siteFolder;
    }

    /**
     * Set the value of siteFolder
     *
     * @return  self
     */
    public function setSiteFolder($siteFolder)
    {
        $this->siteFolder = $siteFolder;

        return $this;
    }

    /**
     * Get the value of host
     */
    public function getHost()
    {
        return $this->host;
    }

    /**
     * Set the value of host
     *
     * @return  self
     */
    public function setHost($host)
    {
        $this->host = $host;

        return $this;
    }

    /**
     * Get the value of port
     */
    public function getPort()
    {
        return $this->port;
    }

    /**
     * Set the value of port
     *
     * @return  self
     */
    public function setPort($port)
    {
        $this->port = $port;

        return $this;
    }

    /**
     * Get the value of user
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set the value of user
     *
     * @return  self
     */
    public function setUser($user)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get the value of password
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * Set the value of pasword
     *
     * @return  self
     */
    public function setPassword($password)
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Get the value of database
     */
    public function getDatabase()
    {
        return $this->database;
    }

    /**
     * Set the value of database
     *
     * @return  self
     */
    public function setDatabase($database)
    {
        $this->database = $database;

        return $this;
    }

    /**
     * Get the value of database_pbb
     */
    public function getDatabasePbb()
    {
        return $this->database_pbb;
    }

    /**
     * Set the value of database_pbb
     *
     * @return  self
     */
    public function setDatabasePbb($database_pbb)
    {
        $this->database_pbb = $database_pbb;

        return $this;
    }
}
