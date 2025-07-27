<?php

namespace App\View\Components;

use App\Http\Controllers\Helpers\ImageController;
use App\Models\Aplikasi;
use Illuminate\Support\Facades\Route;
use Illuminate\View\Component;

class Sidebar extends Component
{
    public $navigations;
    public $aplikasi;
    public $pengaturan;

    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->aplikasi = new ImageController();
        $this->pengaturan = new Aplikasi();
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render()
    {
        $route = Route::getCurrentRoute();
        if ($route->uri == "/") {
            $active = 'dasbor';
            $activeDropdown = 'dasbor';
        } else {
            $route = Route::getCurrentRoute()->action['as'];
            $arr = explode('.', $route);
            $active = $arr[0];

            $arrDropdown = explode('-', $route);
            $activeDropdown = $arrDropdown[0];
        }

        return view('layouts.navigations.sidebar', [
            'logo' => $this->aplikasi->imageApplication('favicon'),
            'nama_aplikasi' => $this->pengaturan->pengaturan_aplikasi()['nama_aplikasi'],
            'sebutan_kabupaten' => $this->pengaturan->openKab()['sebutan_kabupaten'],
            'sebutan_desa' => $this->pengaturan->pengaturan_aplikasi()['sebutan_desa'],
            'active' => $active,
            'activeDropdown' => $activeDropdown,
        ]);
    }
}
