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
        Schema::create('opendk', function (Blueprint $table) {
            $table->id();
            $table->string('kode_provinsi', 4)->nullable();
            $table->string('nama_provinsi', 64)->nullable();
            $table->string('kode_kabupaten', 6)->nullable();
            $table->string('nama_kabupaten', 64)->nullable();
            $table->string('kode_kecamatan', 6)->unique();
            $table->string('nama_kecamatan', 64)->nullable();
            $table->string('domain_opendk', 128)->nullable();
            $table->string('port_domain', 128)->nullable();
            $table->date('tgl_akhir_backup')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('opendk');
    }
};
