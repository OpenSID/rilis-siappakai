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
        Schema::create('cloudflare_rule_deploy_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('deploy_history_id')->constrained('cloudflare_rule_deploy_histories')->onDelete('cascade');
            $table->unsignedBigInteger('customer_domain_id');
            $table->string('status'); // success, failed
            $table->text('error_message')->nullable();
            $table->json('payload')->nullable();
            $table->json('response')->nullable();
            $table->timestamps();

            $table->foreign('customer_domain_id')->references('id')->on('pelanggan_sinkron_cloudflare')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cloudflare_rule_deploy_logs');
    }
};
