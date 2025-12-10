<?php

namespace App\Console\Commands;

use App\Http\Controllers\Helpers\AttributeSiapPakaiController;
use App\Http\Controllers\Helpers\CommandController;
use Illuminate\Console\Command;

class ManageSslWildcard extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'siappakai:ssl-wildcard {--domain=} {--app=} {--kodewilayah=} {--force} {--no-enable : Hanya membuat file konfigurasi tanpa mengaktifkan site}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Instal SSL Wildcard pada domain tertentu menggunakan file dari /etc/apache2/privatekey/';

    private $att;
    private $comm;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->att = new AttributeSiapPakaiController();
        $this->comm = new CommandController();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $domain = $this->option('domain');
        $app = $this->option('app') ?? 'opensid';
        $kodewilayah = str_replace('.', '', $this->option('kodewilayah'));
        $force = $this->option('force');
        $noEnable = $this->option('no-enable');

        if (!$domain) {
            $this->error('❌ Domain harus diisi. Contoh: php artisan siappakai:ssl-wildcard --domain=tumanggal.berdesa.id --app=opensid');
            return 1;
        }

        // ambil domain root (misal: berdesa.id dari tumanggal.berdesa.id)
        $parts = explode('.', $domain);
        $rootDomain = implode('.', array_slice($parts, -2));

        // Ambil nama domain tanpa TLD (.id, .com, dll)
        $domainNameOnly = $parts[count($parts) - 2]; // contoh: berdesa

        $wildcardDir = "/etc/apache2/privatekey/{$rootDomain}/";
        $crt = "{$wildcardDir}{$domainNameOnly}.crt";
        $key = "{$wildcardDir}{$domainNameOnly}.key";
        $ca  = "{$wildcardDir}{$domainNameOnly}-ca.crt";

        if (!is_dir($wildcardDir)) {
            $this->error("⚠️ Folder SSL wildcard tidak ditemukan di {$wildcardDir}");
            return 1;
        }

        if (!file_exists($crt) || !file_exists($key)) {
            $this->error("❌ File SSL tidak lengkap di {$wildcardDir} (butuh .crt dan .key)");
            return 1;
        }

        $this->info("🔐 Menggunakan wildcard SSL dari {$wildcardDir}");

        if ($app == 'opensid'){
            $siteDir = $this->att->getMultisiteFolder() . $kodewilayah;
        } else if ($app == 'opendk') {
            $siteDir = $this->att->getMultisiteFolder() . $kodewilayah . DIRECTORY_SEPARATOR . 'public';
        } else {
            $this->error("❌ Jenis app tidak dikenal: {$app}");
            return 1;
        }

        if (!is_dir($siteDir)) {
            $this->warn("📂 Folder aplikasi belum ada: {$siteDir}");
            mkdir($siteDir, 0755, true);
        }

        $confPath = $this->att->getApacheConfDir() . $domain . '.conf';

        // jika ada file lama
        if (file_exists($confPath)) {
            if ($force) {
                $this->warn("🧹 Menghapus konfigurasi lama: {$confPath}");
                copy($confPath, $confPath . '.bak-' . date('YmdHis')); // backup dulu
                $this->comm->removeFile($confPath);
            } else {
                $this->error("⚠️ Konfigurasi sudah ada. Gunakan --force untuk menimpa file lama.");
                return 1;
            }
        }

        $confContent = "
            <VirtualHost *:80>
                ServerName {$domain}
                ServerAlias www.{$domain}
                Redirect permanent / https://{$domain}/
            </VirtualHost>

            <VirtualHost *:443>
                ServerName {$domain}

                DocumentRoot {$siteDir}
                <Directory {$siteDir}>
                    AllowOverride All
                    Require all granted
                </Directory>

                SSLEngine on
                SSLCertificateFile {$crt}
                SSLCertificateKeyFile {$key}
            " . (file_exists($ca) ? "    SSLCertificateChainFile {$ca}\n" : '') . "
                ErrorLog \${APACHE_LOG_DIR}/{$domain}-error.log
                CustomLog \${APACHE_LOG_DIR}/{$domain}-access.log combined
            </VirtualHost>";

        file_put_contents($confPath, $confContent);
        $this->comm->chownFileCommand($confPath);

        // 🚀 Aktifkan site (kecuali jika --no-enable)
        if (!$noEnable) {
            $this->info("🧩 Mengaktifkan konfigurasi site...");
            exec("sudo a2ensite {$domain}.conf && sudo systemctl reload apache2", $output, $status);

            if ($status === 0) {
                $this->info("✅ SSL wildcard berhasil diterapkan untuk {$domain}");
            } else {
                $this->warn("⚠️ Site belum aktif. Jalankan manual: sudo a2ensite {$domain}.conf && sudo systemctl reload apache2");
            }
        } else {
            $this->info("⚙️ File konfigurasi dibuat tanpa mengaktifkan site (--no-enable digunakan).");
        }

        return 0;
    }
}
