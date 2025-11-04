<?php

namespace App\View\Components;

use App\Http\Controllers\Helpers\ImageController;
use App\Models\Aplikasi;
use App\Models\Pelanggan;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\Component;

class Navbar extends Component
{
    public $toggle;
    public $aplikasi;

    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct($toggle = true)
    {
        $this->toggle = $toggle ?? true;
        $this->aplikasi = new ImageController();
    }

    public function notifBackup()
    {
        $pelanggan = Pelanggan::first();
        if ($pelanggan) {
            $tglbackup = $pelanggan->tgl_akhir_backup;
            $hariini = date('Y-m-d');
            $selisih = (strtotime($hariini) - strtotime($tglbackup)) / 60 / 60 / 24;

            $notifbackup = true;
            if ($selisih <= 7 || $tglbackup == '') {
                $notifbackup = false;
            }

            return $notifbackup;
        }
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render()
    {
        $aplikasi = Aplikasi::pengaturan_aplikasi();
        $akun_pengguna = 'admin';
        if ($aplikasi['akun_pengguna'] == 1) {
            $akun_pengguna = Auth::user()->name;
        } else if ($aplikasi['akun_pengguna'] == 2) {
            $akun_pengguna = Auth::user()->username;
        } else if ($aplikasi['akun_pengguna'] == 3) {
            $akun_pengguna = Auth::user()->email;
        }

        return view('layouts.navigations.navbar', [
            'foto_pengguna' => $this->aplikasi->imagePengguna('pengguna'),
            'akun_pengguna' => $akun_pengguna,
            'notifbackup' => $this->notifBackup(),
        ]);
    }
}
