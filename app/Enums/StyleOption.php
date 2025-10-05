<?php
namespace App\Enums;

enum StyleOption: string
{
    case TOURISM = 'tourism';
    case GO_GREEN = 'gogreen';
    case CLASSIC = 'classic';

    public function label(): string
    {
        return match ($this) {
            self::TOURISM => 'Tourism',
            self::GO_GREEN => 'Go Green',
            self::CLASSIC => 'Classic',
        };
    }
}
