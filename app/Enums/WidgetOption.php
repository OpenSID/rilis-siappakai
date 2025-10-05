<?php
namespace App\Enums;

enum WidgetOption: string
{
    case SLIDE = 'w_slide';
    case GRID = 'w_grid';

    public function label(): string
    {
        return match ($this) {
            self::SLIDE => 'Slide',
            self::GRID => 'Grid',
        };
    }
}
