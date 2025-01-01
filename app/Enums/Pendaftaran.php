<?php

namespace App\Enums;

enum Pendaftaran: string
{
    case YA = 'true';
    case TIDAK = 'false';

    public function options(string $sebutandesa, string $sebutankab, string $namakabupaten)
    {
        return match ($this) {
            self::TIDAK => 'Daftarkan per ' . strtolower($sebutandesa),
            self::YA => 'Daftarkan semua ' . strtolower($sebutandesa) . ' pada ' . $sebutankab . ' ' . ucwords(strtolower($namakabupaten)),
        };
    }
}
