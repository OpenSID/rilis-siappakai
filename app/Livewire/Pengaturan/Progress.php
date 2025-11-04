<?php

namespace App\Livewire\Pengaturan;

use App\Models\Aplikasi;
use Livewire\Component;

class Progress extends Component
{
    public $reset;
    public $submit;

    public function Submit()
    {
        session()->flash('message-success', 'Berhasil Menambahkan Pengaturan Wilayah.');
    }

    public function render()
    {
        $pengaturan = new Aplikasi();
        $sebutandesa = $pengaturan->pengaturan_aplikasi()['sebutan_desa'];
        return view('livewire.pengaturan.progress', ['sebutandesa' => $sebutandesa]);
    }
}
