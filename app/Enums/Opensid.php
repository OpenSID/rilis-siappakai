<?php

namespace App\Enums;

enum Opensid: int
{
    case UMUM = 1;
    case PREMIUM = 2;
    case PREMIUM_UMUM = 3;

    public function label(): string
    {
        return match($this) {
            self::UMUM => 'Umum',
            self::PREMIUM => 'Premium',
            self::PREMIUM_UMUM => 'Premium Dan Umum',
        };
    }
}
