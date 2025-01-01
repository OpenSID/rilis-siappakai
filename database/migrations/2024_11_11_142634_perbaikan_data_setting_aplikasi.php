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
        Schema::table('pengaturan_aplikasi', function (Blueprint $table) {
            $table->string('label', 100)->nullable()->after('script');
        });

        //perbaiki label
        $perbaiki = DB::table('pengaturan_aplikasi')->get();
        foreach ($perbaiki as $value) {
            DB::table('pengaturan_aplikasi')->where('key', $value->key)->update(['label' => ucwords(str_replace('_', ' ', $value->key))]);
        }

        DB::table('pengaturan_aplikasi')->where('key', 'akun_pengguna')->update(['kategori' => 'pengaturan_dasar']);
        DB::table('pengaturan_aplikasi')->where('key', 'redirect_https')->update(['kategori' => 'pengaturan_dasar', 'label' => 'Paksa Memakai Https']);
        DB::table('pengaturan_aplikasi')->where('key', 'donotusecolumnstatistics')->update(['label' => 'Column Statistic', 'kategori' => 'pengaturan_dasar']);
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
