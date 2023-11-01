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
            "key" => "waktu_backup",
            "keterangan" => "Isi berapa hari sekali untuk melakukan backup",
            "value" => "2",
            "jenis" => "number",
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
