<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Process\Process;

class Migrate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'siappakai:migrate {--path=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrasikan OpenSID, OpenSID API, dan Aplikasi PBB melalui terminal';

    /**
     * Create a new command instance.
     *
     * @return void
     */
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
        $path = $this->option('path');

        // run migrasi opensid
        $opensid = new Process(['php', 'index.php'], $path);
        $opensid->setTimeout(null);
        $opensid->run();
    }
}
