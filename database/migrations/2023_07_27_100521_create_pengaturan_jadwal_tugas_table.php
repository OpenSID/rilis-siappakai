<?php

use Database\Seeders\Pengaturan\JadwalTugasSeeder;
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
        Schema::create('pengaturan_jadwal_tugas', function (Blueprint $table) {
            $table->id();
            $table->string('command', 64)->nullable();
            $table->string('timezone', 16)->nullable();
            $table->string('jam', 8)->nullable();
            $table->string('keterangan', 64)->nullable();
            $table->timestamps();
        });

        $data = new JadwalTugasSeeder();
        $data->run();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('pengaturan_waktu_cronjob');
    }
};
