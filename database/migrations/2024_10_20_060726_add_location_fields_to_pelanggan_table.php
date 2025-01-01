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
        Schema::table('pelanggan', function (Blueprint $table) {
            $table->string('kode_provinsi')->nullable()->after('id');
            $table->string('nama_provinsi')->nullable()->after('kode_provinsi');
            $table->string('kode_kabupaten')->nullable()->after('nama_provinsi');
            $table->string('nama_kabupaten')->nullable()->after('kode_kabupaten');
            $table->string('kode_kecamatan')->nullable()->after('nama_kabupaten');
            $table->string('nama_kecamatan')->nullable()->after('kode_kecamatan');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('pelanggan', function (Blueprint $table) {
            $table->dropColumn([
                'kode_provinsi',
                'kode_kabupaten',
                'kode_kecamatan',
                'nama_provinsi',
                'nama_kabupaten',
                'nama_kecamatan'
            ]);
        });
    }
};
