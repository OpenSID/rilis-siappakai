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
        Schema::create('cloudflare_rule_deploy_histories', function (Blueprint $table) {
            $table->id();
            $table->uuid('batch_id')->unique();
            $table->string('mode'); // full_replace, append, smart_sync
            $table->integer('total_domains')->default(0);
            $table->integer('success_count')->default(0);
            $table->integer('fail_count')->default(0);
            $table->string('status')->default('running'); // running, completed, partially_failed
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cloudflare_rule_deploy_histories');
    }
};
