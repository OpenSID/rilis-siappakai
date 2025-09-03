<?php
// ----------------------------------------------------------------------------
// Konfigurasi aplikasi dalam berkas ini merupakan setting konfigurasi tambahan
// SID. Letakkan setting konfigurasi ini di desa/config/config.php.
// ----------------------------------------------------------------------------

/*
	Uncomment jika situs ini untuk demo. Pada demo, user admin tidak bisa dihapus
	dan username/password tidak bisa diubah
*/
// $config['demo_mode'] = true;

// Setting ini untuk menentukan user yang dipercaya. User dengan id di setting ini
// dapat membuat artikel berisi video yang aktif ditampilkan di Web.
// Misalnya, ganti dengan id = 1 jika ingin membuat pengguna admin sebagai pengguna terpecaya.
$config['user_admin'] = 0;
$config['server_layanan'] = '{$server_layanan}';
$config['token_layanan'] = '{$token_premium}';
$config['kode_desa'] =  '{$kodedesa}';
$config['web_theme'] = '{$web_theme}';

// Ijinkan agar bisa melakukan impor data penduduk dari OpenKAB
$config['impor_massal'] = false;

// config email
$config['protocol']  = '{$smtp_protocol}';  // mail   mail, sendmail, or smtp The mail sending protocol.
$config['smtp_host'] = '{$smtp_host}';      // SMPT Server Address.
$config['smtp_user'] = '{$smtp_user}';      // SMPT Username.
$config['smtp_pass'] = '{$smtp_pass}';      // SMPT Password.
$config['smtp_port'] = '{$smtp_port}';      // SMTP Port.

// config untuk path tema, perlu disesuaikan dengan kondisi pada server
$config['theme_path'] = '../../multisite/{$kodedesa}/'; // baca dari lokasi file code asli, bukan code symlink`

// tingkatan untuk level pengaturan
$config['tingkatan_database'] = '{$tingkatan_database}';
