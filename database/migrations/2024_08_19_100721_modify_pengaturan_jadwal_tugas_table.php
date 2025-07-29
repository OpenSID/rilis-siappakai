<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('pengaturan_jadwal_tugas')
            ->where('command', 'siappakai:update-saas')
            ->update(['command' => 'siappakai:update-siappakai']);

        DB::table('pengaturan_jadwal_tugas')
            ->where('command', 'siappakai:delete-saas')
            ->update(['command' => 'siappakai:delete-siappakai']);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('pengaturan_jadwal_tugas')
            ->where('command', 'siappakai:update-siappakai')
            ->update(['command' => 'siappakai:update-saas']);

        DB::table('pengaturan_jadwal_tugas')
            ->where('command', 'siappakai:delete-siappakai')
            ->update(['command' => 'siappakai:delete-saas']);
    }
};
