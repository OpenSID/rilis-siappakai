<?php

namespace App\Livewire\Pelanggan;

use App\Jobs\AktivasiTemaJob;
use App\Models\Pelanggan;
use App\Models\Tema;
use App\Models\TemaKonfigurasi;
use App\Services\OptionsService;
use Livewire\Component;

class ModalAktivasiTema extends Component
{
    public $data;
    public $tema;

    // field table tema_konfigurasi
    public $tema_id;
    public $aktivasi_tema;
    public $logo;
    public $kode_kota;
    public $fbadmin;
    public $fbappid;
    public $ip_address;
    public $color;
    public $fluid;
    public $menu;
    public $chat;
    public $widget;
    public $style;
    public $hide_layanan;
    public $hide_banner_layanan;
    public $hide_banner_laporan;

    // ditampilkan atau tidak baris pada form table
    public $showAktivasiTema;
    public $showLogo;
    public $showKodeKota;
    public $showFbadmin;
    public $showFbappid;
    public $showIpaddress;
    public $showColor;
    public $showFluid;
    public $showMenu;
    public $showChats;
    public $showWidget;
    public $showStyle;
    public $showHideBannerLayanan;
    public $showHideBannerLaporan;

    // data pilihan
    public $logos;
    public $colors;
    public $fluids;
    public $menus;
    public $konfigurasi_tema;
    public $chats;
    public $widgets;
    public $styles;
    public $hide_banner_layanans;
    public $hide_banner_laporans;

    // pilihan
    public $selectedTema = null;
    public $selectedLogo = null;
    public $selectedColor = null;
    public $selectedFluid = null;
    public $selectedMenu = null;
    public $selectedChat = null;
    public $selectedWidget = null;
    public $selectedStyle = null;
    public $selectedHideBannerLayanan = null;
    public $selectedHideBannerLaporan = null;
    public int $desa;

    public function mount()
    {
        $this->data = Pelanggan::find($this->desa);
        $this->tema = Tema::where('kode_desa', $this->data->kode_desa)->get();

        $options = new OptionsService();
        $this->logos = $options->pilihLogo();
        $this->colors = $options->pilihWarna();
        $this->fluids = $options->pilihNilaiKebenaran();
        $this->menus = $options->pilihNilaiKebenaran();
        $this->chats = $options->pilihNilaiKebenaran();
        $this->widgets = $options->pilihWidget();
        $this->styles = $options->pilihStyle();
        $this->hide_banner_layanans = $options->pilihNilaiKebenaran();
        $this->hide_banner_laporans = $options->pilihNilaiKebenaran();
    }

    public function updatedSelectedTema($pilihtema)
    {
        $pilih = explode("-", $pilihtema);
        $this->tema_id = $pilih[0];

        $this->konfigurasi_tema = TemaKonfigurasi::where('tema_id', $this->tema_id)->first();

        if (!is_null($this->konfigurasi_tema)) {
            $this->tema_id = $this->konfigurasi_tema->tema_id;
            $this->aktivasi_tema = $this->konfigurasi_tema->aktivasi_tema;
            $this->kode_kota = $this->konfigurasi_tema->kode_kota;
            $this->fbadmin = $this->konfigurasi_tema->fbadmin;
            $this->fbappid = $this->konfigurasi_tema->fbappid;
            $this->ip_address = $this->konfigurasi_tema->ip_address;
            $this->logo = $this->konfigurasi_tema->logo;
            $this->color = $this->konfigurasi_tema->color;
            $this->fluid = $this->konfigurasi_tema->fluid;
            $this->menu = $this->konfigurasi_tema->menu;
            $this->style = $this->konfigurasi_tema->style;
            $this->chat = $this->konfigurasi_tema->chats;
            $this->widget = $this->konfigurasi_tema->widget;
            $this->hide_layanan = $this->konfigurasi_tema->hide_layanan;

            $this->selectedLogo = $this->konfigurasi_tema->logo;
            $this->selectedColor = $this->konfigurasi_tema->color;
            $this->selectedFluid = $this->konfigurasi_tema->fluid;
            $this->selectedMenu = $this->konfigurasi_tema->menu;
            $this->selectedChat = $this->konfigurasi_tema->chats;
            $this->selectedWidget = $this->konfigurasi_tema->widget;
            $this->selectedStyle = $this->konfigurasi_tema->style;
            $this->selectedHideBannerLayanan = $this->konfigurasi_tema->hide_banner_layanan;
            $this->selectedHideBannerLaporan = $this->konfigurasi_tema->hide_banner_laporan;
        } else if (is_null($this->konfigurasi_tema)) {
            $this->selectedLogo = '';
            $this->selectedColor = '';
            $this->selectedFluid = '';
            $this->selectedMenu = '';
            $this->selectedChat = '';
            $this->selectedWidget = '';
            $this->selectedStyle = '';
            $this->selectedHideBannerLayanan = '';
            $this->selectedHideBannerLaporan = '';
        } else {
            $this->clearKonfigurasi();
        }

        if(!$pilih[0] == '')
        {
            if ($pilih[1] == 'denatra') {
                $this->showAktivasiTema = true;
                $this->showKodeKota = true;
                $this->showFbadmin = true;
                $this->showFbappid = true;
                $this->showIpaddress = true;
                $this->showLogo = true;
                $this->showColor = true;
                $this->showFluid = true;
                $this->showMenu = true;
                $this->showChats = true;
                $this->showWidget = true;
                $this->showStyle = false;
                $this->showHideBannerLaporan = true;
                $this->showHideBannerLayanan = true;
            } else if ($pilih[1] == 'denava') {
                // tambah model style di atas
                $this->showAktivasiTema = true;
                $this->showKodeKota = true;
                $this->showFbadmin = true;
                $this->showFbappid = true;
                $this->showIpaddress = true;
                $this->showLogo = true;
                $this->showColor = false;
                $this->showFluid = false;
                $this->showMenu = false;
                $this->showChats = true;
                $this->showWidget = true;
                $this->showStyle = true;
                $this->showHideBannerLaporan = true;
                $this->showHideBannerLayanan = true;
            } else if ($pilih[1] == 'batuah') {
                $this->clearTema();
                $this->showKodeKota = true;
            } else {
                $this->clearTema();
            }
        } else {
            $this->clearTema();
        }
    }

    public function clearKonfigurasi()
    {
        $this->tema_id = '';
        $this->aktivasi_tema = '';
        $this->logo = '';
        $this->kode_kota = '';
        $this->fbadmin = '';
        $this->fbappid = '';
        $this->ip_address = '';
        $this->color = '';
        $this->fluid = '';
        $this->menu = '';
        $this->chat = '';
        $this->widget = '';
        $this->styles = '';
        $this->hide_layanan = '';
        $this->hide_banner_layanan = '';
        $this->hide_banner_laporan = '';
    }

    public function clearTema()
    {
        $this->showAktivasiTema = false;
        $this->showLogo = false;
        $this->showKodeKota = false;
        $this->showFbadmin = false;
        $this->showFbappid = false;
        $this->showIpaddress = false;
        $this->showColor = false;
        $this->showFluid = false;
        $this->showMenu = false;
        $this->showChats = false;
        $this->showWidget = false;
        $this->showStyle = false;
        $this->showHideBannerLaporan = false;
        $this->showHideBannerLayanan = false;
    }

    public function updatedSelectedLogo($logo)
    {
        $this->logo = $logo;
    }

    public function updatedSelectedColor($color)
    {
        $this->color = $color;
    }

    public function updatedSelectedFluid($fluid)
    {
        $this->fluid = $fluid;
    }

    public function updatedSelectedMenu($menu)
    {
        $this->menu = $menu;
    }

    public function SimpanPengaturan()
    {
        if (is_null($this->selectedTema)) {
            session()->flash('message-failed', 'Silakan Pilih Tema terlebih dahulu.');
        }

        $konfigurasi = [
            'tema_id' => $this->tema_id,
            'aktivasi_tema' => $this->aktivasi_tema,
            'logo' => $this->logo,
            'kode_kota' => $this->kode_kota,
            'fbadmin' => $this->fbadmin,
            'fbappid' => $this->fbappid,
            'ip_address' => $this->ip_address,
            'color' => $this->color,
            'fluid' => $this->fluid,
            'menu' => $this->menu,
            'chats' => $this->chat,
            'widget' => $this->widget,
            'styles' => $this->style,
            'hide_layanan' => $this->hide_layanan,
            'hide_banner_layanan' => $this->hide_banner_layanan,
            'hide_banner_laporan' => $this->hide_banner_laporan
        ];

        if ($this->konfigurasi_tema) {
            $this->konfigurasi_tema->update($konfigurasi);
            session()->flash('message-success', 'Pengaturan Konfigurasi Tema.');
        } else {
            TemaKonfigurasi::create($konfigurasi);
            session()->flash('message-success', 'Pengaturan Konfigurasi Tema.');
        }

        $konfigurasi['kode_desa'] = $this->data->kode_desa;
        $konfigurasi['token_premium'] = $this->data->token_premium;
        AktivasiTemaJob::dispatch($konfigurasi);
    }    

    public function render()
    {
        return view('livewire.pelanggan.modal-aktivasi-tema');
    }
}
