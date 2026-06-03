# Dokumentasi Perintah Update Tema

Perintah `siappakai:update-tema` digunakan untuk memperbarui tema (baik versi Pro maupun Gratis) yang terpasang di Dasbor SiapPakai dengan versi terbaru dari layanan OpenDesa.

## Deskripsi
Perintah ini mengotomatiskan proses pengecekan, pengunduhan, dan pemasangan pembaruan tema. Sistem akan mencocokkan tema lokal dengan daftar tema yang tersedia di server layanan OpenDesa, mengunduh arsip ZIP jika ada pembaruan, dan mengganti direktori tema lama dengan yang baru.

## Prasyarat
Agar perintah ini dapat berjalan, pastikan konfigurasi berikut telah diatur di file `.env`:

```env
SERVER_LAYANAN=https://layanan.opendesa.id
LAYANAN_THEME_API_ENDPOINT=/api/v1/themes
LAYANAN_THEME_API_TOKEN=token_akses_anda
```

- **SERVER_LAYANAN**: URL dasar layanan OpenDesa.
- **LAYANAN_THEME_API_ENDPOINT**: Path API untuk mengambil daftar tema.
- **LAYANAN_THEME_API_TOKEN**: Bearer token untuk autentikasi ke API.

## Cara Penggunaan
Jalankan perintah berikut melalui terminal di root direktori proyek:

```bash
php artisan siappakai:update-tema
```

### 3. Pengaturan Izin (Permissions)
Pastikan nilai `permission` pada tabel `pengaturan_aplikasi` sudah sesuai dengan user web server Anda (contoh: `www-data`). Jika nilai ini salah (misal hanya `www`), perintah `chown` akan gagal.

## Alur Kerja (Workflow)
1. **Fetch Themes**: Mengambil metadata tema terbaru dari API Layanan OpenDesa.
2. **Matching**: Mencocokkan folder tema lokal dengan data server menggunakan slug/alias yang dinormalisasi. **Penting**: Hanya tema yang terdaftar di tabel `pengaturan_temas` lokal yang akan diproses untuk update.
3. **Download**: Mengunduh arsip tema dalam format `.zip` ke folder sementara.
4. **Smart Extract**: Mengekstrak ZIP dan secara otomatis mendeteksi folder sumber tema di dalamnya (mendukung struktur folder bersarang hingga 2 level).
5. **Atomic Replace**: 
    - Menghapus direktori tema lama secara permanen.
    - Memindahkan konten baru ke direktori tema tujuan.
6. **Permissions**: Mengatur ulang kepemilikan folder tema yang diperbarui secara otomatis menggunakan `sudo chown` berdasarkan nilai `permission` di database.
7. **Cleanup**: Menghapus file sementara hasil proses update.

## Automasi Pengujian (Testing)
Telah disediakan *feature test* untuk memverifikasi alur ini tanpa mengubah data asli:
```bash
php artisan test tests/Feature/UpdateTemaTest.php
```
Test ini menggunakan *mocking* untuk API marketplace dan perintah sistem, sehingga aman dijalankan di lingkungan pengembangan mana pun.

## Struktur Kode Utama
File: `app/Console/Commands/UpdateTema.php`

- `handle()`: Titik masuk utama perintah.
- `fetchMarketplaceThemes()`: Mengambil data dari API layanan.
- `updateThemeCollection()`: Iterasi pada koleksi tema Pro dan Gratis.
- `updateTemaFromMarketplace()`: Logika inti pengunduhan dan penggantian direktori.
- `isMatchingTheme()`: Logika pencocokan ketat antara data lokal dan marketplace.
- `resolveExtractedSource()`: Mendeteksi lokasi source code asli di dalam ZIP hasil ekstrak.

## FAQ (Pertanyaan yang Sering Diajukan)

### Q: Mengapa tema tidak terdeteksi untuk diupdate padahal ada di marketplace?
A: Pastikan **Alias** atau **Nama** tema di marketplace sesuai dengan kolom `tema` di tabel `pengaturan_temas` Anda. Jika tidak ada kecocokan persis, sistem akan melewati tema tersebut. 

### Q: Mengapa muncul error `chown: invalid user: 'www:www'`?
A: Periksa tabel `pengaturan_aplikasi` untuk kunci `permission`. Pastikan isinya adalah `www-data` (atau user yang valid di OS Anda), bukan hanya `www`.

### Q: Bagaimana jika saya melihat error "Permission Denied" saat migrasi database?
A: Migrasi `UpdateHtaccess` telah diperbarui untuk menggunakan `sudo tee`. Jika masih terkendala, pastikan user sistem Anda memiliki izin `sudo` yang cukup.

### Q: Apakah pembaruan tema ini juga mengupdate database?
A: Tidak. Perintah ini hanya merefresh kode sumber (source code) tema di folder `master-tema-*`. Perubahan pada tabel `pengaturan_temas` harus dilakukan secara manual atau melalui fitur pengaturan di dasbor.
