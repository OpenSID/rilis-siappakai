<?php

namespace App\Enums;

enum StatusLangganan: int
{
    case AKTIF = 1;
    case SUSPENDED = 2;
    case TIDAK_AKTIF = 3;

    public function label(): string
    {
        return match($this) {
            self::AKTIF => 'Aktif',
            self::SUSPENDED => 'Suspend',
            self::TIDAK_AKTIF => 'Tidak Aktif',
        };
    }
}
