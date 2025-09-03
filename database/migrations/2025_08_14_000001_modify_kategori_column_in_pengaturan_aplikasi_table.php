<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
    // Gunakan raw query karena `change()` biasanya memerlukan doctrine/dbal.
    // Pastikan driver adalah MySQL/MariaDB; untuk PostgreSQL/SQLite sesuaikan SQL.
    DB::statement("ALTER TABLE `pengaturan_aplikasi` MODIFY `kategori` VARCHAR(125) NULL");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
    // Kembalikan ke definisi sebelumnya menggunakan raw query
    DB::statement("ALTER TABLE `pengaturan_aplikasi` MODIFY `kategori` VARCHAR(255) NULL");
    }
};
