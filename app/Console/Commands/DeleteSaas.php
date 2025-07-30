<?php

namespace App\Console\Commands;

use App\Models\Pelanggan;
use App\Services\ProcessService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;


class DeleteSaas extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'siappakai:delete-siapkai';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Hapus Dasbor SiapPakai sudah habis berlangganan';


    private $rootFolder;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->rootFolder = env('MULTISITE_OPENSID');
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $pelanggan = Pelanggan::get()->where('Remaining', '<=', -30);
        foreach ($pelanggan as  $value) {
            // disable vhost
            $process = new Process(['a2dissite', $value->domain_opensid . '.conf']);
            $process->run();

            // hapus file host
            if (File::isFile('/etc/apache2/sites-available/' . $value->domain_opensid . '.conf')) {
                File::delete('/etc/apache2/sites-available/' . $value->domain_opensid . '.conf');
                $restart = new Process(['systemctl', 'reload', 'apache2']);
                $restart->run();
                echo $restart->getOutput();
            }

            File::deleteDirectory($this->rootFolder . str_replace('.', '', $value->kode_desa));
            $value->delete();

            // hapus database
            $database = str_replace('.', '', $value->kode_desa);
            DB::statement("DROP DATABASE `db_{$database}`");
            DB::statement("DROP DATABASE `db_{$database}_pbb`");
        }

        ProcessService::aturKepemilikanDirektori(config('siappakai.root.folder'));
    }
}
