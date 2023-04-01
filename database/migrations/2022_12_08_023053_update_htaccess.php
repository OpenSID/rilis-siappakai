<?php

use Illuminate\Support\Facades\File;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Database\Migrations\Migration;

class UpdateHtaccess extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $root_folder = env('ROOT_OPENSID');
        // folder multisite
        $multisite = env('MULTISITE_OPENSID');
        $dirs = File::directories($multisite);
        $files = new Filesystem();
        foreach ($dirs as $dir) {
            $htopensid = $dir . DIRECTORY_SEPARATOR . '.htaccess';
            $htapi = $dir . DIRECTORY_SEPARATOR . 'api-app' . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . '.htaccess';
            $htpbb = $dir . DIRECTORY_SEPARATOR . 'pbb-app' . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . '.htaccess';

            if (file_exists($htopensid)) {
                File::delete($htopensid); // hapus .httacess opensid
            } else {
                exec('sudo ln -s ' . $root_folder . 'master-template' . DIRECTORY_SEPARATOR . 'template-desa' . DIRECTORY_SEPARATOR . '.htaccess ' . $htopensid);
            }

            if (file_exists($htapi)) {
                File::delete($htapi); // hapus .httacess api
            } else {
                exec('sudo ln -s ' . $root_folder . 'master-template' . DIRECTORY_SEPARATOR . 'template-api' . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . '.htaccess ' . $htapi);
            }

            if (file_exists($htpbb)) {
                File::delete($htpbb); // hapus .httacess pbb
            } else {
                exec('sudo ln -s ' . $root_folder . 'master-template' . DIRECTORY_SEPARATOR . 'template-pbb' . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . '.htaccess ' . $htpbb);
            }

            // perbarui index.php opensid
            $file_index = $dir . DIRECTORY_SEPARATOR . 'index.php';
            $index = file_get_contents($file_index);
            $content = str_replace("symlink(SYMLINK_DOMAIN . DIRECTORY_SEPARATOR . 'pbb-app' . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . '.htaccess', '.htaccess')", "symlink(OPENSID_FOLDER . DIRECTORY_SEPARATOR . '.htaccess', '.htaccess')", $index);
            file_put_contents($file_index, $content);
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
}
