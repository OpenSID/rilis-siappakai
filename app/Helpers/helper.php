<?php

use App\Models\Aplikasi;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

/** versi dasbor siappakai */
if (!function_exists('siappakai_version')) {
    function siappakai_version()
    {
        return 'v2509.0.3';
    }
}

/** pengecekan tanggal expired */
if (!function_exists('near_expired')) {
    function near_expired($sisa_hari = '')
    {
        if (str_contains($sisa_hari, '-')) {
            return false;
        }
        if ($sisa_hari <= 30) {
            return true;
        }
    }
}

/** pengecekan tanggal akhir backup database dan folder desa */
if (!function_exists('cek_tgl_akhir_backup')) {
    function cek_tgl_akhir_backup($pelanggans)
    {
        if ($pelanggans->first()) {
            $tglbackup = $pelanggans->where('status_langganan_opensid', 1)->first()->tgl_akhir_backup;
            $hariini = date('Y-m-d');
            $selisih = (strtotime($hariini) - strtotime($tglbackup)) / 60 / 60 / 24;

            return $selisih;
        }
    }
}

/** directory storage */
if (!function_exists('siappakai_storage')) {
    function siappakai_storage()
    {
        $folder_backup = env('ROOT_OPENSID') . 'storage';

        if (!file_exists($folder_backup)) {
            exec('mkdir ' . $folder_backup);
        }

        return $folder_backup;
    }
}

/** jumlah directory maksimal backup ke gdrive */
if (!function_exists('max_backup_dir')) {
    function max_backup_dir()
    {
        return Aplikasi::pengaturan_aplikasi()['maksimal_backup'];
    }
}

/** validasi untuk menghilangkan domain http atau https */
if (!function_exists('validasi_domain')) {
    function validasi_domain($domain)
    {
        if (substr($domain, 0, 8) === "https://") {
            $domain = substr($domain, 8);
        } else if (substr($domain, 0, 7) === "http://") {
            $domain = substr($domain, 7);
        }
        return $domain;
    }
}

/** validasi untuk menghilangkan domain menggunakan protokol http dan https serta slish dibelakang
 *
 * @param string $domain
 * @return string
*/
if (!function_exists('formatDomain')) {
    function formatDomain($domain)
    {
        $domain = substr($domain, 0, 4) == "http" ? preg_replace("/http/", "https", rtrim($domain, "/")) : "https://" . rtrim($domain, "/");
        return substr($domain, 9);
    }
}

/** perintah validasi untuk menambahkan domain http atau https */
if (!function_exists('formatUrl')) {
    function formatUrl($domain)
    {
        return substr($domain, 0, 8) == "https://" ? $domain : "https://" . $domain;
    }
}

/** aktifkan backup menggunakan rclone syncs to cloud storage */
if (!function_exists('rclone_syncs_storage')) {
    function rclone_syncs_storage()
    {
        return file_exists('/usr/bin/rclone') ? true : false;
    }
}

/** path_root siappakai */
if (!function_exists('path_root_siappakai')) {
    function path_root_siappakai($root)
    {
        if ($root == 'root_vps') {
            $path = "/var/www/html/";
        } else if ($root == 'root_panel') {
            $path = '/www/wwwroot/';
        }
        return $path;
    }
}

if (!function_exists('unlinkSymlink')) {
    function unlinkSymlink($directory, $symlinkCorrupted = null)
    {
        if (is_link($directory) || $symlinkCorrupted) {
            exec('sudo unlink ' . $directory);
        }
    }
}

/** nama database gabungan aplikasi OpenSID */
if (!function_exists('nama_database_gabungan')) {
    function nama_database_gabungan($opensid = 'premium')
    {
        // diawali dengan nama database `db_`, sehingga menjadi `db_gabungan`
        return 'gabungan_' . Str::lower($opensid);
    }
}


/** nama database gabungan aplikasi OpenSID */
if (!function_exists('to_label')) {
    function to_label($text)
    {
        return   ucwords(str_replace('_', ' ', $text));
    }
}

/** jumlah directory maksimal backup ke gdrive */
if (!function_exists('cek_token_github')) {
    function cek_token_github()
    {
        if(!config('siappakai.git.token')){
            $notif = "Informasi: silakan cek token Github di env ";
            Log::notice($notif);
            return die($notif);
        }
    }
}
