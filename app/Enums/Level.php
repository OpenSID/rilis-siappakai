<?php

namespace App\Enums;

enum Level: int
{
    case Provinsi = 1;
    case Kabupaten = 2;
    case Kecamatan = 3;

    public function label(): string
    {
        return match($this) {
            self::Provinsi => 'Level 1 Provinsi',
            self::Kabupaten => 'Level 2 Kabupaten',
            self::Kecamatan => 'Level 3 Kecamatan',
        };
    }
}
