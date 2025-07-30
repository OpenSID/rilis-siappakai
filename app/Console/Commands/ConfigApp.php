<?php

namespace App\Console\Commands;

use App\Http\Controllers\Helpers\AttributeSiapPakaiController;
use App\Http\Controllers\Helpers\CommandController;
use App\Http\Controllers\Helpers\EnvController;
use App\Http\Controllers\Helpers\KoneksiController;
use Illuminate\Console\Command;

class ConfigApp extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'siappakai:config-app {--production=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Konfigurasi APP_ENV production dan APP_DEBUG false';

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
        $app = $this->option('production');
        if (file_exists($this->att->getSiteFolder())) {
            $this->configProduction($app);
        }
    }

    private function configProduction($app)
    {
        if ($app == 'true') {
            $app_env = 'production';
            $app_debug = 'false';
        } else if ($app == 'false') {
            $app_env = 'local';
            $app_debug = 'true';
        } else {
            return die('app env tidak diatur');
        }

        $envExample = $this->att->getSiteFolder() . DIRECTORY_SEPARATOR . 'master-template' . DIRECTORY_SEPARATOR . '.env.example';
        $env = $this->att->getSiteFolder() . DIRECTORY_SEPARATOR . '.env';
        $this->files->envSiapPakai($envExample, $env, $this->att->getHost(), $this->database, $this->att->getUsername(), $this->att->getPassword(), $this->att->getRootFolder(), $this->att->getTokenGithub(), $this->att->getFtpUser(), $this->att->getFtpPass(), $app_env, $app_debug, $this->att->getOpenKab(), $this->att->getAppUrl());

        $this->command->keyGenerateCommand($this->att->getSiteFolder());
        $this->command->chownCommand($this->att->getSiteFolder());
    }
}
