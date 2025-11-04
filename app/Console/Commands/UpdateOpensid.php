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

    private $att;

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

        $this->setPdoDatabase();
    }

    public function setPdoDatabase(){
        $path = config('siappakai.root.folder') . 'master-opensid/premium/config/database.php';

        // Baca isi file
        $content = @file_get_contents($path);
        if ($content === false) {
            echo "❌ Gagal membaca file: {$path}\n";
            return;
        }

        // Cari baris 'options' => $options['options'] ?? [], dan replace dengan PDO options
        $pattern = "/'options'\\s*=>\\s*\\\$options\\['options'\\]\\s*\\?\\?\\s*\\[\\],/i";
        $replacement = "'options' => [\n            PDO::ATTR_EMULATE_PREPARES => true,\n        ],";

        if (preg_match($pattern, $content)) {
            $newContent = preg_replace($pattern, $replacement, $content, 1);
            if ($newContent !== null && $newContent !== $content) {
                if (file_put_contents($path, $newContent) !== false) {
                    echo "✅ Berhasil mengganti blok 'options' di: {$path}\n";
                } else {
                    echo "❌ Gagal menulis ke file: {$path}\n";
                }
            } else {
                echo "❌ Tidak ada perubahan yang dilakukan pada file: {$path}\n";
            }
        } else {
            echo "❌ Tidak ditemukan baris 'options' => \$options['options'] ?? [], di file: {$path}\n";
        }
    }
}
