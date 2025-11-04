<?php

namespace App\View\Components;

use Illuminate\View\Component;

class Information extends Component
{
    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render()
    {
        return view('layouts.navigations.information', [
            'informations' => $this->Information()
        ]);
    }

    public function Information()
    {
        return array(
            ["id" => "heading1", "target" => "collapse1", "show" => "show", "collapsed" => "", "title" => "Tentang Dasbor SiapPakai", "description" => "Dasbor SiapPakai merupakan salah satu layanan dari OpenDesa, dimana pelanggan yang menggunakan layanan ini tidak perlu direpotkan dengan bagaimana instalasi dan pembaruan versi setiap bulannya pada aplikasi OpenSID premium, OpenSID API, dan Aplikasi PBB.", "link" => "https://panduan.opendesa.id/id/dasbor-siappakai"],
            ["id" => "heading2", "target" => "collapse2", "show" => "", "collapsed" => "collapsed", "title" => "Instalasi", "description" => "Dasbor SiapPakai merupakan aplikasi yang bertujuan untuk mempermudah pengelolaan untuk beberapa desa dalam satu VPS. Adapun yang perlu disiapkan dalam setiap VPS diantaranya instalasi.", "link" => "https://panduan.opendesa.id/id/dasbor-siappakai/instalasi"],
            ["id" => "heading3", "target" => "collapse3", "show" => "", "collapsed" => "collapsed", "title" => "Memperbarui", "description" => "Dasbor SiapPakai senantiasa dikembangkan berdasarkan perkembangan yang dibutuhkan, dan tidak menutup kemungkinan disetiap minggunya ada perubahan fitur baru atau perbaikan (bug), sehingga perlu adanya pembaruan Dasbor SiapPakai agar tidak tertinggal dari versi yang terus dikembangkan.", "link" => "https://panduan.opendesa.id/id/dasbor-siappakai/memperbarui"],
            ["id" => "heading4", "target" => "collapse4", "show" => "", "collapsed" => "collapsed", "title" => "Rilis", "description" => "Catatan rilis merupakan catatan dimana pada rilis tersebut terdapat fitur apa saja yang ditambahkan atau perbaikan-perbaikan (bug) apa saja yang dilakukan baik secara fungsi maupun teknis. Catatan rilis dapat dilihat pada `catatan_rilis.md` yang ada pada Dasbor SiapPakai.", "link" => "https://panduan.opendesa.id/id/dasbor-siappakai/rilis"],
            ["id" => "heading5", "target" => "collapse5", "show" => "", "collapsed" => "collapsed", "title" => "Halaman Administrasi", "description" => "Halaman administrasi yang pertama kali ditampilkan atau diakses adalah Dasbor yang terdapat beberapa menu-menu dan informasi pada Dasbor SiapPakai.", "link" => "https://panduan.opendesa.id/id/dasbor-siappakai/halaman-administrasi"],
            ["id" => "heading6", "target" => "collapse6", "show" => "", "collapsed" => "collapsed", "title" => "Dokumentasi", "description" => "Dokumentasi ini dibuat sebagai panduan bagi pengembang atau programmer maupun tim pelaksana untuk mengetahui proses pengembangan Dasbor SiapPakai mulai dari awal dibuatnya aplikasi sampai terus dikembangkan menjadi lebih baik dengan berbagai fiturnya.", "link" => "https://panduan.opendesa.id/id/dasbor-siappakai/dokumentasi"],
        );
    }
}
