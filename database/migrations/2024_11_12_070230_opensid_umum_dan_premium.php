<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
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
        DB::table('pengaturan_aplikasi')->insert([
            'key' => 'opensid',
            'value' => 2,
            'keterangan' => 'Pilih apakah akan menggunakan OpenSID Umum atau Premium',
            'required' => 1,
            'jenis' => 'option',
            'kategori' => 'pengaturan_dasar',
            'urut' => '1',
            'options' => json_encode([["value" => "1", "label" => "OpenSID Umum"], ["value" => "2", "label" => "OpenSID Premium"],  ["value" => "3", "label" => "OpenSID Umum dan Premium"]]),
            'script' => null,
            'label' => 'OpenSID',
            'attributes' => null,
            'class' => null,
            'placeholder' => '-- Pilih Jenis OpenSid --',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // perbaiki aa panel
        DB::table('pengaturan_aplikasi')->where('key', 'server_panel')->update(['script' => 'pages.pengaturan.aplikasi.components.pengaturan-aapanel']);
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
