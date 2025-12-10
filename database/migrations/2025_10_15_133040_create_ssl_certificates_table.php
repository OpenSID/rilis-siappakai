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
        Schema::create('ssl_certificates', function (Blueprint $table) {
            $table->id();
            $table->string('nama_sertifikat');
            $table->string('domain', 128)->nullable();
            $table->date('tgl_akhir')->nullable();
            $table->string('path_crt')->nullable();
            $table->string('path_key')->nullable();
            $table->string('path_ca')->nullable();
            $table->enum('status', ['aktif', 'akan berakhir', 'tidak aktif'])->default('aktif');
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
        Schema::dropIfExists('ssl_certificates');
    }
};
