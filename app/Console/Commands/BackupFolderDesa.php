<?php

namespace App\Console\Commands;

use App\Http\Controllers\Helpers\AttributeSiapPakaiController;
use App\Http\Controllers\Helpers\CommandController;
use App\Models\Aplikasi;
use App\Models\Pelanggan;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;

class BackupFolderDesa extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'siappakai:backup-folder-desa';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Melakukan backup folder desa OpenSID (dapat dilakukan melalui cronjob jika ada rilis terbaru)';

    private $att;
    private $command;
    private $folder_backup;
    private $folder_desa;
    private $pelanggans;

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
        $this->folder_backup = siappakai_storage() . DIRECTORY_SEPARATOR . 'backup';
        $this->folder_desa = $this->folder_backup . DIRECTORY_SEPARATOR . 'folder-desa';
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->pelanggans = Pelanggan::get();
        $this->backupFolderDesa();
        $this->command->notifMessage('backup folder desa');
    }

    private function backupFolderDesa()
    {
        $token = $this->att->tokenGithubInfo();
        $path_root = dirname(base_path(), 1);
        $premium_folder = $path_root . DIRECTORY_SEPARATOR . 'master-opensid' . DIRECTORY_SEPARATOR . 'premium';

        // Mendapatkan versi terbaru dari GitHub
        $response = Http::withHeaders([
            'Accept' => 'application/vnd.github.v3+json',
            'Authorization' => "token {$token}"
        ])->get('https://api.github.com/repos/OpenSID/rilis-premium/releases/latest')->throw()->json();
        $version_git = preg_replace('/[^0-9]/', '', $response['tag_name']);

        $content_versi = 0;
        if (File::exists($premium_folder)) {
            $tags_server = 'cd ' . $premium_folder . DIRECTORY_SEPARATOR . ' && sudo git describe --tags';
            $content_versi = exec($tags_server);
        }
        $version_server = preg_replace('/[^0-9]/', '', $content_versi);

        $newVersion = substr($version_git, 4, 2);

        // Logika untuk menentukan apakah perlu backup
        if ($this->isBackupRequired($newVersion, $version_git, $version_server)) {
            $this->pelangganSiapPakai();
        }
    }

    private function isBackupRequired($newVersion, $version_git, $version_server)
    {
        if ($newVersion == "00" && (substr($version_git, 0, 4) > substr($version_server, 0, 4))) {
            return true; // Backup sebelum rilis tanggal 1
        } else if ($newVersion > 0 && (substr($version_git, 0, 6) > substr($version_server, 0, 6))) {
            return true; // Backup sebelum rilis revisi
        } else if (cek_tgl_akhir_backup($this->pelanggans) >= Aplikasi::pengaturan_aplikasi()['waktu_backup'] && rclone_syncs_storage() == true) {
            return true; // Backup setiap 2 hari sekali atau sesuai pengaturan
        }
        return false;
    }

    private function pelangganSiapPakai()
    {
        $this->folderBackup();

        foreach ($this->pelanggans as $item) {
            $this->backupOpensid($item);
            $this->backupPbb($item);

            // Update tanggal akhir backup
            $update_backup = Pelanggan::find($item->id);
            $update_backup->tgl_akhir_backup = date('Y-m-d');
            $update_backup->save();
        }
    }

    private function folderBackup()
    {
        // Hapus folder desa dari backup sebelumnya
        $this->command->removeDirectory($this->folder_desa);

        // Buat folder backup dan folder-desa
        $this->command->makeDirectory($this->folder_backup);
        $this->command->makeDirectory($this->folder_desa);
    }

    private function backupOpensid($item)
    {
        $kode_desa = str_replace('.', '', $item->kode_desa);
        $folderdesa_from = $this->att->getMultisiteFolder() . $kode_desa . DIRECTORY_SEPARATOR . 'desa';
        $filezip_from = $this->att->getMultisiteFolder() . $kode_desa . DIRECTORY_SEPARATOR . 'desa_' . $kode_desa . '.zip';
        $filezip_to = $this->folder_desa . DIRECTORY_SEPARATOR . 'desa_' . $kode_desa. '.zip';

        // melakukan compress ke zip file
        $this->command->zipDirectory($filezip_from, $folderdesa_from);

        if (file_exists($this->folder_desa)) {
            // melakukan pemindahan zip file ke storage
            $this->command->moveFile($filezip_from, $filezip_to);
            $this->command->chownCommand($this->folder_desa);
        }
    }

    private function backupPbb($item)
    {
        $kode_desa = str_replace('.', '', $item->kode_desa);
        $folderdesa_from = $this->att->getMultisiteFolder() . $kode_desa . DIRECTORY_SEPARATOR . 'pbb-app' . DIRECTORY_SEPARATOR . 'storage';
        $filezip_from = $this->att->getMultisiteFolder() . $kode_desa . DIRECTORY_SEPARATOR . 'pbb-app' . DIRECTORY_SEPARATOR . 'storage_' . $kode_desa . '.zip';
        $filezip_to = $this->folder_desa . DIRECTORY_SEPARATOR . 'storage_' . $kode_desa. '.zip';

        // melakukan compress ke zip file
        $this->command->zipDirectory($filezip_from, $folderdesa_from);

        if (file_exists($this->folder_desa)) {
            // melakukan pemindahan zip file ke storage
            $this->command->moveFile($filezip_from, $filezip_to);
            $this->command->chownCommand($this->folder_desa);
        }
    }

}
