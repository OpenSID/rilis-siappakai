# Dokumentasi Perintah Artisan

Dokumen ini menyediakan daftar lengkap perintah Artisan kustom yang tersedia dalam proyek Dasbor SiapPakai.

## Perintah Inti Siappakai (`siappakai:`)

Perintah-perintah ini digunakan untuk mengelola aplikasi inti Siappakai, termasuk instalasi, pembaruan, dan konfigurasi.

| Signature Perintah | Deskripsi |
| --- | --- |
| `siappakai:backup-database` | Melakukan backup database OpenSID (dapat dilakukan melalui cronjob jika ada rilis terbaru) |
| `siappakai:backup-vps-sftp` | Melakukan backup database dan folder desa ke server vps |
| `siappakai:build-site {--kode_desa=} {--domain_opensid=} {--langganan_opensid=} {--tgl_akhir_premium=} {--token_premium=} {--kode_desa_default=}` | Membuat Web Pelanggan : OpenSID Premium, OpenSID API, Aplikasi PBB |
| `siappakai:check-subscription-expiry {--dry-run}` | Periksa tanggal kadaluarsa langganan dan nonaktifkan situs web yang sudah kadaluarsa |
| `siappakai:config-app {--production=}` | Konfigurasi APP_ENV production dan APP_DEBUG false |
| `siappakai:config-clear` | Menjalankan perintah artisan optimize, config:cache, config:clear, config:clear |
| `siappakai:config-email-api {--kode_desa=} {--mail_host=} {--mail_user=} {--mail_pass=} {--mail_address=}` | Pengaturan Email untuk konfigurasi .env pada OpenSID API |
| `siappakai:config-email-opensid {--kode_desa=} {--smtp_protocol=} {--smtp_host=} {--smtp_user=} {--smtp_pass=} {--smtp_port=} {--token_premium=}` | Pengaturan Email untuk konfigurasi config.php pada OpenSID |
| `siappakai:config-env {--db_host=} {--db_username=} {--db_password=} {--dir_root=}` | Konfigurasi .env.example dan ubah menjadi .env serta konfigurasi index.php |
| `siappakai:config-ftp {--ftp_user=} {--ftp_pass=}` | Konfigurasi FTP Password |
| `siappakai:config-ftp-api` | Konfigurasi FTP untuk OpenSID API |
| `siappakai:config-openkab {--app=}` | Konfigurasi untuk mengaktifkan atau menonaktifkan SiapPakai untuk OpenKab |
| `siappakai:config-token {--token_github=}` | Konfigurasi token github ke .env |
| `siappakai:delete-modul-desa {modul} {--kode_desa=}` | Hapus modul perdesa (tanpa download paket) |
| `siappakai:delete-pelanggan {--kode_desa=} {--domain_opensid=}` | Hapus Pelanggan Dasbor SiapPakai melalui Layanan |
| `siappakai:delete-pelanggan-openkab {--kode_desa=} {--domain_opensid=} {--port_domain=}` | Hapus Pelanggan Dasbor SiapPakai melalui Layanan (OpenKab) |
| `siappakai:delete-siapkai` | Hapus Dasbor SiapPakai sudah habis berlangganan |
| `siappakai:install-api-satudata {--domain=} {--vhost=} {--domain_openkab=}` | Instal Api SatuData |
| `siappakai:install-master {--kode_desa=} {--token_premium=}` | Instal Master Aplikasi : OpenSID Premium, OpenSID API, dan Aplikasi PBB |
| `siappakai:install-master-tema` | Instal Master Tema Pro |
| `siappakai:install-modul-desa {modul} {--kode_desa=} {--pasang_baru}` | Install modul perdesa (tanpa download paket) |
| `siappakai:install-module` | Perbarui Modul Opensid |
| `siappakai:install-openkab {--domain=} {--vhost=} {--domain_api=}` | Instal OpenKab |
| `siappakai:install-siappakai {--domain=} {--vhost=} {--permission=}` | Instal Siapakai, OpenSID Premium, OpenSID Umum, OpenSID API, dan Aplikasi PBB |
| `siappakai:install-ssl {--domain=}` | Instal SSL pada domain tertentu |
| `siappakai:install-tags-version` | Instal OpenSID Premium, OpenSID API, Aplikasi PBB versi 5 bulan sebelumnya |
| `siappakai:install-tema {--tema=} {--kode_desa=} ...` | Instal Tema Pro di desa tertentu berdasarkan kode desa |
| `siappakai:install-tema-bawaan {--tema=} {--kode_desa=}` | Instal Tema Bawaan di desa tertentu berdasarkan kode desa |
| `siappakai:install-tema-premium {--symlink=} {--unlink=}` | Instal Tema Pro di folder desa pada master opensid premium |
| `siappakai:maintenance-setup` | Setup otomatis konfigurasi Mode Pemeliharaan Global |
| `siappakai:master-secure` | Menjalankan job untuk mengamankan semua folder master |
| `siappakai:migrate {--path=}` | Migrasikan OpenSID, OpenSID API, dan Aplikasi PBB melalui terminal |
| `siappakai:mitra {--kode_desa=} {--mitra=} {--all=}` | Menampilkan logo Mitra pada halaman web |
| `siappakai:mundur-versi {--kode_desa=}` | Mundur ke versi sebelumnya |
| `siappakai:pindah-hosting {--kode_desa=}` | Pindah hosting ke Dasbor SiapPakai |
| `siappakai:ssl-check-expiry {--domain=}` | Periksa tanggal kadaluarsa SSL untuk semua domain |
| `siappakai:ssl-wildcard {--domain=} {--app=} {--kodewilayah=}` | Instal SSL Wildcard pada domain tertentu |
| `siappakai:unduh-folder-desa {--directory_from=} {--file=} {--directory=}` | Mengunduh folder desa pada desa tertentu. |
| `siappakai:update-api` | Perbarui versi Api (dapat dilakukan setiap hari melalui cronjob) |
| `siappakai:update-domain {--kode_desa=} {--token_premium=} ...` | Perbarui domain pelanggan |
| `siappakai:update-domain-opendk {--kode_kecamatan=} ...` | Perbarui domain opendk |
| `siappakai:update-host-siappakai {domain} {--conf=}` | Perbarui domain siappakai |
| `siappakai:update-index` | Perbarui index aplikasi pada masing-masing aplikasi |
| `siappakai:update-konfigurasi-tema {--kode_desa=} ...` | Pembaruan Aktivasi dan Konfigurasi Tema Pro |
| `siappakai:update-opendk` | Perbarui versi OpenDK |
| `siappakai:update-opensid` | Perbarui versi premium dan umum |
| `siappakai:update-pbb` | Perbarui versi PBB (dapat dilakukan setiap hari melalui cronjob) |
| `siappakai:update-pelanggan {--kode_desa=} ...` | Perbarui pelanggan premium atau pelanggan Dasbor SiapPakai |
| `siappakai:update-setting-opensid {key} {--kode_desa=}` | Perbarui pengaturan opensid |
| `siappakai:update-siappakai` | Perbarui versi Dasbor SiapPakai |
| `siappakai:update-ssl {--domain=}` | Perbarui SSL pada semua domain yang belum aktif. |
| `siappakai:update-ssl-lets-encrypt` | Perbarui SSL Let's Encrypt pada semua domain yang belum aktif. |
| `siappakai:update-tema` | Perbarui Tema Pro (dapat dilakukan setiap hari melalui cronjob) |
| `siappakai:update-token-masal` | Perbarui data pelanggan melalui token |
| `siappakai:update-token-premium {--kode_desa=}` | Pembaruan Token di desa tertentu berdasarkan kode desa |

## Perintah Multi-tenant & Web Server (`multitenant:`, `ols:`)

Perintah untuk mengelola situs multi-tenant dan Web Server (OpenLiteSpeed/Apache).

| Signature Perintah | Deskripsi |
| --- | --- |
| `multitenant:list-sites` | Tampilkan daftar semua situs multi-tenant OpenSID |
| `multitenant:remove-site {site-uuid}` | Hapus situs multi-tenant OpenSID dan bersihkan semua sumber daya |
| `multitenant:test` | Uji fungsionalitas sistem multi-tenant OpenSID |
| `ols:generate-listeners` | Buat konfigurasi listener OpenLiteSpeed dari vhost yang ada |

## Perintah Manajemen SSL (`ssl:`)

Perintah khusus untuk manajemen sertifikat SSL.

| Signature Perintah | Deskripsi |
| --- | --- |
| `ssl:install-acme` | Instal acme.sh untuk manajemen sertifikat SSL |
| `ssl:manage` | Kelola sertifikat SSL menggunakan acme.sh untuk OpenLiteSpeed |

## Perintah Jaringan & Integrasi (`domains:`, `sentry:`)

Perintah untuk sinkronisasi domain dan integrasi eksternal.

| Signature Perintah | Deskripsi |
| --- | --- |
| `domains:sync` | Sinkronisasi domain pelanggan dengan Cloudflare |
| `sentry:test` | Uji integrasi pelacakan kesalahan Sentry |

## Perintah Utilitas (`recycle-bin:`)

| Signature Perintah | Deskripsi |
| --- | --- |
| `recycle-bin:clean {--days=30}` | Bersihkan file lama dari recycle bin |
| `app:version` | Tampilkan versi aplikasi saat ini |

---
*Catatan: Beberapa perintah memiliki opsi yang luas. Gunakan `php artisan help [nama_perintah]` untuk detail lengkap.*
