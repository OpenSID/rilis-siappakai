<?php

namespace Database\Seeders\Pengaturan;

use App\Models\PengaturanTema;
use Illuminate\Database\Seeder;

class TemaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $temas = array(
            ['username' => 'rohmanudin05', 'tema' => 'batuah', 'repo' => 'batuah', 'branch' => 'master'],
            ['username' => 'ariandii', 'tema' => 'denatra', 'repo' => 'denatra', 'branch' => 'master'],
            ['username' => 'ariandii', 'tema' => 'denava', 'repo' => 'denava', 'branch' => 'master'],
            ['username' => 'OpenSID', 'tema' => 'silir', 'repo' => 'tema-silir', 'branch' => 'master'],
            ['username' => 'dafris', 'tema' => 'pusako', 'repo' => 'tema_pusako', 'branch' => 'main'],
        );

        foreach ($temas as $item) {
            PengaturanTema::create($item);
        }
    }
}
