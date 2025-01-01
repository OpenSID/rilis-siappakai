<?php

use App\Services\FileService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $root = config('siappakai.root.folder');
        $multisiteFolder = config('siappakai.root.folder_multisite');
        $fileservice = new FileService();

        // lakukan hapus existing symlink


        // ambil data pelanggan
        $costumers = DB::table('pelanggan')->get();
        $templateFavicon = $root . 'master-template'. DIRECTORY_SEPARATOR . 'template-opensid'. DIRECTORY_SEPARATOR . 'desa'. DIRECTORY_SEPARATOR . 'logo'. DIRECTORY_SEPARATOR . 'favicon.ico';
        foreach ($costumers as  $costumer) {
            $kode = str_replace('.', '', $costumer->kode_desa);
            $folderOpensid = $multisiteFolder . $kode;

            // hapus symlink di root folder opensid
            $fileservice->deleteSymlinks($folderOpensid);

            // hapus symlink di folder desa opensid
            $fileservice->deleteSymlinks($folderOpensid. DIRECTORY_SEPARATOR . 'desa');

            $favicon =  $folderOpensid. DIRECTORY_SEPARATOR . 'desa' .DIRECTORY_SEPARATOR . 'logo'. DIRECTORY_SEPARATOR . 'favicon.ico';
            if (file_exists($folderOpensid) && !file_exists($favicon)) {
                File::copy($templateFavicon,$favicon);
            }
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
};
