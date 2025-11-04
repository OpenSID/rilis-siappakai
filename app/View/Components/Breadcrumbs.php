<?php

namespace App\View\Components;

use Illuminate\View\Component;

class Breadcrumbs extends Component
{
    public $navigations;
    public $active;
    public $link;

    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct($navigations = null, $active = null, $link = null)
    {
        $this->navigations = $navigations ?? "";
        $this->active = $active ?? "";
        $this->link = $link ?? "#";
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render()
    {
        return view('layouts.navigations.breadcrumbs');
    }
}
