<?php

use App\Models\Aplikasi;
use Illuminate\Support\Facades\DB;
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
        // Insert pengaturan_database if it doesn't exist
        $existingPengaturan = DB::table('pengaturan_aplikasi')
            ->where('key', 'pengaturan_database')
            ->exists();

        if (!$existingPengaturan) {
            $data = [
                'key' => 'pengaturan_database',
                'value' => 'database_gabungan',
                'keterangan' => 'Pilih tipe database: database_tunggal (setiap desa memiliki database terpisah) atau database_gabungan (beberapa desa dalam satu database berdasarkan langganan)',
                'jenis' => 'option',
                'kategori' => 'pengaturan_dasar',
                'options' => json_encode([
                    ["value" => "database_tunggal", "label" => "Database Tunggal"],
                    ["value" => "database_gabungan", "label" => "Database Gabungan"],
                ]),
                "label" => "Tipe Database",
                "placeholder" => "Pilih tipe pelanggan"
            ];
            DB::table('pengaturan_aplikasi')->insert($data);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Remove pengaturan_database setting
        Aplikasi::where('key', 'pengaturan_database')->delete();
    }
};
