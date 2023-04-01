<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePelangganTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pelanggan', function (Blueprint $table) {
            $table->id();
            $table->string('kode_desa', 16)->unique();
            $table->string('nama_desa', 32);
            $table->string('langganan_opensid', 32);
            $table->string('versi_opensid', 32)->nullable();
            $table->string('domain_opensid', 128);
            $table->string('domain_pbb', 128)->nullable();
            $table->string('domain_api', 128)->nullable();
            $table->enum('status_langganan_opensid', ['1', '2', '3']); // 1. Aktif, 2. Suspended, 3. Tidak Aktif
            $table->enum('status_langganan_saas', ['1', '2', '3']); // 1. Aktif, 2. Suspended, 3. Tidak Aktif
            $table->date('tgl_akhir_premium')->nullable();
            $table->date('tgl_akhir_saas')->nullable();
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
        Schema::dropIfExists('pelanggan');
    }
}
