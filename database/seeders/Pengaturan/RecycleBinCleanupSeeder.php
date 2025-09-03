<?php

namespace Database\Seeders\Pengaturan;

use App\Models\JadwalTugas;
use Illuminate\Database\Seeder;

class RecycleBinCleanupSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Check if the recycle bin cleanup task already exists
        $existingTask = JadwalTugas::where('command', 'recycle-bin:clean')->first();
        
        if (!$existingTask) {
            JadwalTugas::create([
                'command' => 'recycle-bin:clean',
                'timezone' => 'Asia/Jakarta',
                'jam' => '00:00', // Midnight
                'keterangan' => 'Jadwal Tugas Pembersihan Recycle Bin (hapus file lama dari recycle bin)'
            ]);
        }
    }
}