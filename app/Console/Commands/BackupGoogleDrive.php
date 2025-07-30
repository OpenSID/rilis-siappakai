<?php

namespace App\Console\Commands;

use App\Http\Controllers\Helpers\AttributeSiapPakaiController;
use App\Http\Controllers\Helpers\RemoteController;
use App\Models\Aplikasi;
use App\Models\Pelanggan;
use Illuminate\Console\Command;

class BackupGoogleDrive extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'siappakai:backup-google-drive';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Melakukan backup database dan folder desa ke google drive';

    private $att;
    private $remote;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->att = new AttributeSiapPakaiController();
        $this->remote = new RemoteController();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        // tipe pencadangan, misalkan: drive, sftp, dll
        $storage_type = 'drive';

        // nama remote yang dibuat melalui rclone config
        $remote_name = 'backup-drive';

        // data pelanggan
        $pelanggans = Pelanggan::get();

        if ($this->remote->checkBackupOption($storage_type)) {
            // hapus data yang paling lama dengan batas maksimal yang ditentukan
            $this->remote->removeBackupCloudStorage($remote_name, $pelanggans, null);

            // proses backup
            $this->remote->backupToCloudStorage($storage_type, $remote_name, $pelanggans, null);

            // proses pengecekan antara folder root dengan cloud
            $this->remote->compareBackupStorageAndCloud($remote_name, null);
        }
    }
}
