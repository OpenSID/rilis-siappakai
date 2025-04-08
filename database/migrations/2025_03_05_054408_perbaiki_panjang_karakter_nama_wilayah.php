<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE wilayah MODIFY COLUMN nama_desa VARCHAR(150) NULL");
        DB::statement("ALTER TABLE wilayah MODIFY COLUMN nama_kec VARCHAR(150) NULL");
        DB::statement("ALTER TABLE wilayah MODIFY COLUMN nama_kab VARCHAR(150) NULL");
        DB::statement("ALTER TABLE wilayah MODIFY COLUMN nama_prov VARCHAR(150) NULL");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE wilayah MODIFY COLUMN nama_desa VARCHAR(40) NULL");
        DB::statement("ALTER TABLE wilayah MODIFY COLUMN nama_kec VARCHAR(40) NULL");
        DB::statement("ALTER TABLE wilayah MODIFY COLUMN nama_kab VARCHAR(40) NULL");
        DB::statement("ALTER TABLE wilayah MODIFY COLUMN nama_prov VARCHAR(40) NULL");
    }
};
