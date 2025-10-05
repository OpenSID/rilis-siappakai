<?php

namespace App\Enums;

enum RepositoryEnum: string
{
    case OPENSID_UMUM = 'OpenSID/OpenSID';
    case OPENSID_PREMIUM = 'OpenSID/rilis-premium';
    case OPENKAB = 'OpenSID/OpenKab';
    case OPENKAB_API = 'OpenSID/API-Database-Gabungan';
    case DASBOARD_SIAPPAKAI = 'OpenSID/rilis-siappakai';
    case OPENSID_API = 'OpenSID/rilis-opensid-api';
    case PBB = 'OpenSID/rilis-pbb';
    case OPENDK = 'OpenSID/OpenDK';

    /**
     * Ambil owner dari repository.
     */
    public function getOwner(): string
    {
        return explode('/', $this->value)[0];
    }

    /**
     * Ambil nama repository.
     */
    public function getRepo(): string
    {
        return explode('/', $this->value)[1];
    }

    public function getFolderName(): string
    {
        return match ($this) {
            self::OPENSID_UMUM => 'umum',
            self::OPENSID_PREMIUM => 'premium',
            self::OPENKAB => 'openkab',
            self::OPENKAB_API => 'api-gabungan',
            self::DASBOARD_SIAPPAKAI => 'dasbor-siappakai',
            self::OPENSID_API => 'opensid-api',
            self::PBB => 'pbb_desa',
            self::OPENDK => 'opendk',
        };
    }

    /**
     * Ambil enum berdasarkan folder name (umum, premium, dll).
     */
    public static function fromFolderName(string $folderName): ?self
    {
        return match ($folderName) {
            'umum' => self::OPENSID_UMUM,
            'premium' => self::OPENSID_PREMIUM,
            'openkab' => self::OPENKAB,
            'api-gabungan' => self::OPENKAB_API,
            'dasbor-siappakai' => self::DASBOARD_SIAPPAKAI,
            'opensid-api' => self::OPENSID_API,
            'pbb' => self::PBB,
            'opendk' => self::OPENDK,
            default => null, // Kembalikan null jika tidak cocok
        };
    }

    /**
     * Ambil folder template berdasarkan folder name (umum, premium, dll).
     */
    public static function getFolderMaster(string $folderName): string
    {
        $path_root = dirname(base_path(), 1);
        $path_root . DIRECTORY_SEPARATOR . 'master-opensid' . DIRECTORY_SEPARATOR . 'premium';
        return match ($folderName) {
            'umum' => $path_root . DIRECTORY_SEPARATOR . 'master-opensid' . DIRECTORY_SEPARATOR . 'umum',
            'premium' => $path_root . DIRECTORY_SEPARATOR . 'master-opensid' . DIRECTORY_SEPARATOR . 'premium',
            'opensid-api' => $path_root . DIRECTORY_SEPARATOR . 'master-api' . DIRECTORY_SEPARATOR . 'opensid-api',
            'dasbor-siappakai' => $path_root . DIRECTORY_SEPARATOR . 'dasbor-siappakai',
            'pbb' => $path_root . DIRECTORY_SEPARATOR . 'master-pbb' . DIRECTORY_SEPARATOR . 'pbb_desa',
            'opendk' => $path_root . DIRECTORY_SEPARATOR . 'master-opendk' . DIRECTORY_SEPARATOR . 'opendk',
            'openkab' => $path_root . DIRECTORY_SEPARATOR . 'openkab',
            'api-gabungan' => $path_root . DIRECTORY_SEPARATOR . 'api-gabungan',
            default => null, // Kembalikan null jika tidak cocok
        };
    }

    /**
     * Ambil folder template berdasarkan folder name (umum, premium, dll).
     */
    public static function getFolderTemplate(string $folderName): string
    {
        $base_path = base_path() . DIRECTORY_SEPARATOR . 'master-template';
        return match ($folderName) {
            'umum' => $base_path . DIRECTORY_SEPARATOR . 'template-opensid',
            'premium' => $base_path . DIRECTORY_SEPARATOR . 'template-opensid',
            'opensid-api' => $base_path . DIRECTORY_SEPARATOR . 'template-api',
            'pbb' => $base_path . DIRECTORY_SEPARATOR . 'template-pbb',
            'opendk' => $base_path . DIRECTORY_SEPARATOR . 'template-opendk',
            'openkab' => $base_path . DIRECTORY_SEPARATOR . 'template-openkab',
            'api-gabungan' => $base_path . DIRECTORY_SEPARATOR . 'template-api-satudata',
            default => null, // Kembalikan null jika tidak cocok
        };
    }
}
