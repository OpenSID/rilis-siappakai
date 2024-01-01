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
            "key" => "permission",
            "keterangan" => "Isi jenis permission yang akan digunakan, contoh: www-data",
            "value" => "www-data",
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
