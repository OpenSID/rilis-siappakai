<?php

namespace App\Console\Commands;

use App\Http\Controllers\Helpers\AttributeSiapPakaiController;
use App\Http\Controllers\Helpers\CommandController;
use App\Models\Pelanggan;
use Illuminate\Console\Command;

class InstallModulDesa extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'siappakai:install-modul-desa {modul} {--kode_desa=} {--pasang_baru}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Install modul perdesa (tanpa download paket), example: php artisan siappakai:install-modul-desa prodeskel  kode_desa_1,kodedesa_2';

    private $att;
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
        $this->command = new CommandController();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $multisite = $this->att->getMultisiteFolder();
        $listDesa = [];
        $kodeDesa = $this->option('kode_desa');
        if($kodeDesa){
            $listDesa = explode(',',$kodeDesa);
        }else {
            $listDesa = Pelanggan::select(['kode_desa'])->get()->map(static fn($q) => str_replace('.', '', $q->kode_desa) )->toArray();
        }
        $modul = $this->input->getArgument('modul');
        $version = $this->option('pasang_baru') ? 1 : 0;
        $emptyUrl               = 'url';
        $nameVersion            = implode('___', [$modul, $emptyUrl, $version]);
        foreach($listDesa as $kode){
            // variable
            $folderOpensid = $multisite . $kode;

            if (file_exists($folderOpensid)) {
                // eksekusi index.php di opensid untuk menjalankan migrasi modul
                $this->components->info(sprintf('Pasang modul %s pada folder %s', $folderOpensid, $nameVersion));
                $this->command->installModul($folderOpensid, $nameVersion);
            }
        }
    }
}
