<?php

namespace App\Console\Commands;

use App\Jobs\InstallModuleJob;
use Illuminate\Console\Command;

class ModuleInstall extends Command
{

    protected $signature = 'siappakai:install-module';


    protected $description = 'Update Module Opensid';


    public function __construct()
    {
        parent::__construct();
       
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        InstallModuleJob::dispatch();
        return Command::SUCCESS;
    }
}
