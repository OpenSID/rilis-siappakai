<?php

use App\Models\JadwalTugas;
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
        $jadwal = new JadwalTugas();
        $data_update = $jadwal::where('command', 'siappakai:update-saas')->first() ?? '';
        $data_delete = $jadwal::where('command', 'siappakai:delete-saas')->first() ?? '';

        $data1 = $jadwal::find($data_update->id);
        $data1->update(["command" => 'siappakai:update-siappakai']);

        $data2 = $jadwal::find($data_delete->id);
        $data2->update(["command" => 'siappakai:delete-siappakai']);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $jadwal = new JadwalTugas();
        $data_update = $jadwal::where('command', 'siappakai:update-siappakai')->first() ?? '';
        $data_delete = $jadwal::where('command', 'siappakai:delete-siappakai')->first() ?? '';

        $data1 = $jadwal::find($data_update->id);
        $data1->update(["command" => 'siappakai:update-saas']);

        $data2 = $jadwal::find($data_delete->id);
        $data2->update(["command" => 'siappakai:delete-saas']);
    }
};
