<?php

namespace App\Repositories;

use App\Models\Pelanggan;
use App\Repositories\Contracts\GlobalSettingsRepositoryInterface;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

class GlobalSettingsRepository implements GlobalSettingsRepositoryInterface
{
    /**
     * Nama file konfigurasi global
     */
    private const FILENAME = 'global.json';

    /**
     * {@inheritdoc}
     */
    public function getAll(string $configPath): array
    {
        if (! $this->exists($configPath)) {
            return [];
        }

        $content = File::get($configPath);
        $settings = json_decode($content, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            Log::error('GlobalSettingsRepository: JSON parse error', [
                'path'  => $configPath,
                'error' => json_last_error_msg(),
            ]);

            return [];
        }

        if (! is_array($settings)) {
            Log::error('GlobalSettingsRepository: JSON root is not an array', [
                'path'       => $configPath,
                'value_type' => gettype($settings),
            ]);

            return [];
        }

        return $settings;
    }

    /**
     * {@inheritdoc}
     */
    public function findByKey(string $configPath, string $key): ?array
    {
        $settings = $this->getAll($configPath);

        return collect($settings)->firstWhere('key', $key);
    }

    /**
     * {@inheritdoc}
     */
    public function getByCategory(string $configPath, string $category): array
    {
        $settings = $this->getAll($configPath);

        return collect($settings)
            ->filter(fn (array $setting) => strtolower($setting['kategori'] ?? '') === strtolower($category))
            ->values()
            ->toArray();
    }

    /**
     * {@inheritdoc}
     */
    public function save(string $configPath, array $settings): bool
    {
        $directory = dirname($configPath);

        if (! File::isDirectory($directory)) {
            File::makeDirectory($directory, 0755, true);
        }

        $json = json_encode($settings, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        return File::put($configPath, $json) !== false;
    }

    /**
     * {@inheritdoc}
     */
    public function updateByKey(string $configPath, string $key, mixed $value): bool
    {
        $settings = $this->getAll($configPath);

        $updated = false;

        foreach ($settings as &$setting) {
            if ($setting['key'] === $key) {
                $setting['value'] = $value;
                $updated = true;
                break;
            }
        }

        if (! $updated) {
            return false;
        }

        return $this->save($configPath, $settings);
    }

    /**
     * {@inheritdoc}
     */
    public function getConfigPath(Pelanggan $pelanggan): string
    {
        $folderMultisite = config('siappakai.root.folder_multisite');
        $kodeDesa = str_replace('.', '', $pelanggan->kode_desa);

        return $folderMultisite . $kodeDesa
            . DIRECTORY_SEPARATOR . 'desa'
            . DIRECTORY_SEPARATOR . 'config'
            . DIRECTORY_SEPARATOR . self::FILENAME;
    }

    /**
     * {@inheritdoc}
     */
    public function exists(string $configPath): bool
    {
        return File::exists($configPath);
    }
}
