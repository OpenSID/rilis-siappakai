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
            "key" => "server_panel",
            "keterangan" => "Isi server panel yang akan digunakan",
            "value" => "1",
            "jenis" => "option",
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
