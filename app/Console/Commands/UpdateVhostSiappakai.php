<?php

namespace App\Console\Commands;

use App\Http\Controllers\Helpers\AttributeSiapPakaiController;
use App\Http\Controllers\Helpers\CommandController;
use Illuminate\Console\Command;

class UpdateVhostSiappakai extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'siappakai:update-host-siappakai {domain} {--conf=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update domain siappakai, contoh: php artisan siappakai:update-host-siappakai panel.opendesa.id --conf=/etc/apache2/sites-available/siappakai.conf';

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
        $domain = $this->argument('domain');
        $fileConf = $this->option('conf') ?? $this->att->getApacheConfDir() . $domain . '.conf';
        $apacheConf = $this->att->getTemplateFolderSiapPakai() . DIRECTORY_SEPARATOR . 'apache.conf';
        $documentRoot = $this->att->getRootFolder() . 'public_html';
        $documentDirectory = rtrim($this->att->getRootFolder(), "/");

        if(file_exists($apacheConf)){
            $this->command->copyFile($apacheConf, $fileConf);
        }

        if(!file_exists($fileConf)){
            $this->error('File konfigurasi tidak ditemukan');
        }

        if(file_exists($fileConf)){
            //replace apache conf
            $file_contents = file_get_contents($fileConf);
            $new_contents = str_replace(['{$domain}', '{$documentRoot}', '{$documentDirectory}'], [$domain, $documentRoot, $documentDirectory], $file_contents);
            file_put_contents($fileConf, $new_contents);
            $this->info('Berhasil update vhost');

            //buat ssl
            exec("sudo a2ensite $domain.conf");
            $this->command->certbotSsl($domain);
            $this->info('Berhasil buat ssl');

            // restart apache
            $this->command->restartApache();
        }
    }
}
