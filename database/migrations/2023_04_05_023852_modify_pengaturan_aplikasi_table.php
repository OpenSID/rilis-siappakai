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
        $aplikasi = array(
            ["key" => "pengaturan_domain", "value" => "apache", "keterangan" => "Digunakan untuk pengaturan domain melalui apache atau proxy.", "jenis" => "option", "kategori" => "pengganti_sebutan", "script" => ""],
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
