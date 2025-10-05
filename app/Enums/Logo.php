<?php
namespace App\Enums;

enum Logo: string
{
    case BAWAAN_TEMA = 'true';
    case BAWAAN_OPENSID = 'false';

    public function label(): string
    {
        return match ($this) {
            self::BAWAAN_TEMA => 'Bawaan Tema',
            self::BAWAAN_OPENSID => 'Bawaan OpenSID',
        };
    }
}
