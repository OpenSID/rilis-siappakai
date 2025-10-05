<?php
namespace App\Enums;

enum Warna: string
{
    case PRIMARY = 'primary';
    case SUCCESS = 'success';
    case WARNING = 'warning';
    case DANGER = 'danger';
    case SECONDARY = 'secondary';

    public function label(): string
    {
        return match ($this) {
            self::PRIMARY => 'Primary',
            self::SUCCESS => 'Success',
            self::WARNING => 'Warning',
            self::DANGER => 'Danger',
            self::SECONDARY => 'Secondary',
        };
    }
}
