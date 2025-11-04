<?php

namespace App\Livewire\Pelanggan;

use App\Jobs\MundurVersiJob;
use Livewire\Component;

class ModalMasal extends Component
{
    public function mundurVersi($data)
    {
        foreach ($data as $dt) {
            MundurVersiJob::dispatch(['kode_desa' => $dt]);
        }
    }

    public function render()
    {
        return view('livewire.pelanggan.modal-masal');
    }
}
