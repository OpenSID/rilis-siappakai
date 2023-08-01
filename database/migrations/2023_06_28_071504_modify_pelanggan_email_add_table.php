<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
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
        Schema::table('pelanggan_email', function (Blueprint $table) {
            $table->after('mail_address', function ($table) {
                $table->string('smtp_protocol', 64)->nullable();
                $table->string('smtp_host', 64)->nullable();
                $table->string('smtp_user', 64)->nullable();
                $table->string('smtp_pass', 64)->nullable();
                $table->string('smtp_port', 64)->nullable();
            });
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('pelanggan_email', function (Blueprint $table) {
            $table->dropColumn(['smtp_protocol']);
            $table->dropColumn(['smtp_host']);
            $table->dropColumn(['smtp_user']);
            $table->dropColumn(['smtp_pass']);
            $table->dropColumn(['smtp_port']);
        });
    }
};
