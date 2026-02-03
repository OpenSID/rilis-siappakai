<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up()
    {
        $data = [
            [
                "username" => "akmalfadli",
                "tema" => "perwira",
                "repo" => "perwira",
                "branch" => "main",
                "jenis_tema" => "tema-pro",
                "created_at" => now(),
                "updated_at" => now()
            ],
            [
                "username" => "mariyadi-lampung",
                "tema" => "seruit",
                "repo" => "seruit",
                "branch" => "main",
                "jenis_tema" => "tema-pro",
                "created_at" => now(),
                "updated_at" => now()
            ]
        ];

        foreach ($data as $row) {
            $exists = DB::table('pengaturan_temas')
                ->where('username', $row['username'])
                ->where('tema', $row['tema'])
                ->exists();

            if (!$exists) {
                DB::table('pengaturan_temas')->insert($row);
            }
        }
    }

    public function down()
    {
        DB::table('pengaturan_temas')
            ->where(function ($q) {
                $q->where('username', 'akmalfadli')
                ->where('tema', 'perwira');
            })
            ->orWhere(function ($q) {
                $q->where('username', 'mariyadi-lampung')
                ->where('tema', 'seruit');
            })
            ->delete();
    }

};
