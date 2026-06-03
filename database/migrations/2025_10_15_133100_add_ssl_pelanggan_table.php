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
        Schema::table('pelanggan', function (Blueprint $table) {
            if (!Schema::hasColumn('pelanggan', 'jenis_ssl')) {
                $table->after('port_domain', function ($table) {
                    $table->enum('jenis_ssl', ['letsencrypt', 'wildcard'])->nullable();
                    $table->date('tgl_akhir')->nullable();
                });
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('pelanggan', function (Blueprint $table) {
            if (Schema::hasColumn('pelanggan', 'jenis_ssl')) {
                $table->dropColumn('jenis_ssl');
            }
            if (Schema::hasColumn('pelanggan', 'tgl_akhir')) {
                $table->dropColumn('tgl_akhir');
            }
        });
    }
};
