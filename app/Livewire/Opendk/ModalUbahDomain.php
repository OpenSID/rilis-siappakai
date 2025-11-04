<?php

namespace App\Livewire\Opendk;

use App\Jobs\MundurVersiJob;
use App\Models\Pelanggan;
use LivewireUI\Modal\ModalComponent;

class ModalUbahDomain extends ModalComponent
{
    public $data;
    public $versi_opensid;

    public static function modalMaxWidth(): string
    {
        return 'md';
    }

    public function mundurVersi($data)
    {
        MundurVersiJob::dispatch($data);
        $this->closeModal();
    }

    public function mount($data = null)
    {
        // If data is an ID, load the model
        if (is_numeric($data)) {
            $this->data = Pelanggan::find($data);
        } else {
            $this->data = $data;
        }

        $this->versi_opensid = env('OPENKAB') == 'true' ? (Pelanggan::first()->versi_opensid ?? '') : ($this->data?->versi_opensid ?? '');
    }

    public function render()
    {
        return view('livewire.opendk.modal-ubah-domain', [
            'openkab' => env('OPENKAB'),
        ]);
    }
}
