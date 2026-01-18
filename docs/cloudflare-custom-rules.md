# Dokumentasi Modul Cloudflare Custom Rules (WAF)

## 1. Konsep Dasar

### Masalah
Mengelola aturan keamanan (Firewall/WAF) untuk ratusan domain pelanggan secara manual sangat tidak efisien. Jika Anda ingin memblokir satu serangan baru di semua domain pelanggan, Anda harus login dan membuatnya satu per satu.

### Solusi: "Master Rule"
Modul ini bekerja dengan konsep **Master Slave Replication**.
- **Master Rule**: Aturan yang Anda definisikan di sistem ini (Dasbor Siappakai). Ini adalah "sumber kebenaran".
- **Slave Rule**: Aturan yang benar-benar terpasang di Cloudflare milik pelanggan.

Sistem akan memastikan bahwa apa yang ada di **Master** akan diduplikasi (deploy) ke seluruh **Slave** (Domain Pelanggan) secara otomatis.

---

## 2. Mekanisme & Alur Kerja

### A. Persiapan (Prerequisites)
Agar sistem bisa bekerja, domain pelanggan harus memenuhi syarat:
1.  **Akun Terhubung**: Domain harus memiliki relasi ke data `Master Cloudflare` (Akun induk).
2.  **Token Valid**: Token API akun Cloudflare tersebut harus aktif dan memiliki izin **Zone.Firewall Services** dan **Account.Rulesets**.
3.  **DNS Status OK**: Domain harus memiliki `dns_status = 'OK'`, artinya IP domain sudah sesuai dengan IP server yang diharapkan. Sistem tidak akan melakukan deploy ke domain yang belum verified (mencegah error deployment).

### B. Alur Penggunaan (User Flow)

#### Langkah 1: Membuat "Master Rule"
1.  Masuk ke menu **Pengaturan** > **Rule Cloudflare**.
2.  Klik **Tambah**.
3.  Definisikan aturan pada formulir yang tersedia:

    | Field | Penjelasan | Contoh |
    | :--- | :--- | :--- |
    | **Name** | Nama identifikasi aturan. Hanya untuk internal sistem, tidak muncul di error page user. | `Blokir Admin Login` |
    | **Description** | Catatan tambahan mengenai aturan ini. | `Mencegah akses wp-admin dari IP selain kantor` |
    | **Action** | Tindakan yang dilakukan Cloudflare jika aturan terpenuhi. <br> - **Block**: Memblokir total (untuk serangan). <br> - **Managed Challenge**: Tantangan cerdas (rekomendasi untuk bot). <br> - **JS Challenge**: Cek browser tanpa captcha. <br> - **Challenge**: Captcha interaktif. <br> - **Skip**: (Baru) Bypass WAF, Rate Limit, & Custom Rules lain. Gunakan ini untuk **Whitelist**. <br> - **Log**: Hanya mencatat tanpa aksi. | `Block` |
    | **Expression** | Logika filter menggunakan sintaks Wirefilter Cloudflare. Contoh: `(http.request.uri.path eq "/wp-admin" and ip.src ne 1.2.3.4)` | `(http.request.uri.path eq "/login")` |
    | **Priority** | Urutan eksekusi. **Angka Kecil = Prioritas Tinggi**. <br> - **1 - 10**: Gunakan untuk **Whitelist/Skip** (agar dieksekusi duluan sebelum diblokir). <br> - **100+**: Gunakan untuk **Block/Challenge**. | `10` |
    | **Enabled** | Status aktif/non-aktif rule ini. Jika dimatikan, saat deploy rule ini akan dihapus dari Cloudflare. | `ON` |

4.  Simpan. Saat ini, aturan baru **BELUM** terpasang di Cloudflare. Ini baru tersimpan di database lokal "Master".

#### Langkah 2: Deploy (Sinkronisasi)
1.  Di halaman daftar Rule Cloudflare, klik tombol **Deploy Rules**.
2.  Pilih **Mode Deployment** (lihat penjelasan mode di bawah).
3.  Klik **Start Deployment**.

#### Langkah 3: Proses di Belakang Layar (Worker)
Sistem akan menjalankan tugas (Job) di latar belakang untuk setiap domain terdaftar:
1.  **Cek Mapping**: Sistem melihat catatan lokal, "Apakah domain ini sudah punya rule ini sebelumnya?"
2.  **Eksekusi API**:
    *   Jika **Baru**: Sistem membuat rule baru di Cloudflare. ID rule yang didapat dari Cloudflare disimpan di database lokal (Mapping).
    *   Jika **Berubah**: Sistem mengupdate rule yang sudah ada di Cloudflare.
    *   Jika **Dihapus**: Jika Anda menghapus Master Rule, sistem juga akan menghapus rule terkait di Cloudflare.
3.  **Logging**: Hasil sukses/gagal dicatat di log.

---

### Contoh Skenario Prioritas (Penting!)

Bayangkan Anda memiliki 2 aturan yang bertentangan:
1.  **Whitelist Kantor**: Mengizinkan IP Kantor (`1.2.3.4`) mengakses apapun (Action: *Allow*).
2.  **Blokir Admin**: Memblokir akses ke halaman login `/admin` (Action: *Block*).

Bagaimana jika Orang Kantor membuka halaman `/admin`?

**Skenario A (Benar): Whitelist dieksekusi duluan**
*   Rule Whitelist: Priority **10** (Lebih Kecil = Lebih Awal)
*   Rule Blokir: Priority **50**
*   **Alur**: Orang kantor masuk -> Cek Rule 10 (Cocok? Ya) -> **Allow**. Selesai.
*   *Hasil*: Orang kantor **BISA** akses admin.

**Skenario B (Salah): Blokir dieksekusi duluan**
*   Rule Blokir: Priority **10**
*   Rule Whitelist: Priority **50**
*   **Alur**: Orang kantor masuk -> Cek Rule 10 (Cocok? Ya, karena dia akses admin) -> **Block**. Selesai.
*   *Hasil*: Orang kantor **TERBLOKIR** (Rule Whitelist tidak sempat dicek).

**Kesimpulan**: Berikan angka prioritas **KECIL** (misal: 10, 20) untuk rule yang sifatnya "Mengizinkan" (Whitelist/Allow), dan angka **BESAR** (misal: 100, 200) untuk rule yang memblokir.

---

## 3. Penjelasan Mode Deployment

Saat menekan tombol Deploy, Anda diminta memilih mode. Berikut perbedaannya:

| Mode | Penjelasan | Kapan Digunakan? |
| :--- | :--- | :--- |
| **Smart Sync** (Rekomendasi) | Cerdas. Menambah yang baru, update yang berubah, dan menghapus yang sudah tidak ada di Master. | Gunakan ini sehari-hari untuk menjaga sinkronisasi penuh. |
| **Append Only** | Hanya menambah rule baru. Tidak akan mengganggu/mengubah rule yang sudah ada. | Jika Anda takut merusak aturan yang sudah ada dan hanya ingin "menitipkan" aturan baru. |
| **Full Replace** (Hati-hati) | Menghapus SEMUA rule yang dikelola sistem ini di Cloudflare, lalu membuatnya ulang dari nol. | Gunakan hanya jika sinkronisasi macet total atau banyak rule duplikat/error. |

---

## 4. Troubleshooting Error Umum

Berikut adalah pesan error yang sering muncul di Log dan solusinya:

| Pesan Error | Arti | Solusi |
| :--- | :--- | :--- |
| `Cloudflare account not linked` | Data domain di database lokal tidak memiliki induk akun Cloudflare. | Edit data Pelanggan, pilih Akun Cloudflare yang sesuai. |
| `Authentication error (Code: 10000)` | Token API tidak valid atau tidak memiliki izin akses. | Cek menu Master Cloudflare. Pastikan token API akun tersebut valid dan punya permission **Zone.Firewall Services**. |
| `Zone ID is missing` | Data domain belum tersinkronisasi Zone ID-nya. | Jalankan "Sinkron Cloudflare" terlebih dahulu untuk domain tersebut agar mendapatkan Zone ID. |

---

## 5. Arsitektur Teknis (Untuk Developer)

### Database Schema
*   **`cloudflare_rule_masters`**: Definisi rule utama (sumber kebenaran).
*   **`cloudflare_rule_mappings`**: Tabel pivot krusial. Menyimpan relasi `master_id` <-> `customer_domain_id` <-> `cloudflare_rule_id`. Tanpa tabel ini, sistem tidak tahu rule mana di Cloudflare yang milik kita.
*   **`cloudflare_rule_deploy_histories`** & **`_logs`**: Menyimpan audit log deployment.

### Kode Terkait
*   **Controller**: `App\Http\Controllers\Pengaturan\CloudflareRuleMasterController`
*   **Job**: `App\Jobs\DeployCloudflareRulesJob` (Otak utama logika sinkronisasi)
*   **Service**: `App\Services\CloudflareRulesetService` (Wrapper API Rulesets - Modern)
*   **Service (Legacy)**: `App\Services\CloudflareService` (Wrapper API lainnya, tidak digunakan untuk Custom Rules)

### Catatan Teknis
*   Modul ini menggunakan **Cloudflare Rulesets API** (modern).
*   **Action Skip**: Digunakan sebagai pengganti `allow`/`bypass`. Secara default sistem mengkonfigurasi action `skip` untuk me-bypass:
    *   **Managed Rules** (WAF)
    *   **Rate Limiting**
    *   **Remaining Custom Rules** (Whitelist behavior)
*   **Description**: Sistem menggunakan format `Name` (tanpa deskripsi panjang) untuk identifikasi rule di Cloudflare agar lebih bersih.
