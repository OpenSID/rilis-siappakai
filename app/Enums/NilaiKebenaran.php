<?php
namespace App\Enums;

enum NilaiKebenaran: string
{
    case YA = 'true';
    case TIDAK = 'false';

    public function label(): string
    {
        return match ($this) {
            self::YA => 'Ya',
            self::TIDAK => 'Tidak',
        };
    }
}