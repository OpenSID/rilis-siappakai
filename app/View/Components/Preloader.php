<?php

namespace App\View\Components;

use App\Http\Controllers\Helpers\ImageController;
use Illuminate\View\Component;

class Preloader extends Component
{
    public $aplikasi;

    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->aplikasi = new ImageController();
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render()
    {
        return view('layouts.navigations.preloader', [
            'logo_aplikasi' => $this->aplikasi->imageApplication('logo_aplikasi'),
        ]);
    }
}
