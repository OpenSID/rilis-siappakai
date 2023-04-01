<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTemaKonfigurasiTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tema_konfigurasi', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('tema_id')->nullable();
            $table->string('logo', 8)->nullable();
            $table->string('kode_kota', 8)->nullable();
            $table->string('fbadmin', 16)->nullable();
            $table->string('fbappid', 16)->nullable();
            $table->string('ip_address', 16)->nullable();
            $table->string('color', 16)->nullable();
            $table->string('fluid', 8)->nullable();
            $table->string('menu', 8)->nullable();
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
        Schema::dropIfExists('tema_konfigurasi');
    }
}
