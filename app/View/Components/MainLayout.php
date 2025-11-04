<?php

namespace App\View\Components;

use App\Http\Controllers\Helpers\ImageController;
use Illuminate\View\Component;

class MainLayout extends Component
{
    public $title;
    public $aplikasi;

    public $styles = null;
    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct($title = null)
    {
        $this->title = $title ?? "Dasbor | " . env('APP_NAME');
        $this->aplikasi = new ImageController();
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render()
    {
        return view('layouts.main', [
            'favicon' => $this->aplikasi->imageApplication('favicon'),
        ]);
    }
}
