<?php

use Illuminate\Support\Facades\File;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Database\Migrations\Migration;

class UpdateHtaccess extends Migration
{
    public $root_default;

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $this->root_default = path_root_siappakai('root_vps');
        $root_folder = env('ROOT_OPENSID');
        $multisite = env('MULTISITE_OPENSID');
        $dirs = File::directories($multisite);
        $files = new Filesystem();

        if (file_exists($multisite)) {
            foreach ($dirs as $dir) {
                $master_template = $root_folder . 'master-template' . DIRECTORY_SEPARATOR;

                if (file_exists($master_template)) {
                    $this->updateHtaccess($root_folder, $master_template, $dir);
                }

                // perbarui index.php opensid
                $file_index = $dir . DIRECTORY_SEPARATOR . 'index.php';
                if (file_exists($file_index)) {
                    $index = file_get_contents($file_index);
                    $target = "symlink(SYMLINK_DOMAIN . DIRECTORY_SEPARATOR . 'pbb-app' . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . '.htaccess', '.htaccess')";
                    $replacement = "symlink(OPENSID_FOLDER . DIRECTORY_SEPARATOR . '.htaccess', '.htaccess')";

                    if (str_contains($index, $target)) {
                        $content = str_replace($target, $replacement, $index);
                        // Gunakan tee karena file kemungkinan dimiliki oleh www-data
                        $command = "echo " . escapeshellarg($content) . " | tee " . escapeshellarg($file_index) . " > /dev/null";
                        exec($command);
                    }
                }
            }
        }
    }

    public function updateHtaccess($root_folder, $master_template, $dir)
    {
        $htopensid = $dir . DIRECTORY_SEPARATOR . '.htaccess';
        $htapi = $dir . DIRECTORY_SEPARATOR . 'api-app' . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . '.htaccess';
        $htpbb = $dir . DIRECTORY_SEPARATOR . 'pbb-app' . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . '.htaccess';

        if (file_exists($htopensid)) {
            File::delete($htopensid); // hapus .httacess opensid
        } else {
            $root_folder == $this->root_default ? exec('ln -s ' . $master_template . 'template-opensid' . DIRECTORY_SEPARATOR . '.htaccess ' . $htopensid) : '';
        }

        if (file_exists($htapi)) {
            File::delete($htapi); // hapus .httacess api
        } else {
            $root_folder == $this->root_default ? exec('ln -s ' . $master_template . 'template-api' . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . '.htaccess ' . $htapi) : '';
        }

        if (file_exists($htpbb)) {
            File::delete($htpbb); // hapus .httacess pbb
        } else {
            $root_folder == $this->root_default ? exec('ln -s ' . $master_template . 'template-pbb' . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . '.htaccess ' . $htpbb) : '';
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
