<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFieldsKonfigurasiTemaTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tema_konfigurasi', function (Blueprint $table) {
            $table->after('menu', function ($table) {
                $table->string('chats', 16)->nullable();
                $table->string('widget', 16)->nullable();
                $table->string('style', 16)->nullable();
                $table->string('hide_layanan', 16)->nullable();
                $table->string('hide_banner_layanan', 16)->nullable();
                $table->string('hide_banner_laporan', 16)->nullable();
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
            $table->dropColumn('chats');
            $table->dropColumn('widget');
            $table->dropColumn('style');
            $table->dropColumn('hide_layanan');
            $table->dropColumn('hide_banner_layanan');
            $table->dropColumn('hide_banner_laporan');
        });
    }
}
