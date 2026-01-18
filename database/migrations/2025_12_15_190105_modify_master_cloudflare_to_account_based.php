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
        Schema::table('master_cloudflare', function (Blueprint $table) {
            // Drop kolom yang tidak diperlukan
            $table->dropUnique(['domain']);
            $table->dropColumn(['domain', 'zone_id', 'account_id', 'last_error', 'last_checked_at']);
            
            // Ubah account_name menjadi unique dan not nullable
            $table->string('account_name', 255)->unique()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('master_cloudflare', function (Blueprint $table) {
            // Kembalikan kolom yang dihapus
            $table->string('domain', 255)->unique()->after('id');
            $table->string('zone_id', 255)->nullable()->after('api_token');
            $table->string('account_id', 255)->nullable()->after('zone_id');
            $table->text('last_error')->nullable()->after('status');
            $table->timestamp('last_checked_at')->nullable()->after('last_error');
            
            // Kembalikan account_name ke nullable
            $table->string('account_name', 255)->nullable()->change();
        });
    }
};
