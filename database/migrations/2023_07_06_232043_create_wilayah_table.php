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
        Schema::create('wilayah', function (Blueprint $table) {
            $table->id();
            $table->string('kode_desa', 16)->unique();
            $table->string('nama_desa', 40)->nullable();
            $table->string('nama_kec', 32)->nullable();
            $table->enum('status_terdaftar', ['0', '1'])->nullable(); // 0. Belum Terdaftar 1. Terdaftar
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
        Schema::dropIfExists('wilayah');
    }
};
