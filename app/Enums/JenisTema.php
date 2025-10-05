<?php
namespace App\Enums;

enum JenisTema: string
{
    case GRATIS = 'tema-gratis';
    case PRO = 'tema-pro';

    public function label(): string
    {
        return match ($this) {
            self::GRATIS => 'Tema Gratis',
            self::PRO => 'Tema Pro',
        };
    }
}