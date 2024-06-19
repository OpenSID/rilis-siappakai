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
        $data = array(
            [
            "key" => "aapanel_key",
            "keterangan" => "Isi API secret key aaPanel",
            "value" => "",
            "jenis" => "text",
            "kategori" => "pengaturan_aapanel",
            "script" => ""],

            [
                "key" => "aapanel_ip",
                "keterangan" => "Isi IP Whitelist aaPanel",
                "value" => "",
                "jenis" => "text",
                "kategori" => "pengaturan_aapanel",
                "script" => ""],

            [
                "key" => "aapanel_php",
                "keterangan" => "Isi Versi PHP aaPanel (contoh: 74, 81)",
                "value" => "81",
                "jenis" => "text",
                "kategori" => "pengaturan_aapanel",
                "script" => ""],
        );

        foreach ($data as $item) {
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
