<?php

namespace App\Console\Commands;

use App\Models\Aplikasi;
use App\Services\GitService;
use App\Enums\RepositoryEnum;
use App\Jobs\InstallModuleJob;
use Illuminate\Console\Command;
use App\Services\ProcessService;
use App\Services\AplikasiService;
use App\Services\DatabaseService;
use Illuminate\Support\Facades\DB;
use App\Services\RepositoryService;
use Symfony\Component\Process\Process;
use App\Services\OpensidInstallerService;
use App\Http\Controllers\Helpers\EnvController;
use App\Http\Controllers\Helpers\CommandController;
use App\Http\Controllers\Helpers\KoneksiController;
use App\Http\Controllers\Helpers\AttributeSiapPakaiController;

class InstallMaster extends Command
{
    protected $signature = 'siappakai:install-master {--kode_desa=} {--token_premium=}';
    protected $description = 'Instal Master Aplikasi : OpenSID Premium, OpenSID API, dan Aplikasi PBB ';

    private $att;
    private $comm;
    private $filesEnv;
    private $koneksi;
    private $ip_source_code;
    private $multiphp;

    public function __construct(
        AttributeSiapPakaiController $att,
        CommandController $comm,
        EnvController $filesEnv,
        KoneksiController $koneksi
    ) {
        parent::__construct();
        $this->att = $att;
        $this->comm = $comm;
        $this->filesEnv = $filesEnv;
        $this->koneksi = $koneksi;
    }



    public function handle(AplikasiService $aplikasiService): int
    {
        $opensid_installer_service = new OpensidInstallerService(true);

        $siappakai_opensid = "siappakai_opensid";
        $kodedesa = $this->option('kode_desa');
        $kode_kecamatan = implode('.', array_slice(explode('.', $kodedesa), 0, 3));
        $token_premium = $this->option('token_premium');
        $this->ip_source_code = $aplikasiService->pengaturanApikasi('ip_source_code');

        if ($this->att->getSiteFolder()) {
            // install tema pro
            $command = ['php', 'artisan', 'siappakai:install-master-tema'];
            ProcessService::runProcess($command, $this->att->getSiteFolder());

            // instal opensid premium
            $opensid_installer_service->instalOpensid('Premium', $kodedesa, $token_premium);

            // instal opensid Umum
            $opensid_installer_service->instalOpensid('Umum', $kodedesa);

            $this->installMasterApi($siappakai_opensid, $kodedesa, $token_premium);
            $this->installMasterPbb($siappakai_opensid, $kodedesa, $token_premium);
            // install tags 5 bulan sebelumnya
            echo "install tags 5 bulan sebelumnya\n";
            $commandInstallTag = ['php', 'artisan', 'siappakai:install-tags-version'];
            ProcessService::runProcess($commandInstallTag, $this->att->getSiteFolder());
            echo "selesai install tags 5 bulan sebelumnya\n";

            // install opendk
            $this->installMasterOpendk($siappakai_opensid, $kode_kecamatan);
        }

        $this->comm->indexCommand($this->att->getSiteFolder() . DIRECTORY_SEPARATOR . 'public');
        $this->comm->commitCommand('install master template', $this->att->getSiteFolder());

        InstallModuleJob::dispatch();

        echo "Install Master Selesai\n";
        ProcessService::aturKepemilikanDirektori(config('siappakai.root.folder'));
        return 0;
    }

    public function installMasterApi(string $siappakai_opensid, string $kodedesa, string $token_premium): void
    {
        $folderMaster = $this->att->getRootFolder() . 'master-api';
        $folderSite = $folderMaster . DIRECTORY_SEPARATOR . 'opensid-api';

        if ($folderMaster) {
            echo "Proses Git clone API\n";
            $command = ['git', 'clone', 'https://github.com/OpenSID/rilis-opensid-api', 'opensid-api'];
            ProcessService::runProcess($command, $folderMaster);
            //buat user database untuk api
            $databaseService = new DatabaseService($this->att->getHost());
            $databaseService->createUser('siappakai_opensid_premium');

            $this->filesEnv->envApi(
                $this->att->getHost(),
                $this->att->getTemplateFolderApi(),
                $this->att->getServerLayanan(),
                $folderSite,
                $kodedesa,
                'siappakai_opensid_premium',
                $token_premium,
                null,
                null,
                null,
                null,
                '/api',
                $this->att->getFtpUser(),
                $this->att->getFtpPass()
            );

            exec('sudo git config --global --add safe.directory ' . $folderSite);
        }

        // jika menggunakan multiphp hapus file htaccess dan buat symlink
        $htaccess = $folderSite . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . '.htaccess';
        $htaccess_from = $this->att->getRootFolder() . 'master-template' . DIRECTORY_SEPARATOR . 'template-api' . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . '.htaccess';
        $this->comm->setHtaccessMaster($this->multiphp, $htaccess_from, $htaccess);

        $this->comm->chownCommand($folderSite);
        $this->comm->composerInstall($folderSite);
        $this->comm->keyGenerateCommand($folderSite);
        $this->comm->keyJwtSecretCommand($folderSite);
        $this->dataStorage($folderSite);
    }

    private function dataStorage(string $folderSite): void
    {
        $data = $folderSite . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'framework' . DIRECTORY_SEPARATOR . 'cache' . DIRECTORY_SEPARATOR . 'data';
        $this->comm->removeDirectory($data);
    }

    public function installMasterPbb(string $siappakai_pbb, string $kodedesa, string $token_premium): void
    {
        $folderMaster = $this->att->getRootFolder() . 'master-pbb';
        $folderSite = $folderMaster . DIRECTORY_SEPARATOR . 'pbb_desa';

        if ($folderMaster) {
            GitService::cloneRepository(RepositoryEnum::PBB, $folderMaster);
            $this->createDatabasePbb($siappakai_pbb);

            $this->filesEnv->envPbb(
                $this->att->getHost(),
                $this->att->getTemplateFolderPbb(),
                $this->att->getServerLayanan(),
                $folderSite,
                $kodedesa,
                $siappakai_pbb,
                '/pbb',
                $token_premium
            );

            exec('sudo git config --global --add safe.directory ' . $folderSite);
            $this->comm->removeFile($folderSite . DIRECTORY_SEPARATOR . 'composer.lock');
        }

        // jika menggunakan multiphp hapus file htaccess dan buat symlink
        $htaccess = $folderSite . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . '.htaccess';
        $htaccess_from = $this->att->getRootFolder() . 'master-template' . DIRECTORY_SEPARATOR . 'template-pbb' . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . '.htaccess';
        $this->comm->setHtaccessMaster($this->multiphp, $htaccess_from, $htaccess);

        $this->comm->chownCommand($folderSite);
        $this->comm->composerInstall($folderSite);
        $this->comm->keyGenerateCommand($folderSite);
        $this->comm->migrateSeedForce($folderSite);
        $this->comm->storageLink($folderSite);
    }

    private function createDatabasePbb(string $siappakai_pbb): void
    {
        $databaseService = new DatabaseService($this->ip_source_code);
        $databaseService->createDatabase($siappakai_pbb . "_pbb");
        $databaseService->createUser($siappakai_pbb);
    }

    public function installMasterOpendk(string $kode_kecamatan): void
    {
        $folderMaster = config('siappakai.root.folder') . 'master-opendk';
        $folderSite = $folderMaster . DIRECTORY_SEPARATOR . 'opendk';

        if ($folderMaster) {
            GitService::cloneRepository(RepositoryEnum::OPENDK, $folderMaster);
            $this->createDatabaseOpendk('opendk');

            $this->filesEnv->envOpendk(
                $this->att->getHost(),
                $this->att->getTemplateFolderOpendk(),
                $folderSite,
                $kode_kecamatan,
                '/opendk'
            );

            exec('sudo git config --global --add safe.directory ' . $folderSite);
            $this->comm->removeFile($folderSite . DIRECTORY_SEPARATOR . 'composer.lock');
        }

        // jika menggunakan multiphp hapus file htaccess dan buat symlink
        $htaccess = $folderSite . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . '.htaccess';
        $htaccess_from = $this->att->getRootFolder() . 'master-template' . DIRECTORY_SEPARATOR . 'template-opendk' . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . '.htaccess';
        $this->comm->setHtaccessMaster($this->multiphp, $htaccess_from, $htaccess);

        $this->comm->chownCommand($folderSite);
        $this->comm->composerInstall($folderSite);
        $this->comm->keyGenerateCommand($folderSite);
        $this->comm->migrateSeedForce($folderSite);
        $this->comm->storageLink($folderSite);
    }

    private function createDatabaseOpendk(string $opendk): void
    {
        if (!$this->koneksi->cekDatabasePbb($opendk)) {
            $namadbuser = $opendk;
            echo 'Berhasil buat database db_' . $namadbuser . "\n";

            DB::statement("CREATE DATABASE db_" . $namadbuser);
            DB::statement("GRANT ALL PRIVILEGES ON db_$namadbuser.* TO 'user_$opendk'@'$this->ip_source_code' WITH GRANT OPTION");
            DB::statement("FLUSH PRIVILEGES");
        }
    }
}
