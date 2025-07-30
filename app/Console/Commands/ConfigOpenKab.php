<?php

namespace App\Console\Commands;

use App\Http\Controllers\Helpers\AttributeSiapPakaiController;
use App\Http\Controllers\Helpers\CommandController;
use App\Http\Controllers\Helpers\EnvController;
use App\Http\Controllers\Helpers\KoneksiController;
use Illuminate\Console\Command;

class ConfigOpenKab extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'siappakai:config-openkab {--app=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Konfigurasi untuk mengaktifkan atau menonaktifkan SiapPakai untuk OpenKab';

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
        $app = $this->option('app');
        if (file_exists($this->att->getSiteFolder())) {
            $this->configOpenKab($app);
        }
    }

    public function configOpenKab($app)
    {
        if ($app == 'true') {
            $openkab = 'true';
            exec('unlink ' . env('ROOT_OPENSID') . 'phpmyadmin');
        } else if ($app == 'false') {
            $openkab = 'false';
            exec('ln -s /usr/share/phpmyadmin ' . env('ROOT_OPENSID') . 'phpmyadmin');
        } else {
            return die('openkab tidak diatur');
        }

        $envExample = $this->att->getSiteFolder() . DIRECTORY_SEPARATOR . 'master-template' . DIRECTORY_SEPARATOR . '.env.example';
        $env = $this->att->getSiteFolder() . DIRECTORY_SEPARATOR . '.env';
        $this->files->envSiapPakai($envExample, $env, $this->att->getHost(), $this->database, $this->att->getUsername(), $this->att->getPassword(), $this->att->getRootFolder(), $this->att->getTokenGithub(), $this->att->getFtpUser(), $this->att->getFtpPass(), $this->att->getAppEnv(), $this->att->getAppDebug(), $openkab, $this->att->getAppUrl());

        $this->command->keyGenerateCommand($this->att->getSiteFolder());
        $this->command->chownCommand($this->att->getSiteFolder());
    }
}
