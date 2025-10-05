<?php

namespace App\Services;

use App\Models\PelangganEmail;

class EmailService
{
    /**
     * Mengambil atribut konfigurasi email berdasarkan ID pelanggan.
     *
     * @param int $id ID pelanggan untuk mengambil konfigurasi email.
     * @return array Konfigurasi email yang mencakup host, user, password,
     *               alamat email, protokol SMTP, dan pengaturan lainnya.
     */
    public static function getEmailConfiguration(int $id): array
    {
        $email = PelangganEmail::where('pelanggan_id', $id)->first();

        if ($email) {
            return self::mapEmailConfiguration($email);
        }

        return self::getDefaultEmailConfiguration();
    }

    /**
     * Memetakan data konfigurasi email dari model ke array.
     *
     * @param PelangganEmail $email Instance model PelangganEmail.
     * @return array Konfigurasi email.
     */
    private static function mapEmailConfiguration(PelangganEmail $email): array
    {
        return [
            'mail_host' => $email->mail_host,
            'mail_user' => $email->mail_user,
            'mail_pass' => $email->mail_pass,
            'mail_address' => $email->mail_address,
            'smtp_protocol' => $email->smtp_protocol,
            'smtp_host' => $email->smtp_host,
            'smtp_user' => $email->smtp_user,
            'smtp_pass' => $email->smtp_pass,
            'smtp_port' => $email->smtp_port,
        ];
    }

    /**
     * Mendapatkan konfigurasi email default.
     *
     * @return array Konfigurasi email default dengan nilai kosong.
     */
    private static function getDefaultEmailConfiguration(): array
    {
        return [
            'mail_host' => '',
            'mail_user' => '',
            'mail_pass' => '',
            'mail_address' => '',
            'smtp_protocol' => '',
            'smtp_host' => '',
            'smtp_user' => '',
            'smtp_pass' => '',
            'smtp_port' => '',
        ];
    }
}
