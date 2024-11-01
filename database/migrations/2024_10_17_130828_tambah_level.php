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
            'key' => 'level',
            'keterangan' => 'Tingkatan Level Database',
            'kategori' => 'pengaturan_wilayah',
            'jenis' => 'option',
            'value' => 2,
            'created_at' => now(),
            'updated_at' => now()
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('pengaturan_aplikasi')->where('key', 'level')->delete();
    }
};
