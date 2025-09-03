<?php

use Illuminate\Support\Facades\DB;
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
        // Check if the setting already exists
        $exists = DB::table('pengaturan_aplikasi')
            ->where('key', 'tipe_pelanggan')
            ->exists();

        // Only insert if it doesn't exist
        if (!$exists) {
            $data = [
                "key" => "tipe_pelanggan",
                "value" => "diskominfo",
                "keterangan" => "Tipe pelanggan default Diskominfo",
                "jenis" => "option",
                "kategori" => "pengaturan_dasar",
                "script" => "",
                "created_at" => date("Y-m-d H:i:s"),
                "updated_at" => date("Y-m-d H:i:s"),
                'options' => json_encode([
                    ["value" => "diskominfo", "label" => "Diskominfo"],
                    ["value" => "siappakai", "label" => "Siappakai"],
                ]),
                "label" => "Tipe Pelanggan",
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
        DB::table('pengaturan_aplikasi')
            ->where('key', 'tipe_pelanggan')
            ->delete();
    }
};
