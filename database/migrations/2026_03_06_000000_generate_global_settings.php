<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;

return new class extends Migration
{
    public function up()
    {
        try {
            // Jalankan command generate global settings untuk semua pelanggan
            Artisan::call('siappakai:generate-global-settings');

            Log::info('Command siappakai:generate-global-settings berhasil dijalankan via migration.');
        } catch (\Throwable $e) {
            Log::error('Gagal menjalankan command generate global settings via migration: ' . $e->getMessage());
        }
    }

    public function down()
    {
        // Tidak perlu rollback karena generate file bukan operasi DB
    }
};
