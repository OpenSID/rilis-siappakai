<?php

namespace Database\Seeders\Pengaturan;

use App\Models\PengaturanTema;
use Exception;
use Illuminate\Database\Seeder;

class ModifyTemaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // ubah pengaturan sebelumnya
        $temas = PengaturanTema::get();

        try {
            foreach ($temas as $item) {
                $item->jenis_tema = 'tema-pro';
                $item->save();
            }
        } catch (Exception $e) {
        }


        $mod_temas = array(
            ['username' => 'OpenSID', 'tema' => 'palanta', 'repo' => 'tema-palanta', 'branch' => 'master', 'jenis_tema' => 'tema-gratis'],
        );

        foreach ($mod_temas as $item) {
            PengaturanTema::create($item);
        }
    }
}
