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
            "key" => "donotusecolumnstatistics", 
            "keterangan" => "Pilih akan menggunakan function donotusecolumnstatistics atau tidak", 
            "value" => "1", 
            "jenis" => "option", 
            "kategori" => "",
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
