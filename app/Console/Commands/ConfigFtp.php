<?php

namespace App\Console\Commands;

use App\Http\Controllers\Helpers\AttributeSiapPakaiController;
use App\Http\Controllers\Helpers\CommandController;
use App\Http\Controllers\Helpers\EnvController;
use App\Http\Controllers\Helpers\KoneksiController;
use Illuminate\Console\Command;

class ConfigFtp extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'siappakai:config-ftp {--ftp_user=} {--ftp_pass=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Konfigurasi FTP Password';

    private $att;
    private $files;
    private $database;
    private $command;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->files = new EnvController();
        $this->att = new AttributeSiapPakaiController();
        $this->command = new CommandController();

        $db = new KoneksiController();
        $this->database = $db->dbSiapPakai();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $ftp_user = $this->option('ftp_user');
        $ftp_pass = $this->option('ftp_pass');
        if (file_exists($this->att->getSiteFolder())) {
            $this->configFTP($ftp_user, $ftp_pass);
        }
    }

    private function configFTP($ftp_user, $ftp_pass)
    {
        if ($ftp_user == "") {
            $ftp_user = 'ftpuser';
        }

        $envExample = $this->att->getSiteFolder() . DIRECTORY_SEPARATOR . 'master-template' . DIRECTORY_SEPARATOR . '.env.example';
        $env = $this->att->getSiteFolder() . DIRECTORY_SEPARATOR . '.env';
        $this->files->envSiapPakai($envExample, $env, $this->att->getHost(), $this->database, $this->att->getUsername(), $this->att->getPassword(), $this->att->getRootFolder(), $this->att->getTokenGithub(), $ftp_user, $ftp_pass, $this->att->getAppEnv(), $this->att->getAppDebug(), $this->att->getOpenKab(), $this->att->getAppUrl());

        $this->command->keyGenerateCommand($this->att->getSiteFolder());
        $this->command->chownCommand($this->att->getSiteFolder());
    }
}
