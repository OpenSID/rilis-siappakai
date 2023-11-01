<?php

use App\Models\Aplikasi;
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
        $data = [
            "key" => "host_backup_server", 
            "keterangan" => "IP server untuk menyimpan data backup (misalkan xxx.xxx.xxx.xx / domain.id)", 
            "jenis" => "text", 
            "kategori" => "pengganti_sebutan",
            "script" => ""
        ];

        Aplikasi::create($data);
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
