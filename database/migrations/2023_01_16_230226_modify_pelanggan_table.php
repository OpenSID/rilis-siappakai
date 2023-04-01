<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ModifyPelangganTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('pelanggan', function (Blueprint $table) {
            $table->after('tgl_akhir_backup', function ($table) {
                $table->text('token_premium')->nullable();
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
        Schema::table('pelanggan', function (Blueprint $table) {
            $table->dropColumn(['token_premium']);
        });
    }
}
