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
        // Check if the recycle bin cleanup task already exists
        $existingTask = JadwalTugas::where('command', 'recycle-bin:clean')->first();

        if (!$existingTask) {
            JadwalTugas::create([
                'command' => 'recycle-bin:clean',
                'timezone' => 'Asia/Jakarta',
                'jam' => '00:00', // Midnight
                'keterangan' => 'Jadwal Tugas Pembersihan Recycle Bin'
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
        // Remove the recycle bin cleanup task
        JadwalTugas::where('command', 'recycle-bin:clean')->delete();
    }
};
