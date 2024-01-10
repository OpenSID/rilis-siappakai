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
            "key" => "multiphp",
            "keterangan" => "Pilih akan menggunakan multiphp atau tidak",
            "value" => "2",
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
