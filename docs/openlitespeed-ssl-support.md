# Dokumentasi Perubahan: Support SSL OpenLiteSpeed (OLS)

Dokumentasi ini menjelaskan perubahan yang dilakukan untuk mendukung pembuatan dan konfigurasi SSL pada server yang menggunakan OpenLiteSpeed (OLS).

## Ringkasan Perubahan

Penambahan logika deteksi panel server dan integrasi dengan `OpenLiteSpeedService` untuk menangani SSL secara otomatis menggunakan `acme.sh`.

### 1. Update SSL Command (`UpdateSsl.php`)

File: [app/Console/Commands/UpdateSsl.php](file:///home/ujgsp/opendesa/dasbor-siappakai-folder/dasbor-siappakai/app/Console/Commands/UpdateSsl.php)

- **Deteksi Panel:** Menambahkan pengecekan `server_panel` dari pengaturan aplikasi. Jika nilai panel adalah `"3"`, maka akan menggunakan logika OLS.
- **Metode Baru `setVhostOpenLiteSpeed`:**
    - Mengecek keberadaan dan validitas sertifikat SSL yang ada.
    - Jika tidak ada atau tidak valid, memicu pembuatan sertifikat baru melalui `OpenLiteSpeedService`.
    - Melakukan reload pada layanan OpenLiteSpeed setelah pembuatan sertifikat berhasil.

### 2. Update Token Premium Command (`UpdateTokenPremium.php`)

File: [app/Console/Commands/UpdateTokenPremium.php](file:///home/ujgsp/opendesa/dasbor-siappakai-folder/dasbor-siappakai/app/Console/Commands/UpdateTokenPremium.php)

- **Konfigurasi Vhost dengan SSL:**
    - Mengupdate metode `updateOpenLiteSpeedVirtualHost` untuk secara otomatis mendeteksi ketersediaan SSL.
    - Parameter `enableSSL` pada `createVirtualHost` sekarang diset berdasarkan hasil pengecekan sertifikat SSL yang nyata.
    - Memberikan informasi pada output console apakah SSL diaktifkan atau tidak pada vhost tersebut.

### 3. Perbaikan Syntax Helper (`helper.php`)

File: [app/Helpers/helper.php](file:///home/ujgsp/opendesa/dasbor-siappakai-folder/dasbor-siappakai/app/Helpers/helper.php)

- Memperbaiki kesalahan syntax (kurung kurawal tutup yang hilang) pada pengecekan fungsi `opensid_version` yang menyebabkan aplikasi error/crash saat dijalankan.

### 4. Update Unit Test

File: [tests/Unit/UpdateTokenPremiumTest.php](file:///home/ujgsp/opendesa/dasbor-siappakai-folder/dasbor-siappakai/tests/Unit/UpdateTokenPremiumTest.php)

- Menambahkan mock untuk `checkSSLCertificate` guna mensimulasikan kondisi SSL aktif.
- Menambahkan verifikasi bahwa `createVirtualHost` dipanggil dengan parameter SSL yang benar.

### 5. Peningkatan Arsitektur (Dependency Injection)

File: [UpdateSsl.php](file:///home/ujgsp/opendesa/dasbor-siappakai-folder/dasbor-siappakai/app/Console/Commands/UpdateSsl.php) & [UpdateTokenPremium.php](file:///home/ujgsp/opendesa/dasbor-siappakai-folder/dasbor-siappakai/app/Console/Commands/UpdateTokenPremium.php)

- **Dependency Injection:** Melakukan refactoring pada constructor command untuk menggunakan Dependency Injection (DI) Laravel. Hal ini meningkatkan *testability* kode dan memungkinkan mocking pada unit/feature testing tanpa memerlukan `alias:` mocks yang brittle.

### 5. Peningkatan pada `OpenLiteSpeedService.php`

File: [app/Services/OpenLiteSpeedService.php](file:///home/ujgsp/opendesa/dasbor-siappakai-folder/dasbor-siappakai/app/Services/OpenLiteSpeedService.php)

- **Sudo Integration:** Menambahkan penggunaan `sudo` pada semua perintah `acme.sh` (issue, renew, install, remove) dan perintah pembuatan direktori. Hal ini memastikan script memiliki izin yang cukup untuk mengakses folder `/root/.acme.sh/` dan `/usr/local/lsws/conf/cert/`.
- **Dynamic Webroot:** Proses verifikasi domain kini menggunakan `documentRoot` dari virtual host masing-masing desa sebagai `webroot` untuk `acme.sh`. Hal ini memastikan validasi domain oleh Let's Encrypt dapat berjalan dengan lancar.
- **Otomatisasi Instalasi:** Service dilengkapi dengan logika untuk mendeteksi ketersediaan `acme.sh` di sistem menggunakan `sudo`. Jika tidak ditemukan, service akan mencoba mengunduh dan menginstallnya secara otomatis ke direktori `/root/.acme.sh/` menggunakan `sudo` sebagai fallback. Hal ini memastikan fitur tetap berjalan meskipun PHP dijalankan oleh user non-root (seperti `appsid` atau `www-data`), selama user tersebut memiliki izin `sudo`.

## Cara Penggunaan

### Update SSL Domain Tertentu
```bash
php artisan siappakai:update-ssl --domain=nama-domain.com
```

### Update Token Premium (Otomatis Update SSL jika ada)
```bash
php artisan siappakai:update-token-premium --kode_desa=12.34.56.7890
```

## Persyaratan Sistem
- Layanan `OpenLiteSpeedService` harus terkonfigurasi dengan benar.
- Tool `acme.sh` dikelola secara otomatis oleh service. `sudo` diperlukan bukan karena batasan `acme.sh`, melainkan agar script dapat menulis sertifikat ke direktori sistem OpenLiteSpeed (`/usr/local/lsws/conf/cert/`) dan mengelola konfigurasi vhost yang dimiliki oleh root. Pastikan user yang menjalankan PHP memiliki izin `sudo` tanpa password untuk perintah-perintah terkait.
