<?php

namespace App\Services;

use App\Models\TemaKonfigurasi;
use Illuminate\Database\Eloquent\Builder;

class TemaService
{
    /**
     * Mengambil atribut konfigurasi tema berdasarkan kode desa.
     *
     * @param string $kodedesa Kode desa untuk mengambil tema konfigurasi.
     * @return array Konfigurasi tema yang mencakup aktivasi tema, logo, kode kota,
     *               informasi Facebook, alamat IP, warna, pengaturan fluid, menu,
     *               obrolan, widget, gaya, dan opsi penyembunyian layanan serta banner.
     *               Jika tema tidak ditemukan, akan mengembalikan konfigurasi default.
     */
    public static function getAtribute(string $kodedesa): array
    {
        // Ambil tema konfigurasi berdasarkan kode desa
        $tema = self::getTemaKonfigurasi($kodedesa);

        // Default konfigurasi tema jika tidak ditemukan
        $defaultConfig = self::getDefaultConfig();

        // Jika tema ditemukan, gabungkan data konfigurasi dari tema dengan default
        return $tema ? self::mapTemaToConfig($tema, $defaultConfig) : $defaultConfig;
    }

    /**
     * Mengambil data TemaKonfigurasi berdasarkan kode desa.
     *
     * @param string $kodedesa Kode desa untuk mencari tema konfigurasi.
     * @return TemaKonfigurasi|null Tema konfigurasi atau null jika tidak ditemukan.
     */
    private static function getTemaKonfigurasi(string $kodedesa): ?TemaKonfigurasi
    {
        return TemaKonfigurasi::with('tema')
            ->whereHas('tema', fn(Builder $query) => $query->where('kode_desa', $kodedesa))
            ->first();
    }

    /**
     * Mendapatkan konfigurasi tema default.
     *
     * @return array Konfigurasi default dengan nilai kosong.
     */
    private static function getDefaultConfig(): array
    {
        return [
            'aktivasi_tema' => '',
            'logo' => '',
            'kode_kota' => '',
            'fbadmin' => '',
            'fbappid' => '',
            'ip_address' => '',
            'color' => '',
            'fluid' => '',
            'menu' => '',
            'chats' => '',
            'widget' => '',
            'style' => '',
            'hide_layanan' => '',
            'hide_banner_layanan' => '',
            'hide_banner_laporan' => '',
        ];
    }

    /**
     * Memetakan data tema ke konfigurasi tema dengan menggabungkan nilai default.
     *
     * @param TemaKonfigurasi $tema TemaKonfigurasi instance.
     * @param array $defaultConfig Konfigurasi default.
     * @return array Konfigurasi tema yang telah digabungkan.
     */
    private static function mapTemaToConfig(TemaKonfigurasi $tema, array $defaultConfig): array
    {
        return array_merge($defaultConfig, [
            'aktivasi_tema' => $tema->aktivasi_tema,
            'logo' => $tema->logo,
            'kode_kota' => $tema->kode_kota,
            'fbadmin' => $tema->fbadmin,
            'fbappid' => $tema->fbappid,
            'ip_address' => $tema->ip_address,
            'color' => $tema->color,
            'fluid' => $tema->fluid,
            'menu' => $tema->menu,
            'chats' => $tema->chats,
            'widget' => $tema->widget,
            'style' => $tema->style,
            'hide_layanan' => $tema->hide_layanan,
            'hide_banner_layanan' => $tema->hide_banner_layanan,
            'hide_banner_laporan' => $tema->hide_banner_laporan,
        ]);
    }
}
