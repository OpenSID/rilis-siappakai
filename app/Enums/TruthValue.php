<?php
namespace App\Enums;

enum TruthValue: string
{
    case YES = 'true';
    case NO = 'false';

    public function label(): string
    {
        return match ($this) {
            self::YES => 'Ya',
            self::NO => 'Tidak',
        };
    }
}
