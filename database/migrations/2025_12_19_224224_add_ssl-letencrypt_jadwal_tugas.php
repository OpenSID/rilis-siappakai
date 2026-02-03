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
        // Check if ssl letsencrypt update task already exists
        $existingTask = JadwalTugas::where('command', 'siappakai:update-ssl-lets-encrypt')->first();

        if (!$existingTask) {
            JadwalTugas::create([
                'command' => 'siappakai:update-ssl-lets-encrypt',
                'timezone' => 'Asia/Jakarta',
                'jam' => '20:00', // 8 PM daily
                'keterangan' => 'Jadwal Tugas Pembaruan SSL Let\'s Encrypt'
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
        JadwalTugas::where('command', 'siappakai:update-ssl-lets-encrypt')->delete();
    }
};
