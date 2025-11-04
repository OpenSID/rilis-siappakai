<?php

namespace App\Console\Commands;

use App\Http\Controllers\Helpers\AttributeSiapPakaiController;
use App\Http\Controllers\Helpers\CommandController;
use App\Models\Pelanggan;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class UpdateSettingOpensid extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'siappakai:update-setting-opensid {key} {--kode_desa=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update setting opensid example: php artisan update-setting-opensid mapbox_key --kode_desa=5272051008,5272041002';

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
        $key = $this->input->getArgument('key');
        // Ambil data dari layanan
        // ambil salah satu pelanggan untuk token cek ke layanan
        $pelanggan = Pelanggan::select(['token_premium'])->orderBy('tgl_akhir_saas', 'desc')->whereNotNull('token_premium')->first();
        $tokenPremium = $pelanggan->token_premium;
        $response = Http::withOptions([
            'base_uri' => $this->att->getServerLayanan(),
            // 'debug' => true,
        ])->withHeaders([
                'X-Requested-With' => 'XMLHttpRequest',
            ])
            ->withToken($tokenPremium)
            ->get('api/v1/setting-key/'.$key);

        if ($response->clientError()) {
            $this->error('Gagal mendapatkan data dari layanan dengan status '.$response->status());
        }

        if ($response->getStatusCode() == 200) {
            $setting = $response->throw()->json()['messages'];
            $value = $setting['value'];
            foreach($listDesa as $kode){
                // variable
                $folderOpensid = $multisite . $kode;

                if (file_exists($folderOpensid)) {
                    // eksekusi index.php di opensid untuk menjalankan migrasi modul
                    $this->components->info(sprintf('Update setting %s pada folder %s', $key, $folderOpensid));
                    $this->command->indexCli($folderOpensid, ['setting', $key, $value]);
                }else{
                    $this->components->info(sprintf('Folder Desa tidak ditemukan %s', $folderOpensid));
                }
            }
        }
    }
}
