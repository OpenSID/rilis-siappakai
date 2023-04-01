<?php

use App\Models\Aplikasi;
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
        $aplikasi = array(
            ["key" => "tema_bawaan", "value" => "bima", "keterangan" => "Tema bawaan yang akan digunakan pelanggan OpenSID", "jenis" => "option", "kategori" => "pengganti_sebutan", "script" => ""],
        );

        foreach ($aplikasi as $item) {
            Aplikasi::create($item);
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
