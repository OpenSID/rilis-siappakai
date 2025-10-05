<?php

namespace App\Services;

use Exception;
use App\Models\Pelanggan;
use App\Enums\RepositoryEnum;
use App\Http\Controllers\Helpers\TemaController;

/**
 * Kelas OpensidUpdateService
 *
 * Kelas ini bertanggung jawab untuk mengelola proses pembaruan sistem OpenSID.
 * Kelas ini memperluas MasterOpensidService dan menggunakan beberapa layanan lain
 * seperti FileService, GitService, dan ProcessService.
 */
class OpensidUpdateService extends MasterOpensidService
{
    protected $folderMaster; // Menyimpan path ke folder master OpenSID
    protected $folderOpenSID; // Menyimpan path ke folder OpenSID yang sedang digunakan
    protected $folderMultisite; // Menyimpan path ke folder multisite
    private $temas; // Instance dari TemaController untuk mengelola tema

    /**
     * Konstruktor
     *
     * Menginisialisasi properti dengan nilai dari environment dan konfigurasi aplikasi.
     */
    public function __construct()
    {
        parent::__construct();
        $this->folderMaster = env('ROOT_OPENSID') . 'master-opensid';
        $this->folderMultisite = config('siappakai.root.folder_multisite');
        $this->temas = new TemaController();
    }

    /**
     * Fungsi untuk update opensid
     *
     * @param string $opensid nama folder opensid (umum atau premium)
     *
     * @return void
     *
     * Metode ini melakukan pembaruan OpenSID berdasarkan versi yang tersedia di repository.
     *
     * Langkah-langkah:
     * 1. Cek Versi Beta: Jika server menggunakan versi beta, pembaruan tidak dilakukan.
     * 2. Inisialisasi Layanan: Membuat instance dari FileService dan GitService.
     * 3. Cek Versi: Mendapatkan versi terbaru dari repository dan membandingkannya dengan versi yang ada di server.
     * 4. Pembaruan Bulanan: Jika versi baru adalah rilis bulanan, maka:
     *    - Mengelola folder backup.
     *    - Memperbarui data pelanggan.
     *    - Memproses template index.php untuk setiap pelanggan.
     *    - Menjalankan migrasi data.
     * 5. Pembaruan Revisi: Jika versi baru adalah rilis revisi, maka:
     *    - Mengelola folder revisi.
     *    - Mengkloning repository dengan tag terbaru.
     * 6. Pemasangan Vendor Tema: Memasang vendor tema setelah pembaruan selesai.
     */
    function update($opensid = 'umum')
    {
        if (env('BETA_OPENSID') == 'true') {
            return die("Informasi: server menggunakan versi beta sehingga tidak di update");
        }
        $fileservice = new FileService();
        $this->folderOpenSID = $this->folderMaster . DIRECTORY_SEPARATOR . $opensid;
        ProcessService::gitSafeDirectori($this->folderOpenSID);

        // opensid menggunakan versi yang rilis untuk siappakai maupun kominfo
        $gitservice = new GitService();
        $repoEnum = RepositoryEnum::fromFolderName(strtolower($opensid));
        $versionOpensidTag = $gitservice->getLastRelease($repoEnum)['tag_name'];
        $versionGit = preg_replace('/[^0-9]/', '', $versionOpensidTag);
        $versionServer = preg_replace('/[^0-9]/', '', $this->cekVersiServer($opensid));

        $revVersion = substr($versionGit, 4, 2);
        if (substr($versionGit, 0, 6) > substr($versionServer, 0, 6)) {
            // rilis bulanan

            if ($revVersion == "00") {
                if ($opensid == "umum") {
                    $gitservice->cloneWithTag($repoEnum, $this->folderMaster, $versionOpensidTag);
                } else {
                    try {
                        for ($i = 5; $i >= 1; $i--) {
                            $fileservice->renameFolder($this->folderOpenSID . "_" . sprintf('%02d',$i), $this->folderOpenSID . "_" . sprintf('%02d',($i+1)));
                        }
                        $fileservice->deleteFolder($this->folderOpenSID . "_06");
                        //  lakukan penghapusan opensid versi perbaikan
                        $fileservice->deleteFoldersByPrefix($this->folderMaster, 'premium_rev');

                        $gitservice->cloneWithTag($repoEnum, $this->folderMaster, $versionOpensidTag);
                    } catch (Exception $e) {
                        for ($i = 1; $i <= 6; $i++) {
                            $nomorRev = sprintf('%02d', $i);
                            $nextNomorRev = sprintf('%02d', $i + 1);
                            $fileservice->renameFolder($this->folderOpenSID . "_" . "$nextNomorRev", $this->folderOpenSID . "_" . "$nomorRev");
                        }
                    }
                }

                // lakukan update data pelanggan
                // jika sudah melebihi batas maka akan tetap ke versi sebelumnya
                $this->updatePelanggan($opensid, $versionOpensidTag);
            } else { // rilis perbaikan revisi
                try {
                    for ($i = 5; $i >= 1; $i--) {
                        $nomorRev = sprintf('%02d', $i);
                        $nextNomorRev = sprintf('%02d', $i + 1);
                        $fileservice->renameFolder($this->folderOpenSID . "_rev" . "$nomorRev", $this->folderOpenSID . "_rev" . "$nextNomorRev");
                    }

                    $gitservice->cloneWithTag($repoEnum, $this->folderMaster, $versionOpensidTag, 'premium_rev01');

                    $this->updatePelanggan($opensid, $versionOpensidTag);
                } catch (Exception $e) {
                    for ($i = 1; $i <= 6; $i++) {
                        $nomorRev = sprintf('%02d', $i);
                        $nextNomorRev = sprintf('%02d', $i + 1);
                        $fileservice->renameFolder($this->folderOpenSID . "_rev" . "$nextNomorRev", $this->folderOpenSID . "_rev" . "$nomorRev");
                    }
                }
            }

            // jika install update selesai
            // update httacess
            $this->tanganiHtaccess($this->folderOpenSID);

            $this->temas->pemasanganVendorTema();
        }
    }


    function updatePelanggan($opensid = 'umum', $versionOpensidTag)
    {
        $fileservice = new FileService();
        $templateIndexphp = RepositoryEnum::getFolderTemplate($opensid) . DIRECTORY_SEPARATOR . 'index.php';
        $costumers = Pelanggan::where('status_langganan_saas', 1)->get();
        foreach ($costumers as $costumer) {
            $langganan = PelangganService::langganan($costumer);
            PelangganService::updatePelanggan(['langganan_opensid' => $langganan, 'versi_opensid' => $versionOpensidTag], $costumer->id);
            $folderOpensid = $this->folderMultisite . $costumer->kode_desa_without_dot;

            // hapus symlink
            $fileservice->deleteAllSymlinks($folderOpensid);

            $OpensidIndexPhp = $folderOpensid . DIRECTORY_SEPARATOR . 'index.php';
            $replace = [
                '{$opensidFolder}' => $this->folderMaster . DIRECTORY_SEPARATOR . $langganan,
                '{$symlinkDomain}' => $this->folderMultisite . $costumer->kode_desa_without_dot
            ];
            $fileservice->processTemplate($templateIndexphp, $OpensidIndexPhp,  $replace);

            // migrasikan opensid premium
            $command = ['php', 'artisan', 'siappakai:migrate', "--path={$folderOpensid}"];
            ProcessService::runProcess($command, base_path(), "Migrasi data pelanggan {$costumer->kode_desa_without_dot} ke versi {$langganan}...\n");
            ProcessService::aturKepemilikanDirektori($folderOpensid);

            //copy paste folder assets
            $this->copyAssets($fileservice, $folderOpensid);

            $this->setPermisionFolderOpensid($this->folderOpenSID);
        }
    }

    /**
     * Mengatur permission folder opensid
     *
     * @param string $directory Path folder opensid
     *
     * @return void
     */
    public static function setPermisionFolderOpensid($directory)
    {
        ProcessService::aturPermision($directory . DIRECTORY_SEPARATOR . 'desa');
        ProcessService::aturPermision($directory . DIRECTORY_SEPARATOR . 'storage');
        ProcessService::aturPermision($directory . DIRECTORY_SEPARATOR . 'backup_inkremental');
    }

    function copyAssets($fileservice, $targetFolder)
    {
        $direktoriTemplateAsset = $this->folderOpenSID . DIRECTORY_SEPARATOR . 'assets';
        $targetFolder = $targetFolder . DIRECTORY_SEPARATOR . 'assets';

        // Periksa apakah assets adalah symlink kemudian hapus symlink
        if (is_link($targetFolder)) {
            unlink($targetFolder);
        }

        // Salin semua isi folder sumber ke folder target
        $fileservice->replaceFolderContents($direktoriTemplateAsset, $targetFolder);
    }
}
