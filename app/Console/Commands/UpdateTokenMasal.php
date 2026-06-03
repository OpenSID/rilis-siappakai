<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\TokenService;

class UpdateTokenMasal extends Command
{
    /**
     * Nama & signature command
     */
    protected $signature = 'siappakai:update-token-masal';

    /**
     * Deskripsi command
     */
    protected $description = 'Update data pelanggan melalui token';

    /**
     * Eksekusi command
     */
    public function handle(TokenService $service)
    {
        $count = $service->handle();

        $this->info("Update token masal selesai. Total: $count pelanggan");
    }
}
