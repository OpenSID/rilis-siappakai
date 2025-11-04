<?php

namespace App\Livewire\Pelanggan;

use App\Jobs\MundurVersiJob;
use App\Models\Pelanggan;
use Livewire\Component;

class ModalMundurVersi extends Component
{
    public $data;
    public $versi_opensid;

    public function mundurVersi($data)
    {
        MundurVersiJob::dispatch($data);
    }

    public function mount()
    {
        $this->versi_opensid = env('OPENKAB') == 'true' ? (Pelanggan::first()->versi_opensid ?? '') : $this->data->versi_opensid;
    }

    public function render()
    {
        return view('livewire.pelanggan.modal-mundur-versi', [
            'openkab' => env('OPENKAB'),
        ]);
    }
}
