<?php
// ----------------------------------------------------------------------------
// Konfigurasi aplikasi dalam berkas ini merupakan setting konfigurasi tambahan
// SID. Letakkan setting konfigurasi ini di desa/config/config.php.
// ----------------------------------------------------------------------------

/*
	Uncomment jika situs ini untuk demo. Pada demo, user admin tidak bisa dihapus
	dan username/password tidak bisa diubah
*/
// $config['demo_mode'] = 'y';

// Setting ini untuk menentukan user yang dipercaya. User dengan id di setting ini
// dapat membuat artikel berisi video yang aktif ditampilkan di Web.
// Misalnya, ganti dengan id = 1 jika ingin membuat pengguna admin sebagai pengguna terpecaya.
$config['user_admin'] = 0;
$config['server_layanan'] = '{$server_layanan}';
$config['token_layanan'] = '{$token_premium}';
$config['kode_desa'] =  '{$kodedesa}';
$config['web_theme'] = '{$web_theme}';

/*
	Token untuk mengakses TrackSID mengambil data wilayah
*/
$config['token_tracksid'] = "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpZCI6bnVsbCwidGltZXN0YW1wIjoxNjAzNDY2MjM5fQ.HVCNnMLokF2tgHwjQhSIYo6-2GNXB4-Kf28FSIeXnZw";

/** Aktivasi Tema Pro */
$config['DeNava'] = '{$aktivasi_tema}';
$config['DeNatra'] = '{$aktivasi_tema}';
$config['logo'] =  '{$config_logo}';
$config['kode_kota'] =  '{$config_kode_kota}';
$config['fbadmin'] =  '{$config_fbadmin}';
$config['fbappid'] =  '{$config_fbappid}';
$config['ip_address'] =  '{$config_ip_address}';
$config['color'] =  '{$config_color}';
$config['fluid'] =  '{$config_fluid}';
$config['menu'] =  '{$config_menu}';
$config['chats'] =  '{$config_chats}';
$config['widget'] =  '{$config_widget}';
$config['style'] =  '{$config_style}';
$config['hide_layanan'] =  '{$config_hide_layanan}';
$config['hide_banner_laporan'] =  '{$config_hide_banner_laporan}';
$config['hide_banner_layanan'] =  '{$config_hide_banner_layanan}';

// Ijinkan agar bisa melakukan impor data penduduk dari OpenKAB
$config['impor_massal'] = false;

// config email
$config['protocol']  = '{$smtp_protocol}';  // mail   mail, sendmail, or smtp The mail sending protocol.
$config['smtp_host'] = '{$smtp_host}';      // SMPT Server Address.
$config['smtp_user'] = '{$smtp_user}';      // SMPT Username.
$config['smtp_pass'] = '{$smtp_pass}';      // SMPT Password.
$config['smtp_port'] = '{$smtp_port}';      // SMTP Port.
