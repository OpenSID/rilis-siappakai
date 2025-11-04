# Manajemen Sertifikat SSL dengan Instalasi Otomatis acme.sh

Dokumen ini menjelaskan sistem manajemen sertifikat SSL yang telah ditingkatkan untuk OpenLiteSpeed dengan kemampuan instalasi otomatis acme.sh.

## Gambaran Umum

Sistem sekarang mencakup deteksi dan instalasi otomatis acme.sh, memastikan manajemen sertifikat SSL bekerja dengan lancar tanpa intervensi manual.

## Fitur Baru

### Instalasi Otomatis acme.sh
- **Deteksi otomatis**: Sistem memeriksa apakah acme.sh sudah terinstal sebelum melakukan operasi SSL
- **Instalasi otomatis**: Mengunduh dan menginstal acme.sh jika tidak ditemukan
- **Metode cadangan**: Beberapa metode unduhan (wget, curl) untuk keandalan
- **Konfigurasi**: Pengaturan otomatis dengan Let's Encrypt sebagai CA default

### Perintah yang Ditingkatkan

#### 1. Perintah Install acme.sh
```bash
# Install acme.sh dengan email
php artisan ssl:install-acme --email=admin@example.com

# Hanya cek status instalasi
php artisan ssl:install-acme --check

# Paksa install ulang
php artisan ssl:install-acme --force --email=admin@example.com

# Instalasi interaktif (meminta input email)
php artisan ssl:install-acme
```

#### 2. Manajemen SSL yang Ditingkatkan
```bash
# Generate sertifikat (akan auto-install acme.sh jika diperlukan)
php artisan ssl:manage generate example.com --email=admin@example.com

# Cek status sertifikat
php artisan ssl:manage check example.com

# Perbarui sertifikat
php artisan ssl:manage renew example.com

# Hapus sertifikat
php artisan ssl:manage remove example.com
```

## Konfigurasi

### Variabel Environment
```env
# konfigurasi acme.sh
ACME_PATH=/root/.acme.sh/acme.sh
SSL_DEFAULT_EMAIL=admin@example.com
SSL_CA_SERVER=letsencrypt
SSL_STAGING=false

# path SSL OpenLiteSpeed
SSL_CERT_DIRECTORY=/usr/local/lsws/conf/cert
OLS_ADMIN_PASSWORD=your_admin_password
```

### Update File Konfigurasi
File `config/siappakai.php` termasuk konfigurasi SSL:

```php
'ssl' => [
    'acme_path' => env('ACME_PATH', '/root/.acme.sh/acme.sh'),
    'default_email' => env('SSL_DEFAULT_EMAIL'),
    'ca_server' => env('SSL_CA_SERVER', 'letsencrypt'),
    'staging' => env('SSL_STAGING', false),
    'cert_directory' => env('SSL_CERT_DIRECTORY', '/usr/local/lsws/conf/cert'),
    'admin_password' => env('OLS_ADMIN_PASSWORD'),
],
```

## Proses Instalasi

### Alur Instalasi Otomatis
1. **Deteksi**: Periksa apakah acme.sh ada di path yang dikonfigurasi
2. **Unduh**: Gunakan wget atau curl untuk mengunduh script instalasi
3. **Instalasi**: Jalankan instalasi dengan email yang ditentukan
4. **Konfigurasi**: Set CA default ke Let's Encrypt
5. **Verifikasi**: Konfirmasi kesuksesan instalasi

### Metode Instalasi
Sistem mencoba beberapa metode unduhan:

1. **wget** (pilihan utama): `wget -O- https://get.acme.sh`
2. **curl** (cadangan): `curl https://get.acme.sh`

### Script Instalasi
```bash
# Perintah instalasi yang sebenarnya dijalankan:
curl https://get.acme.sh | sh -s email=your@email.com

# Set CA default setelah instalasi:
~/.acme.sh/acme.sh --set-default-ca --server letsencrypt
```

## Contoh Penggunaan

### 1. Setup Pertama Kali
```bash
# Cek apakah acme.sh sudah terinstal
php artisan ssl:install-acme --check

# Install jika belum ada
php artisan ssl:install-acme --email=admin@example.com

# Verifikasi instalasi
php artisan ssl:install-acme --check
```

### 2. Generate Sertifikat SSL
```bash
# Untuk domain baru (auto-install acme.sh jika diperlukan)
php artisan ssl:manage generate mysite.com --email=admin@example.com

# Sistem akan:
# 1. Cek apakah acme.sh sudah terinstal
# 2. Auto-install jika belum ada
# 3. Generate sertifikat
# 4. Install sertifikat ke OpenLiteSpeed
# 5. Update konfigurasi virtual host
```

### 3. Manajemen Sertifikat
```bash
# Cek status sertifikat
php artisan ssl:manage check mysite.com

# Perbarui sertifikat
php artisan ssl:manage renew mysite.com

# Hapus sertifikat
php artisan ssl:manage remove mysite.com
```

## Integrasi Service

### Method OpenLiteSpeedService

#### Method Instalasi Baru
```php
// Cek dan install acme.sh jika diperlukan
$service->installAcmeIfNeeded(): bool

// Unduh dan install acme.sh
$service->downloadAndInstallAcme(): bool

// Set CA default
$service->setDefaultCA(): bool
```

#### Method SSL yang Ditingkatkan
```php
// Generate sertifikat dengan auto-instalasi
$result = $service->generateSSLCertificate('example.com', 'admin@example.com');

// Cek status sertifikat
$status = $service->checkSSLCertificate('example.com');

// Hapus sertifikat
$success = $service->removeSSLCertificate('example.com');
```

## Penanganan Error

### Kegagalan Instalasi
- **Masalah jaringan**: Beberapa metode unduhan dicoba
- **Error permission**: Pesan error yang jelas dengan solusi
- **Masalah path**: Path instalasi yang dapat dikonfigurasi

### Kegagalan Generate SSL
- **acme.sh hilang**: Upaya instalasi otomatis
- **Validasi domain**: Pesan error yang jelas
- **Instalasi sertifikat**: Rollback saat gagal

## Logging

Semua operasi SSL dicatat ke log default Laravel:

```php
// Contoh entri log
[2024-01-01 10:00:00] INFO: Menginstal acme.sh untuk manajemen sertifikat SSL
[2024-01-01 10:00:05] INFO: acme.sh berhasil diinstal di /root/.acme.sh/acme.sh
[2024-01-01 10:00:10] INFO: Menggenerate sertifikat SSL untuk example.com
[2024-01-01 10:00:30] INFO: Sertifikat SSL berhasil digenerate dan diinstal
```

## Troubleshooting

### Masalah Umum

#### 1. Instalasi acme.sh Gagal
```bash
# Cek konektivitas jaringan
curl -I https://get.acme.sh

# Instalasi manual
curl https://get.acme.sh | sh -s email=your@email.com

# Verifikasi instalasi
ls -la /root/.acme.sh/acme.sh
```

#### 2. Error Permission
```bash
# Pastikan script dapat dieksekusi
chmod +x /root/.acme.sh/acme.sh

# Cek ownership
chown root:root /root/.acme.sh/acme.sh
```

#### 3. Generate Sertifikat Gagal
```bash
# Cek resolusi DNS
nslookup your-domain.com

# Test acme.sh secara manual
/root/.acme.sh/acme.sh --issue -d your-domain.com --webroot /path/to/webroot

# Cek log OpenLiteSpeed
tail -f /usr/local/lsws/logs/error.log
```

## Pertimbangan Keamanan

### Permission File
- acme.sh executable: `755`
- File sertifikat: `644`
- Private key: `600`
- Direktori sertifikat: `755`

### Keamanan Jaringan
- Unduhan dari repository resmi acme.sh
- Verifikasi HTTPS diaktifkan
- Validasi email untuk registrasi

### Kontrol Akses
- Hanya user root yang dapat menginstal acme.sh
- Manajemen sertifikat memerlukan permission yang sesuai
- Password admin diperlukan untuk operasi OpenLiteSpeed

## Best Practices

### 1. Konfigurasi Email
- Gunakan alamat email yang valid untuk notifikasi Let's Encrypt
- Konfigurasikan email default di file environment
- Monitor email notifikasi kadaluwarsa sertifikat

### 2. Environment Staging
- Gunakan staging CA untuk testing: `SSL_STAGING=true`
- Test generate sertifikat sebelum produksi
- Verifikasi kepemilikan domain dan konfigurasi DNS

### 3. Monitoring
- Cek kadaluwarsa sertifikat secara berkala
- Setup renewal otomatis untuk sertifikat produksi
- Monitor log acme.sh untuk masalah

### 4. Backup
- Backup file sertifikat sebelum renewal
- Simpan salinan private key dengan aman
- Dokumentasikan proses instalasi sertifikat

## Integrasi dengan Pembuatan Virtual Host

Manajemen SSL terintegrasi dengan mulus dengan pembuatan virtual host:

```php
// Buat virtual host dengan SSL
$service = app(OpenLiteSpeedService::class);

$result = $service->createVirtualHost([
    'domain' => 'example.com',
    'documentRoot' => '/var/www/example.com',
    'enableSSL' => true,
    'sslEmail' => 'admin@example.com'
]);

// Ini akan:
// 1. Buat konfigurasi virtual host
// 2. Cek/install acme.sh jika diperlukan  
// 3. Generate sertifikat SSL
// 4. Konfigurasi HTTPS listener
// 5. Reload konfigurasi OpenLiteSpeed
```

Sistem yang ditingkatkan ini memastikan manajemen sertifikat SSL yang handal dan otomatis untuk OpenLiteSpeed dengan intervensi manual yang minimal.