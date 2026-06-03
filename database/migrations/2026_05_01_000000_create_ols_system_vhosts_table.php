<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Table sudah ada dari migration sebelumnya - skip jika sudah ada
        if (!Schema::hasTable('ols_system_vhosts')) {
            Schema::create('ols_system_vhosts', function (Blueprint $table) {
                $table->id();
                $table->string('vhost_name')->unique()->comment('Nama vhost (misal: siappakai, openkab, api-gabungan)');
                $table->string('domain')->comment('Domain untuk vhost (misal: scriptopendesa.ngadakab.go.id)');
                $table->string('keterangan')->nullable()->comment('Deskripsi vhost');
                $table->boolean('aktif')->default(true)->comment('Apakah vhost aktif di listeners config');
                $table->timestamps();

                $table->index('aktif');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ols_system_vhosts');
    }
};
