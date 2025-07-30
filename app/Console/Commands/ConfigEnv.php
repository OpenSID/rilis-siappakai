<?php

namespace App\Console\Commands;

use App\Http\Controllers\Helpers\CommandController;
use App\Http\Controllers\Helpers\IndexController;
use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;

class ConfigEnv extends Command
{
    /**
     * Nama dan signature dari console command.
     *
     * @var string
     */
    protected $signature = 'siappakai:config-env {--db_host=} {--db_username=} {--db_password=} {--dir_root=}';

    /**
     * Deskripsi dari console command.
     *
     * @var string
     */
    protected $description = 'Konfigurasi .env.example dan ubah menjadi .env serta konfigurasi index.php';

    private $command;
    private $dirRoot;
    private $files;

    /**
     * Membuat instance baru dari command.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->command = new CommandController();
        $this->files = new Filesystem();
    }

    /**
     * Menjalankan console command.
     *
     * @return int
     */
    public function handle()
    {
        // Mengambil opsi dari command line
        $db_host = $this->option('db_host');
        $db_username = $this->option('db_username');
        $db_password = $this->option('db_password');
        $dir_root = $this->formatDirRoot($this->option('dir_root'));

        $this->dirRoot = $dir_root == "//" ? path_root_siappakai('root_vps') : $dir_root;
        $dashboard = $this->dirRoot . 'dasbor-siappakai';

        if ($dashboard) {
            $this->configEnv($dashboard, $db_host, $db_username, $db_password);
            $this->configIndex($dashboard);
            $this->configWorker($dashboard);
        }
    }

    /**
     * Format direktori root.
     *
     * @param string $dir_root
     * @return string
     */
    private function formatDirRoot($dir_root)
    {
        $dir_root = rtrim($dir_root, "/");
        return substr($dir_root, 0, 1) == "/" ? $dir_root . "/" : "/" . $dir_root . "/";
    }

    /**
     * Konfigurasi file .env.
     *
     * @param string $dashboard
     * @param string $db_host
     * @param string $db_username
     * @param string $db_password
     * @return void
     */
    private function configEnv($dashboard, $db_host, $db_username, $db_password)
    {
        $envExample = $dashboard . DIRECTORY_SEPARATOR . '.env.example';
        $env = $dashboard . DIRECTORY_SEPARATOR . '.env';

        // Salin dan ubah izin file .env
        $this->command->copyFile($envExample, $env);
        $this->command->chmodFileCommand($env);

        // Ganti placeholder dengan nilai sebenarnya
        $configTemplate = $this->files->get($envExample);
        $content = str_replace(
            ['{$db_host}', '{$db_username}', '{$db_password}', '{$dirRoot}'],
            [$db_host, $db_username, $db_password, $this->dirRoot],
            $configTemplate
        );
        $this->files->replace($env, $content);

        // Generate key aplikasi
        $this->command->keyGenerateCommand($dashboard);
    }

    /**
     * Konfigurasi file index.php.
     *
     * @param string $dashboard
     * @return void
     */
    private function configIndex($dashboard)
    {
        $indexMaster = $dashboard . DIRECTORY_SEPARATOR . 'master-template' . DIRECTORY_SEPARATOR . 'template-siappakai' . DIRECTORY_SEPARATOR . 'index.php';
        $indexSiapPakai = $dashboard . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'index.php';

        // Hapus dan salin file index.php
        $this->command->removeFile($indexSiapPakai);
        $this->command->copyFile($indexMaster, $indexSiapPakai);
        $this->command->chmodFileCommand($indexSiapPakai);

        // Konfigurasi index.php
        $index = new IndexController();
        $index->indexSiapPakai($this->dirRoot, $indexMaster, $indexSiapPakai);
    }

    /**
     * Konfigurasi file worker.
     *
     * @param string $dashboard
     * @return void
     */
    private function configWorker($dashboard)
    {
        $workerMaster = $dashboard . DIRECTORY_SEPARATOR . 'master-template' . DIRECTORY_SEPARATOR . 'template-siappakai' . DIRECTORY_SEPARATOR . 'siappakai-worker.conf';
        $worker = $dashboard . DIRECTORY_SEPARATOR . 'siappakai-worker.conf';

        // Salin dan ubah izin file worker
        $this->command->copyFile($workerMaster, $worker);
        $this->command->chmodFileCommand($worker);

        // Ganti placeholder dengan nilai sebenarnya
        $configTemplate = $this->files->get($workerMaster);
        $content = str_replace(['{$dirRoot}'], [$this->dirRoot], $configTemplate);
        $this->files->replace($worker, $content);
    }
}