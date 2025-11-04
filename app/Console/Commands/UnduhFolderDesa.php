<?php

namespace App\Console\Commands;

use App\Http\Controllers\Helpers\AttributeSiapPakaiController;
use App\Http\Controllers\Helpers\CommandController;
use App\Services\ProcessService;
use Illuminate\Console\Command;

class UnduhFolderDesa extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'siappakai:unduh-folder-desa {--directory_from=} {--file=} {--directory=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Mengunduh folder desa pada desa tertentu.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $directory_from = $this->option('directory_from');
        $file = $this->option('file');
        $directory = $this->option('directory');

        $command = new CommandController();

        if (!file_exists($directory)) {
            $command->copyDirectory($directory_from, $directory);
            $command->chownCommand($directory);
        }

        if (!file_exists($file)) {
            $command->zipDirectory($file, $directory);
            $command->chownFileCommand($file);
            $command->chmodFileCommand($file);
        }

        // perintah hapus file setelah diunduh
        if ($file != '' && $directory_from == '' && $directory == '') {
            $command->removeFile($file);
        }

        ProcessService::aturKepemilikanDirektori(config('siappakai.root.folder'));
    }
}
