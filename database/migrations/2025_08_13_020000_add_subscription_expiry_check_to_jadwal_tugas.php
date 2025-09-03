<?php

use Illuminate\Database\Migrations\Migration;
use App\Models\JadwalTugas;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Check if the subscription expiry check task already exists
        $existingTask = JadwalTugas::where('command', 'siappakai:check-subscription-expiry')->first();

        if (!$existingTask) {
            JadwalTugas::create([
                'command' => 'siappakai:check-subscription-expiry',
                'timezone' => 'Asia/Jakarta',
                'jam' => '01:00', // 1 AM daily
                'keterangan' => 'Jadwal Tugas Pengecekan Tanggal Akhir Langganan'
            ]);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Remove the subscription expiry check task
        JadwalTugas::where('command', 'siappakai:check-subscription-expiry')->delete();
    }
};
