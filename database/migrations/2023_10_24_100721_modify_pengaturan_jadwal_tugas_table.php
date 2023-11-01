<?php

use App\Models\JadwalTugas;
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
            ["command" => "siappakai:backup-vps-sftp", "timezone" => "Asia/Jakarta", "jam" => "04:15", "keterangan" => "Jadwal Tugas Backup ke VPS"],
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
