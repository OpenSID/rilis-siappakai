<?php

use App\Jobs\PembaruanTokenJob;
use App\Models\Pelanggan;
use Illuminate\Database\Migrations\Migration;

class UpdateConfigDesa extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $pelanggans = Pelanggan::get();
        foreach ($pelanggans as $pelanggan) {
            PembaruanTokenJob::dispatch($pelanggan);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
