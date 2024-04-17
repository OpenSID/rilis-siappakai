<?php

use Database\Seeders\Pengaturan\ModifyTemaSeeder;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ModifyPengaturanTemasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('pengaturan_temas', function (Blueprint $table) {
            $table->after('branch', function ($table) {
                $table->text('jenis_tema')->nullable();
            });
        });

        $data = new ModifyTemaSeeder();
        $data->run();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('pengaturan_temas', function (Blueprint $table) {
            $table->dropColumn(['jenis_tema']);
        });
    }
}
