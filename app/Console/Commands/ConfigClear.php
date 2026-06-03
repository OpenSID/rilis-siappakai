<?php

namespace App\Console\Commands;

use App\Http\Controllers\Helpers\AttributeSiapPakaiController;
use Illuminate\Console\Command;
use Symfony\Component\Process\Process;

class ConfigClear extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'siappakai:config-clear';

    /**
     * Perintah ini digunakan ketika terjadi perubahan pada command dan jobs perlu jalankan ini pada server.
     *
     * @var string
     */
    protected $description = 'Menjalankan perintah artisan optimize, config:cache, config:clear, config:clear';

    private $att;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->att = new AttributeSiapPakaiController();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        // optimize
        $optimize = new Process(['php', 'artisan', 'optimize:clear'], $this->att->getSiteFolder());
        $optimize->setTimeout(null);
        $optimize->run();

        // config:cache
        $config_cache = new Process(['php', 'artisan', 'config:cache'], $this->att->getSiteFolder());
        $config_cache->setTimeout(null);
        $config_cache->run();

        // config:clear
        $config_clear = new Process(['php', 'artisan', 'config:clear'], $this->att->getSiteFolder());
        $config_clear->setTimeout(null);
        $config_clear->run();

        // cache:clear
        $cache_clear = new Process(['php', 'artisan', 'cache:clear'], $this->att->getSiteFolder());
        $cache_clear->setTimeout(null);
        $cache_clear->run();

        $pbb_migrasi = new Process(['supervisorctl', 'restart', 'all'], $this->att->getSiteFolder());
        $pbb_migrasi->setTimeout(null);
        $pbb_migrasi->run();
    }
}
