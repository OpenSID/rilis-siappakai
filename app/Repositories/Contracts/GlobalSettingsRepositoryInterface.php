<?php

namespace App\Repositories\Contracts;

use App\Models\Pelanggan;

interface GlobalSettingsRepositoryInterface
{
    /**
     * Mendapatkan semua pengaturan global dari file global.json
     *
     * @param string $configPath Path ke file global.json
     * @return array
     */
    public function getAll(string $configPath): array;

    /**
     * Mendapatkan pengaturan berdasarkan key
     *
     * @param string $configPath Path ke file global.json
     * @param string $key Key pengaturan
     * @return array|null
     */
    public function findByKey(string $configPath, string $key): ?array;

    /**
     * Mendapatkan pengaturan berdasarkan kategori
     *
     * @param string $configPath Path ke file global.json
     * @param string $category Nama kategori
     * @return array
     */
    public function getByCategory(string $configPath, string $category): array;

    /**
     * Menyimpan/update pengaturan ke file global.json
     *
     * @param string $configPath Path ke file global.json
     * @param array $settings Data pengaturan
     * @return bool
     */
    public function save(string $configPath, array $settings): bool;

    /**
     * Update satu pengaturan berdasarkan key
     *
     * @param string $configPath Path ke file global.json
     * @param string $key Key pengaturan
     * @param mixed $value Nilai baru
     * @return bool
     */
    public function updateByKey(string $configPath, string $key, mixed $value): bool;

    /**
     * Mendapatkan path file global.json untuk pelanggan
     *
     * @param Pelanggan $pelanggan
     * @return string
     */
    public function getConfigPath(Pelanggan $pelanggan): string;

    /**
     * Cek apakah file global.json sudah ada
     *
     * @param string $configPath Path ke file global.json
     * @return bool
     */
    public function exists(string $configPath): bool;
}
