<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFieldAktivasiTemaKonfigurasiTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tema_konfigurasi', function (Blueprint $table) {
            $table->after('tema_id', function ($table) {
                $table->string('aktivasi_tema', 16)->nullable();
            });
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tema_konfigurasi', function (Blueprint $table) {
            $table->dropColumn('aktivasi_tema');
        });
    }
}
