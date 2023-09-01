<?php

use Database\Seeders\Pengaturan\TemaSeeder;
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
        Schema::create('pengaturan_temas', function (Blueprint $table) {
            $table->id();
            $table->string('username');
            $table->string('tema');
            $table->string('repo');
            $table->string('branch');
            $table->timestamps();
        });
        
        $data = new TemaSeeder();
        $data->run();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('pengaturan_temas');
    }
};
