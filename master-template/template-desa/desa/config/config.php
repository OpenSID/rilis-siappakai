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
$config['sess_cookie_name'] = 'ci_session_{$kodedesa}'; /* isinya harus unik, tidak boleh ada yang sama dengan desa lain */

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
