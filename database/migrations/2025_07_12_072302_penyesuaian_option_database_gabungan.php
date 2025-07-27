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
        $folderMultisite = config('siappakai.root.folder_multisite');
        DB::table('pelanggan')->get()->each(function ($value) use ($folderMultisite) {
            $this->perbaruiDatabase($folderMultisite, $value->kode_desa);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {}

    /**
     * Perbarui file database.php untuk desa yang berbeda
     *
     * @param string $folderMultisite folder root untuk multisite
     *
     * @return void
     */
    public function perbaruiDatabase($folderMultisite, $kodeDesa)
    {
        $kodeDesaWithoutDot = str_replace('.', '', $kodeDesa);
        $folderDesa = $folderMultisite . $kodeDesaWithoutDot;

        // perbarui file database.php
        $DBconfigDesa = $folderDesa . DIRECTORY_SEPARATOR . 'desa' . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'database.php';

        // Check if template file exists and add PDO options if missing

        if (file_exists($DBconfigDesa)) {
            $templateContent = file_get_contents($DBconfigDesa);

            // Check if PDO options line doesn't exist
            if (strpos($templateContent, "\$db['default']['options']") === false) {
                // Add PDO options at the end of the file before closing PHP tag
                $pdoOptionsLine = "\$db['default']['options'] = [PDO::ATTR_EMULATE_PREPARES => true];\n";

                // If file ends with  tag php add before it, otherwise add at the end
                if (substr(trim($templateContent), -2) === '?>') {
                    $templateContent = str_replace('?>', $pdoOptionsLine . '?>', $templateContent);
                } else {
                    $templateContent .= "\n" . $pdoOptionsLine;
                }

                // Write the updated content back to the template file
                file_put_contents($DBconfigDesa, $templateContent);
            }
        }
    }
};
