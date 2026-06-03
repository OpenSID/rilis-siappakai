<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('api_health_checks', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('pelanggan_id');
            $table->string('status', 20)->default('pending'); // pending, success, error
            $table->unsignedSmallInteger('http_status')->nullable();
            $table->text('message')->nullable();
            $table->json('response_data')->nullable();
            $table->timestamp('checked_at')->nullable();
            $table->timestamps();

            $table->foreign('pelanggan_id')
                ->references('id')->on('pelanggan')
                ->onDelete('cascade');

            $table->unique('pelanggan_id');
            $table->index('status');
            $table->index('checked_at');
            $table->index(['pelanggan_id', 'checked_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('api_health_checks');
    }
};
