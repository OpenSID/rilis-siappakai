<?php

namespace Database\Seeders\Pengaturan;

use App\Models\JadwalTugas;
use Illuminate\Database\Seeder;

class JadwalTugasSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $jadwals = array(
            ["command" => "siappakai:backup-database", "timezone" => "Asia/Jakarta", "jam" => "01:00", "keterangan" => "Jadwal Tugas Backup Database"],
            ["command" => "siappakai:backup-folder-desa", "timezone" => "Asia/Jakarta", "jam" => "02:00", "keterangan" => "Jadwal Tugas Backup Folder Desa"],
            ["command" => "siappakai:update-siappakai", "timezone" => "Asia/Jakarta", "jam" => "03:00", "keterangan" => "Jadwal Tugas Update Pelanggan SiapPakai"],
            ["command" => "siappakai:update-opensid", "timezone" => "Asia/Jakarta", "jam" => "03:15", "keterangan" => "Jadwal Tugas Update OpenSID"],
            ["command" => "siappakai:update-pbb", "timezone" => "Asia/Jakarta", "jam" => "03:30", "keterangan" => "Jadwal Tugas Update PBB"],
            ["command" => "siappakai:update-api", "timezone" => "Asia/Jakarta", "jam" => "03:45", "keterangan" => "Jadwal Tugas Update API"],
            ["command" => "siappakai:update-tema", "timezone" => "Asia/Jakarta", "jam" => "04:00", "keterangan" => "Jadwal Tugas Update Tema"],
            ["command" => "siappakai:delete-siappakai", "timezone" => "Asia/Jakarta", "jam" => "04:00", "keterangan" => "Jadwal Tugas Hapus Pelanggan SiapPakai"],
        );

        foreach ($jadwals as $item) {
            JadwalTugas::create($item);
        }
    }
}
