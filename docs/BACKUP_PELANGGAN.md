# Dokumentasi Fitur Backup Database Pelanggan

Fitur ini memungkinkan administrator untuk melakukan backup database masing-masing pelanggan secara mandiri melalui antarmuka dasbor. Hasil backup akan dikompresi dan dikirimkan ke Telegram. Jika ukuran file melebihi batas unggah Telegram (50MB), sistem akan otomatis mengunggahnya ke **Google Drive via rclone** dan mengirimkan tautan unduhan ke Telegram.

## Persyaratan Sistem

Untuk menjalankan fitur ini dengan benar, pastikan hal-hal berikut telah terpenuhi:
1. **Bot Telegram**: Token bot dan Chat ID grup/tujuan.
2. **rclone**: Terinstall di server dan `RCLONE_REFRESH_TOKEN` sudah dikonfigurasi di `.env`.
3. **Library PHP**: `guzzlehttp/guzzle`, `firebase/php-jwt`, dan `spatie/db-dumper`.

## Konfigurasi Env (.env)

Tambahkan atau sesuaikan variabel berikut di file `.env` Anda:

```env
# Konfigurasi Telegram
TELEGRAM_BOT_TOKEN=your_bot_token_here
TELEGRAM_CHAT_ID=your_chat_id_here
TELEGRAM_MESSAGE_THREAD_ID=      # Opsional (untuk subtopik forum grup)
TELEGRAM_MAX_UPLOAD_SIZE=50      # Default 50MB

# Konfigurasi rclone (Google Drive Fallback)
RCLONE_REMOTE_NAME=gdrive
RCLONE_FOLDER=dasbor-siappakai-backup
RCLONE_CLIENT_ID=                # Opsional, bisa kosong (pakai built-in)
RCLONE_CLIENT_SECRET=            # Opsional
RCLONE_REFRESH_TOKEN=            # Wajib diisi, lihat panduan di bawah

# Jalur Penyimpanan Sementara & Retensi
BACKUP_STORAGE_PATH=storage/app/backups
BACKUP_RETENTION_DAYS=7

# Enkripsi Opsional
BACKUP_PASSWORD=                 # Jika diisi, file output .zip terenkripsi
```

## Cara Penggunaan

### 1. Persiapan Infrastruktur
Fitur ini berjalan secara asynchronous menggunakan **Laravel Queue**. Pastikan antrean (queue worker) berjalan di server Anda:

```bash
php artisan queue:work
```

Untuk skala produksi, disarankan menggunakan **Supervisor** untuk menjaga agar queue worker tetap berjalan.

### 2. Antarmuka Dasbor
1. Buka menu **Data Pelanggan**.
2. Cari pelanggan yang ingin di-backup pada tabel.
3. Klik tombol **Backup** (ikon database) pada kolom Aksi.
4. Muncul dialog konfirmasi, klik **Ya, Backup!**.
5. Sistem akan mengirimkan pekerjaan ke antrean. Anda akan menerima notifikasi di Telegram setelah proses selesai.

### 3. Pemantauan (Monitoring)
```bash
tail -f storage/logs/laravel.log
```

## Alur Teknis (Technical Flow)

1. **Trigger**: Controller menerima permintaan dan mengirimkan `BackupPelangganJob` ke antrean.
2. **Cleanup Awal**: File `.sql` sampah dari proses sebelumnya yang gagal dihapus (> 5 menit lama).
3. **Dumping**: `BackupPelangganService` dump database via `spatie/db-dumper`. Nama file mengikuti nama database (`db_<kode_desa>` atau `db_gabungan_<langganan>`).
4. **Compression & Enkripsi**:
    - Jika `BACKUP_PASSWORD` diisi → kompresi ke **`.zip`** terenkripsi.
    - Jika tidak → kompresi ke **`.tar.gz`**.
5. **Delivery**:
    - Jika ukuran **≤ 50MB** → dikirim langsung via Telegram (`sendDocument`).
    - Jika ukuran **> 50MB** → diunggah ke Google Drive menggunakan `rclone copy`, lalu tautan bagikan dikirim via Telegram.
6. **Cleanup Akhir (finally)**: File `.sql` sementara **selalu** dihapus setelah proses, baik sukses maupun gagal. File `.zip`/`.tar.gz` lama dihapus sesuai `BACKUP_RETENTION_DAYS`.

## Komponen Terkait

- **Service**:
    - `App\Services\TelegramService`: Integrasi Bot API Telegram.
    - `App\Services\RcloneService`: Upload ke Google Drive via rclone shell command.
    - `App\Services\BackupPelangganService`: Orkestrator utama proses backup.
    - `App\Services\TarGzService`: Kompresi file ke format `.tar.gz`.
    - `App\Services\BackupZipService`: Kompresi & enkripsi ke format `.zip`.
- **Job**:
    - `App\Jobs\BackupPelangganJob`: Menjalankan logic service secara asynchronous.
- **Konfigurasi**:
    - `config/backup_custom.php`: Memetakan variabel `.env` ke konfigurasi Laravel.

## Panduan Persiapan Kredensial

### 1. Mendapatkan Token Bot Telegram
1. Cari **@BotFather** di Telegram.
2. Kirim perintah `/newbot` dan ikuti instruksi.
3. Masukkan **API Token** yang diberikan ke `TELEGRAM_BOT_TOKEN`.

### 2. Mendapatkan Chat ID Telegram
1. Cari bot **@userinfobot** di Telegram dan kirim pesan.
2. ID pribadi akan dibalas oleh bot (contoh: `123456789`).
3. Untuk **Grup**: tambahkan bot ke grup, ID grup biasanya diawali tanda minus (contoh: `-100280941396`).
4. Masukkan ke `TELEGRAM_CHAT_ID`.
5. **Khusus Subtopik Grup (Topics)**: Jika grup Anda memiliki fitur Forum, Anda bisa mengirim spesifik ke topik tertentu dengan menambahkan ID topik ke `TELEGRAM_MESSAGE_THREAD_ID`. (Cara dapat ID: Salin tautan salah satu pesan di dalam topik tersebut, angka di tengah adalah Thread ID. Contoh: `https://t.me/c/123.../45/67` -> Thread ID = `45`).

### 3. Mengatur Google Drive via rclone

Pendekatan ini menggunakan **akun Google pribadi** (bukan Service Account), sehingga tidak ada batasan kuota storage.

#### Langkah A: Install rclone di server
```bash
curl https://rclone.org/install.sh | sudo bash
```

#### Langkah B: Dapatkan Refresh Token di Laptop Lokal
Buka Terminal/Command Prompt di komputer yang Anda gunakan saat ini (pastikan rclone sudah terinstall di laptop Anda juga).

```bash
rclone config
```

Ikuti panduan berikut persis urutannya:
1. `n/s/q>` Ketik **`n`** (New remote)
2. `name>` Ketik **`gdrive`** (atau nama apa saja)
3. `Storage>` Ketik **`drive`**
4. `client_id>` **Tekan Enter** (Kosongkan)
5. `client_secret>` **Tekan Enter** (Kosongkan)
6. `scope>` Ketik **`1`** (Full access)
7. `service_account_file>` **Tekan Enter** (Kosongkan)
8. `Edit advanced config? (y/n)>` Ketik **`n`**
9. `Use auto config? (y/n)>` Ketik **`y`**

Browser Anda akan terbuka. Selesaikan login dengan akun Google Anda dan beri izin ke aplikasi rclone. Setelah muncul pesan "Success" di browser, kembali ke terminal.

10. `Configure this as a team drive? (y/n)>` Ketik **`n`**
11. `y/e/d>` Ketik **`y`** (Yes, this is OK)
12. `e/n/d/r/c/s/q>` Ketik **`q`** (Quit config)

Setelah selesai, salin refresh token dengan melihat file konfigurasi:
```bash
cat ~/.config/rclone/rclone.conf
# (Di Windows: %APPDATA%\rclone\rclone.conf)
```

Anda akan melihat teks panjang di bagian `token = {...}`. Salin nilai yang ada di dalam `"refresh_token":"Teks_Panjang_Ini"`. Jangan ikutkan tanda kutipnya. Masukkan nilai token tersebut ke `RCLONE_REFRESH_TOKEN` di `.env` server.

#### Langkah C: Buat folder tujuan di Google Drive
1. Buka [Google Drive](https://drive.google.com) Anda.
2. Buat folder baru (contoh: `dasbor-siappakai-backup`).
3. Isi nama folder tersebut ke `RCLONE_FOLDER` di `.env`.

#### Langkah D: Test koneksi dari server
```bash
rclone lsd gdrive:
# Harusnya tampil daftar folder dari Google Drive Anda

# Test upload via artisan:
cd /var/www/html/dasbor-siappakai
php artisan tinker
>>> $svc = app(App\Services\RcloneService::class);
>>> $link = $svc->uploadFile('/tmp/test.txt');
>>> dump($link);
```

## Fitur & Status

| Fitur | Status | Keterangan |
|-------|--------|------------|
| **Kompresi tar.gz** | ✅ Aktif | Mengurangi ukuran file hingga 80-90% |
| **Enkripsi ZIP** | ✅ Aktif | Aktif jika `BACKUP_PASSWORD` diisi |
| **Upload Google Drive** | ✅ Aktif | Via rclone (akun pribadi, tidak perlu Shared Drive) |
| **Cleanup file .sql** | ✅ Aktif | Selalu dihapus via blok `finally`, meski terjadi error |
| **Retensi Otomatis** | ✅ Aktif | Hapus file lama sesuai `BACKUP_RETENTION_DAYS` |
| **Retry Otomatis** | ✅ Aktif | Menggunakan fitur retry bawaan Laravel Queue |
| **Nama file dari DB name** | ✅ Aktif | Contoh: `db_5309122002_20260303_232823.zip` |

## Fitur Keamanan & Pemeliharaan

### 1. Enkripsi Backup (ZIP dengan Password)
Jika `BACKUP_PASSWORD` diisi di `.env`, file backup akan dibuat dalam format `.zip` terenkripsi.

**Cara Membuka:** Klik dua kali file `.zip`, masukkan password. Kompatibel dengan Windows Explorer, WinRAR, 7-Zip, dll.

### 2. Retensi Otomatis
Sistem memeriksa folder backup setiap kali proses selesai dan menghapus file yang umurnya melebihi `BACKUP_RETENTION_DAYS` (default: 7 hari).

### 3. Troubleshooting rclone

| Error | Kemungkinan Penyebab | Solusi |
|---|---|---|
| `RCLONE_REFRESH_TOKEN belum dikonfigurasi` | `.env` kosong | Isi `RCLONE_REFRESH_TOKEN` |
| `rclone copy gagal (exit 1)` | Token expire atau salah | Ulangi `rclone config` di laptop, salin token baru |
| `rclone: command not found` | rclone belum terinstall | Jalankan `curl https://rclone.org/install.sh \| sudo bash` |
| File tidak muncul di Drive | `RCLONE_FOLDER` salah | Pastikan nama folder sama persis dengan yang ada di Drive |

---
*Catatan: Pastikan queue worker selalu berjalan agar fitur ini dapat memproses backup di latar belakang tanpa mengganggu kinerja dasbor.*
