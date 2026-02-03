<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;

return new class extends Migration
{
    public function up()
    {
        try {
            // Jalankan command install tema
            Artisan::call('siappakai:install-master-tema');

            Log::info('Command siappakai:install-master-tema berhasil dijalankan via migration.');
        } catch (\Throwable $e) {
            Log::error('Gagal menjalankan command install tema via migration: ' . $e->getMessage());
        }
    }

    public function down()
    {
        // Tidak perlu rollback karena install tema bukan operasi DB
    }
};
