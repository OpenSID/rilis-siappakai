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
        Schema::table('pengaturan_aplikasi', function (Blueprint $table) {
            $table->integer('required')->default(0)->nullable()->after('keterangan');
            $table->json('options')->nullable()->after('kategori');


            $table->json('attributes')->nullable()->after('script');
            $table->string('class', 100)->nullable()->after('attributes');
            $table->string('placeholder', 100)->nullable()->after('class');
            $table->integer('urut')->nullable()->after('kategori');
        });


    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('pengaturan_aplikasi', function (Blueprint $table) {
            $table->dropColumn(['options', 'attributes', 'class', 'placeholder']);
        });
    }
};
