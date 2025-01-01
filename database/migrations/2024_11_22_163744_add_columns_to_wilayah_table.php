<?php

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
        Schema::table('wilayah', function (Blueprint $table) {
            $table->string('kode_kec', 16)->nullable()->after('nama_desa');
            $table->string('kode_kab', 16)->nullable()->after('nama_kec');
            $table->string('nama_kab', 40)->nullable()->after('kode_kab');
            $table->string('kode_prov', 16)->nullable()->after('nama_kab');
            $table->string('nama_prov', 40)->nullable()->after('kode_prov');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('wilayah', function (Blueprint $table) {
            $table->dropColumn([
                'kode_kec',
                'kode_kab',
                'nama_kab',
                'kode_prov',
                'nama_prov',
            ]);
        });
    }
};
