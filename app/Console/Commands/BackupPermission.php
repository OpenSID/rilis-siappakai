<?php

namespace App\Console\Commands;

use App\Http\Controllers\Helpers\AttributeSiapPakaiController;
use App\Http\Controllers\Helpers\CommandController;
use Illuminate\Console\Command;

class BackupPermission extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'siappakai:backup-permission';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Melakukan backup permission';

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
        $backup = $this->att->getSiteFolder() . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'backup';
        $this->comm->chownCommand($backup);
    }
}
