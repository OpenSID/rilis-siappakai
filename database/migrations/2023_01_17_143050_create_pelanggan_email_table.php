<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePelangganEmailTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pelanggan_email', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('pelanggan_id')->nullable();
            $table->string('mail_host', 64)->nullable();
            $table->string('mail_user', 64)->nullable();
            $table->string('mail_pass', 64)->nullable();
            $table->string('mail_address', 64)->nullable();
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
        Schema::dropIfExists('pelanggan_email');
    }
}
