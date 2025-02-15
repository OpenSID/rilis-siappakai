<?php

use App\Models\JadwalTugas;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $jadwals = array(
            ["command" => "siappakai:siappakai:install-module", "timezone" => "Asia/Jakarta", "jam" => "06:00", "keterangan" => "Jadwal Tugas Update Module Opensid"],
        );

        foreach ($jadwals as $item) {
            JadwalTugas::create($item);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
};
