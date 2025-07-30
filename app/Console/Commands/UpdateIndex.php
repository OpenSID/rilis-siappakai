<?php

namespace App\Console\Commands;

use App\Http\Controllers\Helpers\AttributeSiapPakaiController;
use App\Http\Controllers\Helpers\CommandController;
use App\Http\Controllers\Helpers\IndexController;
use App\Models\Pelanggan;
use App\Services\ProcessService;
use Illuminate\Console\Command;

class UpdateIndex extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'siappakai:update-index';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Perbarui index aplikasi pada masing-masing aplikasi OpenSID, API dan PBB';

    private $att;
    private $comm;
    private $filesIndex;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->att = new AttributeSiapPakaiController();
        $this->comm = new CommandController();
        $this->filesIndex = new IndexController();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        //index siappakai
        $this->setIndexSiapPakai();

        $pelanggans = Pelanggan::get();

        foreach ($pelanggans as $item) {
            $kodedesa = str_replace('.', '', $item->kode_desa);
            $langganan_opensid = $item->langganan_opensid;

            // folder
            $this->att->setSiteFolderOpensid($this->att->getMultisiteFolder() . $kodedesa);
            $this->att->setSiteFolderApi($this->att->getMultisiteFolder() . $kodedesa . DIRECTORY_SEPARATOR . 'api-app');
            $this->att->setSiteFolderPbb($this->att->getMultisiteFolder() . $kodedesa . DIRECTORY_SEPARATOR . 'pbb-app');

            // index.php
            $this->att->setIndexDesa($this->att->getSiteFolderOpensid() . DIRECTORY_SEPARATOR . 'index.php');
            $this->att->setIndexApi($this->att->getSiteFolderApi() . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'index.php');
            $this->att->setIndexPbb($this->att->getSiteFolderPbb() . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'index.php');

            if (file_exists($this->att->getSiteFolderOpensid())) {
                // opensid
                $this->setIndexOpensid($langganan_opensid);

                // opensid api
                $this->setIndexApi($langganan_opensid);

                // pbb
                $this->setIndexPbb($langganan_opensid);
            }
        }

        $this->comm->notifMessage('update index');
        ProcessService::aturKepemilikanDirektori($this->att->getRootFolder() . 'multisite');
    }

    private function setIndexSiapPakai()
    {
        if (file_exists($this->att->getSiteFolder())) {
            $indexMaster = $this->att->getSiteFolder() . DIRECTORY_SEPARATOR . 'master-template' . DIRECTORY_SEPARATOR . 'template-siappakai' . DIRECTORY_SEPARATOR. 'index.php';
            $indexSiapPakai = $this->att->getSiteFolder() . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'index.php';

            // hapus file index.php
            $this->comm->removeFile($indexSiapPakai);

            // salin file index.php
            $this->comm->copyFile($indexMaster, $indexSiapPakai);

            // permission
            $this->comm->chmodFileCommand($indexSiapPakai);

            // ubah symlink di file index
            if (file_exists($indexSiapPakai)) {
                $this->filesIndex->indexSiapPakai(
                    $this->att->getRootFolder(),
                    $indexMaster,
                    $indexSiapPakai
                );
            }

            // buat symlink dengan menjalankan file index.php
            $this->comm->indexCommand($this->att->getSiteFolder() . DIRECTORY_SEPARATOR . 'public'); // index.php
        }
    }

    private function setIndexOpensid($langganan_opensid)
    {
        // hapus file index.php
        $this->comm->removeFile($this->att->getIndexDesa());

        // salin file index.php
        $this->comm->copyFile($this->att->getIndexTemplate(), $this->att->getIndexDesa());

        // permission
        $this->comm->chmodFileCommand($this->att->getIndexDesa());

        // unlink
        $this->comm->unlinkCommandOpenSid($this->att->getSiteFolderOpensid());

        // ubah symlink di file index
        if (file_exists($this->att->getIndexDesa())) {
            $this->filesIndex->indexPhpOpensid(
                $this->att->getRootFolder() . 'master-opensid' . DIRECTORY_SEPARATOR . $langganan_opensid,
                $this->att->getSiteFolderOpensid(),
                $this->att->getIndexTemplate(),
                $this->att->getIndexDesa()
            );
        }

        // buat symlink dengan menjalankan file index.php
        $this->comm->migratePremium($this->att->getSiteFolderOpensid());
    }

    private function setIndexApi($langganan_opensid)
    {
        if (file_exists($this->att->getSiteFolderApi())) {
            // hapus file index.php
            $this->comm->removeFile($this->att->getIndexApi());

            // salin file index.php
            $this->comm->copyFile($this->att->getIndexTemplateApi(), $this->att->getIndexApi());

            // permission
            $this->comm->chmodFileCommand($this->att->getIndexApi());

            // unlink
            $this->comm->unlinkCommandAppLaravel($this->att->getSiteFolderApi());

            // ubah symlink di file index
            if (file_exists($this->att->getIndexApi())) {
                $this->filesIndex->indexPhpApi(
                    $this->filesIndex->langganan_opensid($langganan_opensid, 'opensid-api'),  //apiFolderFrom,
                    $this->att->getRootFolder() . 'master-api' . DIRECTORY_SEPARATOR,                   //apiFolderFrom,
                    $this->att->getSiteFolderApi(),                                                                      //apiFolderTo,
                    $this->att->getIndexTemplateApi(),
                    $this->att->getIndexApi()
                );
            }

            // buat symlink dengan menjalankan file index.php
            $this->comm->indexCommand($this->att->getSiteFolderApi() . DIRECTORY_SEPARATOR . 'public'); // index.php
        }
    }

    private function setIndexPbb($langganan_opensid)
    {
        if (file_exists($this->att->getSiteFolderPbb())) {
            // hapus file index.php
            $this->comm->removeFile($this->att->getIndexPbb());

            // salin file index.php
            $this->comm->copyFile($this->att->getIndexTemplatePbb(), $this->att->getIndexPbb());

            // buat directory import
            $this->comm->makeDirectory($this->att->getSiteFolderPbb() . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'import');

            // permission
            $this->comm->chmodFileCommand($this->att->getIndexPbb());
            $this->comm->chmodDirectoryCommand($this->att->getSiteFolderPbb() . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'import');

            // unlink
            $this->comm->unlinkCommandAppLaravel($this->att->getSiteFolderPbb());
            $this->comm->unlinkCommandAppLaravelPublic($this->att->getSiteFolderPbb());

            // ubah symlink di file index
            if (file_exists($this->att->getIndexPbb())) {
                $this->filesIndex->indexPhpPbb(
                    $this->filesIndex->langganan_opensid($langganan_opensid, 'pbb_desa'),  //pbbFolder
                    $this->att->getRootFolder() . 'master-pbb' . DIRECTORY_SEPARATOR,      //pbbFolderFrom
                    $this->att->getSiteFolderPbb(),                                                              //pbbFolderTo
                    $this->att->getIndexTemplatePbb(),
                    $this->att->getIndexPbb()
                );
            }

            // buat symlink dengan menjalankan file index.php
            $this->comm->indexCommand($this->att->getSiteFolderPbb() . DIRECTORY_SEPARATOR . 'public'); // index.php
        }
    }
}
