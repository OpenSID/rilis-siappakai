<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $folder_root = env('ROOT_OPENSID', '/var/www/html/');
        $folder_saas = $folder_root .'public_html';

        unlinkSymlink($folder_saas);
        exec('php '.$folder_root.'dasbor-siappakai/public/index.php');
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
