<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Artisan;
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
        Schema::table('opendk', function (Blueprint $table) {
            $table->after('port_domain', function ($table) {
                $table->enum('jenis_ssl', ['letsencrypt', 'wildcard'])->nullable();
                $table->date('tgl_akhir')->nullable();
            });
        });

        // 🔧 Jalankan command SSL check setelah migrasi selesai
        try {
            Artisan::call('siappakai:ssl-check-expiry');
            echo "\n✅ Command 'siappakai:ssl-check-expiry' berhasil dijalankan setelah migrasi.\n";
        } catch (\Throwable $e) {
            echo "\n⚠️  Gagal menjalankan 'siappakai:ssl-check-expiry': " . $e->getMessage() . "\n";
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('pelanggan', function (Blueprint $table) {
            $table->dropColumn(['jenis_ssl']);
            $table->dropColumn(['tgl_akhir']);
        });
    }
};
