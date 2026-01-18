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
        Schema::create('cloudflare_rule_mappings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rule_master_id')->constrained('cloudflare_rule_masters')->onDelete('cascade');
            $table->unsignedBigInteger('customer_domain_id'); // Relation to pelanggan_sinkron_cloudflare
            $table->string('cloudflare_rule_id')->nullable(); // ID from Cloudflare
            $table->timestamp('synced_at')->nullable();
            $table->string('status')->default('pending'); // pending, synced, error
            $table->timestamps();

            // Foreign key to existing table if needed, using generic integer for safety
            $table->foreign('customer_domain_id')->references('id')->on('pelanggan_sinkron_cloudflare')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cloudflare_rule_mappings');
    }
};
