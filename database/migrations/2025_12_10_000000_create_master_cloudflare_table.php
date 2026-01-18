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
        Schema::create('master_cloudflare', function (Blueprint $table) {
            $table->id();
            $table->string('domain', 255)->unique();
            $table->text('api_token'); // encrypted
            $table->string('zone_id', 255)->nullable();
            $table->string('account_id', 255)->nullable(); // Changed from integer to string (Cloudflare returns hash)
            $table->string('account_name', 255)->nullable();
            $table->enum('status', ['aktif', 'nonaktif'])->default('aktif');
            $table->text('last_error')->nullable();
            $table->timestamp('last_checked_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('master_cloudflare');
    }
};
