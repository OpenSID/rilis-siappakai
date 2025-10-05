<?php

namespace App\Services;

use Illuminate\Console\Command;

class ConsoleService
{
    public static function isRunningInConsole(): bool
    {
        return php_sapi_name() === 'cli';
    }

    public static function info(string $message): void
    {
        if (self::isRunningInConsole()) {
            echo $message;
        }
    }
}
