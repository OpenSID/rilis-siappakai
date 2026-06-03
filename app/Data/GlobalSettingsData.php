<?php

namespace App\Data;

use Illuminate\Support\Facades\Log;

class GlobalSettingsData
{
    /**
     * Path ke file template default.
     */
    private static function templatePath(): string
    {
        return storage_path('app/global-settings-template.json');
    }

    /**
     * Template default pengaturan global.
     * Jika ada file template yang disimpan, gunakan nilai dari file tersebut.
     *
     * @return array
     */
    public static function defaultSettings(): array
    {
        $defaults = self::baseDefaults();

        // Merge dengan value dari file template jika ada
        $saved = self::loadSavedDefaults();
        if (! empty($saved)) {
            $savedMap = collect($saved)->keyBy('key');
            foreach ($defaults as &$setting) {
                if ($savedMap->has($setting['key'])) {
                    $setting['value'] = $savedMap->get($setting['key'])['value'];
                }
            }
        }

        return $defaults;
    }

    /**
     * Update default values template dan simpan ke file.
     *
     * @param array $values Key-value pairs ['key' => 'value']
     * @return bool
     */
    public static function updateDefaults(array $values): bool
    {
        $defaults = self::baseDefaults();

        foreach ($defaults as &$setting) {
            if (array_key_exists($setting['key'], $values)) {
                $setting['value'] = $values[$setting['key']];
            }
        }

        $path = self::templatePath();
        $dir = dirname($path);

        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $result = file_put_contents(
            $path,
            json_encode($defaults, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
        );

        if ($result !== false) {
            Log::info('GlobalSettingsData: Template defaults updated');
        }

        return $result !== false;
    }

    /**
     * Load saved default values dari file template.
     *
     * @return array
     */
    private static function loadSavedDefaults(): array
    {
        $path = self::templatePath();

        if (! file_exists($path)) {
            return [];
        }

        $content = file_get_contents($path);
        $data = json_decode($content, true);

        return is_array($data) ? $data : [];
    }

    /**
     * Base template default (hardcoded).
     *
     * @return array
     */
    private static function baseDefaults(): array
    {
        return [
            [
                'judul'      => 'Mapbox Key',
                'key'        => 'mapbox_key',
                'value'      => null,
                'urut'       => 2,
                'keterangan' => 'Mapbox API Key untuk peta',
                'jenis'      => 'input-text',
                'option'     => null,
                'attribute'  => null,
                'kategori'   => 'Peta',
            ],
            [
                'judul'      => 'Email protokol',
                'key'        => 'email_protocol',
                'value'      => 'smtp',
                'urut'       => 24,
                'keterangan' => 'Email protokol, misal : SMTP',
                'jenis'      => 'input-text',
                'option'     => null,
                'attribute'  => null,
                'kategori'   => 'email',
            ],
            [
                'judul'      => 'Email Host',
                'key'        => 'email_smtp_host',
                'value'      => null,
                'urut'       => 25,
                'keterangan' => 'Email host',
                'jenis'      => 'input-text',
                'option'     => null,
                'attribute'  => null,
                'kategori'   => 'email',
            ],
            [
                'judul'      => 'Email Username',
                'key'        => 'email_smtp_user',
                'value'      => null,
                'urut'       => 26,
                'keterangan' => 'Email username',
                'jenis'      => 'input-text',
                'option'     => null,
                'attribute'  => null,
                'kategori'   => 'email',
            ],
            [
                'judul'      => 'Email Password',
                'key'        => 'email_smtp_pass',
                'value'      => null,
                'urut'       => 27,
                'keterangan' => 'Email password',
                'jenis'      => 'password',
                'option'     => null,
                'attribute'  => null,
                'kategori'   => 'email',
            ],
            [
                'judul'      => 'Email Port',
                'key'        => 'email_smtp_port',
                'value'      => null,
                'urut'       => 28,
                'keterangan' => 'Email port',
                'jenis'      => 'input-text',
                'option'     => null,
                'attribute'  => null,
                'kategori'   => 'email',
            ],
        ];
    }
}
