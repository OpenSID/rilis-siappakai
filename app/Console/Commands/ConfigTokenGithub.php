<?php

namespace App\Console\Commands;

use App\Http\Controllers\Helpers\AttributeSiapPakaiController;
use App\Http\Controllers\Helpers\CommandController;
use App\Http\Controllers\Helpers\EnvController;
use App\Http\Controllers\Helpers\KoneksiController;
use Illuminate\Console\Command;

class ConfigTokenGithub extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'siappakai:config-token {--token_github=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Konfigurasi token github ke .env';

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
        $this->att = new AttributeSiapPakaiController();
        $this->files = new EnvController();
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
        $token_github = $this->option('token_github');
        if (file_exists($this->att->getSiteFolder())) {
            $this->configTokenGithub($token_github);
        }
    }

    private function configTokenGithub($token_github)
    {

        $envExample = $this->att->getSiteFolder() . DIRECTORY_SEPARATOR . 'master-template' . DIRECTORY_SEPARATOR . '.env.example';
        $env = $this->att->getSiteFolder() . DIRECTORY_SEPARATOR . '.env';
        $this->files->envSiapPakai($envExample, $env, $this->att->getHost(), $this->database, $this->att->getUsername(), $this->att->getPassword(), $this->att->getRootFolder(), $token_github, $this->att->getFtpUser(), $this->att->getFtpPass(), $this->att->getAppEnv(), $this->att->getAppDebug(), $this->att->getOpenKab(), $this->att->getAppUrl());

        $this->command->keyGenerateCommand($this->att->getSiteFolder());
    }
}
