<?php

namespace Database\Seeders;

use Database\Seeders\Pengaturan\{AplikasiSeeder, UsersSeeder};
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call([
            //Pengaturan
            UsersSeeder::class,
        ]);
    }
}
