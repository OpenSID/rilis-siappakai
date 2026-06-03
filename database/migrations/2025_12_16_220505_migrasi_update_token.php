<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Artisan;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Artisan::call('siappakai:update-token-masal');
        echo Artisan::output();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Rollback not implemented as the effects of the Artisan command
        // (siappakai:update-token-masal) cannot be reliably reversed.
        // Manual intervention required if rollback is needed.
    }
};
