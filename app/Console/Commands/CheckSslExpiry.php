<?php

namespace App\Console\Commands;

use App\Models\Opendk;
use App\Models\Pelanggan;
use Illuminate\Console\Command;
use DateTime;
use Illuminate\Support\Facades\Log;

class CheckSslExpiry extends Command
{
    protected $signature = 'siappakai:ssl-check-expiry {--domain=}';
    protected $description = 'Periksa tanggal kadaluarsa SSL untuk semua domain dan perbarui status di database';

    public function handle()
    {
        $domain = $this->option('domain');

        if (is_null($domain)) {
            $this->info("🔍 Mengecek status SSL semua domain...");

            $opendks = Opendk::whereNotNull('domain_opendk')->get();
            $opensids = Pelanggan::whereNotNull('domain_opensid')->get();

            $this->checkExpiry($opendks, 'domain_opendk');
            $this->checkExpiry($opensids, 'domain_opensid');
        } else {
            $this->info("🔍 Mengecek status SSL domain: {$domain}...");

            $opendks = Opendk::where('domain_opendk', $domain)->get();
            $opensids = Pelanggan::where('domain_opensid', $domain)->get();

            $this->checkExpiry($opendks, 'domain_opendk');
            $this->checkExpiry($opensids, 'domain_opensid');
        }

        $this->info("🎯 Pemeriksaan SSL selesai!");
        return Command::SUCCESS;
    }

    /**
     * Cek expiry untuk sekumpulan model.
     */
    private function checkExpiry($certs, string $domainField): void
    {
        foreach ($certs as $cert) {
            $domain = $cert->$domainField;

            if (empty($domain)) {
                $this->warn("⚠️ Domain kosong, dilewati.");
                continue;
            }

            // Pastikan domain valid (tanpa http/https)
            $cleanDomain = preg_replace('#^https?://#', '', $domain);

            try {
                $context = stream_context_create(["ssl" => ["capture_peer_cert" => true]]);
                $client = @stream_socket_client(
                    "ssl://{$cleanDomain}:443",
                    $errno,
                    $errstr,
                    10,
                    STREAM_CLIENT_CONNECT,
                    $context
                );

                if (!$client) {
                    $this->warn("⚠️ Tidak dapat terhubung ke {$cleanDomain}: $errstr");
                    continue;
                }

                $params = stream_context_get_params($client);
                $x509 = openssl_x509_parse($params["options"]["ssl"]["peer_certificate"]);

                if (!$x509 || !isset($x509['validTo_time_t'])) {
                    $this->warn("⚠️ Tidak bisa membaca sertifikat untuk {$cleanDomain}");
                    continue;
                }

                // Ambil tanggal kadaluarsa
                $expiry = date('Y-m-d', $x509['validTo_time_t']);

                // Ambil issuer dan CN
                $issuerOrg = $x509['issuer']['O'] ?? 'Unknown';
                $subjectCN = $x509['subject']['CN'] ?? '';

                // Deteksi jenis SSL
                $isWildcard = str_starts_with($subjectCN, '*.');
                $isLetsEncrypt = str_contains(strtolower($issuerOrg), "let's encrypt");

                $jenis = $isWildcard ? 'wildcard' : ($isLetsEncrypt ? "letsencrypt" : null);

                // Hitung sisa hari
                $today = new DateTime();
                $expiryDate = new DateTime($expiry);
                $daysLeft = (int) $today->diff($expiryDate)->format('%r%a');

                $status = match (true) {
                    $daysLeft < 0 => 'expired',
                    $daysLeft <= 30 => 'akan_expired',
                    default => 'aktif',
                };

                // Update database
                $cert->update([
                    'tgl_akhir' => $expiry,
                    'jenis_ssl' => $jenis,
                ]);

                $this->info("✅ {$cleanDomain} → {$status} ({$jenis}, sisa {$daysLeft} hari, kadaluarsa {$expiry})");

            } catch (\Throwable $e) {
                $this->error("❌ Error memproses {$domain}: {$e->getMessage()}");
                Log::error("SSL Check Error", ['domain' => $domain, 'error' => $e->getMessage()]);
            }
        }
    }
}
