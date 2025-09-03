<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
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
        DB::statement("ALTER TABLE opendk MODIFY COLUMN kode_kecamatan VARCHAR(8) NULL");

        Schema::table('wilayah', function (Blueprint $table) {
            $table->enum('opendk_terdaftar', ['0', '1'])
                ->default('0')
                ->nullable()
                ->after('status_terdaftar');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('wilayah', function (Blueprint $table) {
            $table->dropColumn([
                'opendk_terdaftar',
            ]);
        });
    }
};
