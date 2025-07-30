<?php

namespace App\Console\Commands;

use App\Jobs\InstallModuleJob;
use Illuminate\Console\Command;
use App\Services\ProcessService;
use App\Services\OpensidUpdateService;

class UpdateOpensid extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'siappakai:update-opensid';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Perbarui versi premium dan umum(dapat dilakukan setiap hari melalui cronjob)';


    public function __construct()
    {
        parent::__construct();
    }


    /**
     * Execute the console command.
     *
     * Perbarui versi premium siappakai dan opensid
     *
     * @return void
     */
    public function handle()
    {
        // Buat instance dari kelas OpensidUpdateService
        $updateOpensid = new OpensidUpdateService();

        // Perbarui versi opensid Umum
        echo "Memulai update Opensid Umum \n";
        $updateOpensid->update('umum');
        echo "Update Opensid Umum Selesai \n";

        // Perbarui versi opensid premium
        echo "Memulai update Opensid Premium \n";
        $updateOpensid->update('premium');
        echo "Update Opensid Premium Selesai \n";

        // perbarui Module di Master premium
        InstallModuleJob::dispatch();
        ProcessService::aturKepemilikanDirektori(config('siappakai.root.folder'));
    }
}
