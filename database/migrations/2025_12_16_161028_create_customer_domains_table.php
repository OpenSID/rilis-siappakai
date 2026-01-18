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
        Schema::create('customer_domains', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pelanggan_id')->constrained('pelanggan')->onDelete('cascade');
            $table->foreignId('cloudflare_account_id')->nullable()->constrained('master_cloudflare')->onDelete('set null');
            
            // Domain Information
            $table->string('domain', 255)->index();
            $table->enum('domain_type', ['zone', 'subdomain'])->nullable();
            
            // Cloudflare Zone Information
            $table->string('zone_id', 64)->nullable();
            $table->string('zone_name', 255)->nullable(); // For subdomains, this is the parent zone
            
            // DNS Validation
            $table->enum('dns_status', ['OK', 'MISSING', 'IP_MISMATCH', 'NOT_SYNCED'])->default('NOT_SYNCED');
            $table->string('current_ip', 45)->nullable(); // IPv4 or IPv6
            $table->string('expected_ip', 45)->nullable(); // For validation
            $table->boolean('is_proxied')->default(false);
            
            // Sync Status
            $table->timestamp('last_synced_at')->nullable();
            $table->text('last_error')->nullable();
            $table->integer('sync_attempt_count')->default(0);
            
            $table->timestamps();
            
            // Indexes
            $table->unique(['pelanggan_id', 'domain']);
            $table->index('zone_id');
            $table->index('dns_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customer_domains');
    }
};
