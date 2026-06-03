# Dokumentasi Perubahan Logika Update OpenSID Premium

Dokumen ini menjelaskan perubahan teknis dan fungsional yang dilakukan pada modul update OpenSID di Dasbor Siap Pakai. Perubahan ini bertujuan untuk menyederhanakan manajemen lisensi, menghapus logika usang, dan meningkatkan keandalan sistem melalui penanganan error yang lebih baik serta pengujian otomatis.

## Untuk Issue #106
- [Hapus Alur Opensid Premium versi dibelakang](https://github.com/OpenSID/dasbor-siappakai-folder/dasbor-siappakai/issues/805)

## 1. Latar Belakang

Sebelumnya, sistem memiliki mekanisme "versi premium di belakang" yang memungkinkan pelanggan untuk tetap menggunakan versi premium lama (misal `premium_1` hingga `premium_5`) meskipun lisensi mereka telah berakhir. Mekanisme ini dianggap membebani server (banyak folder master) dan kurang tegas dalam penegakan lisensi.

## 2. Perubahan Teknis Utama

### A. Masa Tenggang Lisensi (180 Hari)
- **File**: `app/Services/PelangganService.php`
- **Perubahan**: Metode `langganan()` menerapkan masa tenggang **180 hari (6 bulan)**. 
- **Logika**:
    - Jika `tgl_akhir_premium` belum melewati 180 hari dari hari ini, pelanggan tetap dianggap sebagai `'premium'`.
    - Jika sudah melewati 180 hari, status akan berubah menjadi `'umum'`.
- **Dampak**: Memberikan fleksibilitas bagi pelanggan sebelum dialihkan sepenuhnya ke versi umum.

### B. Refactor Layanan Update & Penanganan Error
- **File**: `app/Services/OpensidUpdateService.php`
- **Perubahan**:
    - **Penyederhanaan Folder**: Penghapusan rotasi folder master (hanya menggunakan folder `premium` dan `umum`).
    - **Pemisahan Deaktivasi**: Logika deaktifasi website (symlink removal & redirect) telah **dihapus** dari alur update OpenSID. Suspensi hosting kini dikelola secara terpisah.
    - **Mekanisme Revert (Try-Catch)**: Menambahkan blok `try-catch` saat proses update. Sistem akan membuat backup folder master sementara. Jika proses cloning atau update database pelanggan gagal, folder yang bermasalah akan dihapus dan folder backup akan dipulihkan secara otomatis.

### C. Refaktorisasi Perintah Konsol (Console Commands)
- **File**: `UpdateTokenPremium.php`, `UpdateIndex.php`, `UpdatePelanggan.php`, `UpdateApi.php`, `UpdatePbb.php`.
- **Perubahan**: Seluruh perintah ini telah diperbarui untuk menggunakan logika biner (`premium`/`umum`) yang baru. Ketergantungan pada metode lama di `IndexController` telah dihilangkan dan diganti dengan pemanggilan `PelangganService::langganan()`.

### D. Sinkronisasi & Pembersihan
- **Sinkronisasi**: `SinkronkanLayananJob.php` kini menggunakan logika yang sama dari `PelangganService`.
- **Pembersihan**: Menghapus metode `langganan()` dan `langganan_opensid()` yang sudah tidak relevan di `app/Http/Controllers/Helpers/IndexController.php`.

## 3. Sistem Pengujian (Testing)

### A. Unit Test
- **File**: `tests/Unit/PelangganServiceTest.php`
- **Tujuan**: Memastikan batas 180 hari bekerja tepat (H-179 premium, H-181 umum).

### B. Feature Test
- **File**: `tests/Feature/OpensidUpdateTest.php`
- **Tujuan**: Memastikan alur update memperbarui status `langganan_opensid` dengan benar tanpa memicu deaktifasi website.

## 4. Cara Verifikasi Manual

1. Pilih pelanggan premium yang aktif.
2. Ubah `tgl_akhir_premium` menjadi 100 hari yang lalu.
3. Jalankan `php artisan siappakai:update-opensid`.
4. Pastikan status tetap `'premium'`.
5. Ubah `tgl_akhir_premium` menjadi 200 hari yang lalu.
6. Jalankan kembali update; pastikan status berubah menjadi `'umum'`.
7. Pastikan situs **TIDAK** dialihkan ke halaman expired.
