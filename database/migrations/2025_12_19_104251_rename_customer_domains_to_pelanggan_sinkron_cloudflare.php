<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::rename('customer_domains', 'pelanggan_sinkron_cloudflare');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::rename('pelanggan_sinkron_cloudflare', 'customer_domains');
    }
};
