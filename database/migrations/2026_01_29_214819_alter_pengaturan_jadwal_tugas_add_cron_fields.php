<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up()
    {
        Schema::table('pengaturan_jadwal_tugas', function (Blueprint $table) {
            $table->string('cron_expression')->nullable()->after('jam');
            $table->boolean('is_active')->default(true)->after('cron_expression');
            $table->text('log')->nullable()->after('is_active');
            $table->timestamp('last_run_at')->nullable()->after('log');
        });

        // MIGRASI DATA JAM → CRON (tanpa SQL manual)
        DB::table('pengaturan_jadwal_tugas')
            ->whereNull('cron_expression')
            ->get()
            ->each(function ($row) {
                if ($row->jam) {
                    [$hour, $minute] = explode(':', $row->jam);
                    $cron = "$minute $hour * * *";

                    DB::table('pengaturan_jadwal_tugas')
                        ->where('id', $row->id)
                        ->update(['cron_expression' => $cron]);
                }
            });
    }

    public function down()
    {
        Schema::table('pengaturan_jadwal_tugas', function (Blueprint $table) {
            $table->dropColumn(['cron_expression', 'is_active', 'log', 'last_run_at']);
        });
    }
};
