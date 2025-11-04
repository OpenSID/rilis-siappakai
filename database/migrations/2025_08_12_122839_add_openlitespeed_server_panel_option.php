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
        // Add OpenLiteSpeed option to server_panel configuration
        $server_panel = [
            ["value" => 1, "label" => "aaPanel"],
            ["value" => 2, "label" => "VPS Biasa"],
            ["value" => 3, "label" => "OpenLiteSpeed"],
        ];
        
        DB::table('pengaturan_aplikasi')
            ->where('key', 'server_panel')
            ->update(['options' => json_encode($server_panel)]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Revert to original server_panel options
        $server_panel = [
            ["value" => 1, "label" => "aaPanel"],
            ["value" => 2, "label" => "VPS Biasa"],
        ];
        
        DB::table('pengaturan_aplikasi')
            ->where('key', 'server_panel')
            ->update(['options' => json_encode($server_panel)]);
    }
};