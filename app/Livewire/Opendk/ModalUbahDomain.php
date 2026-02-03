<?php

namespace App\Livewire\Opendk;

use App\Jobs\UpdateDomainOpendkJob;
use LivewireUI\Modal\ModalComponent;

class ModalUbahDomain extends ModalComponent
{
    public $data;
    public $nama_domain_baru;

    public function mount($data)
    {
        $this->data = $data;
        $this->nama_domain_baru = $data->domain_opendk;
    }

    public function Batal()
    {
        return redirect()->to('/opendk');
    }

    public function Simpan()
    {
        $payload = [
            'domain_opendk' => $this->nama_domain_baru,
            'domain_opendk_lama' => $this->data->domain_opendk,
            'kode_kecamatan' => $this->data->kode_kecamatan
        ];

        if ($this->data->domain_opendk != $payload['domain_opendk']) {
            UpdateDomainOpendkJob::dispatch($payload);
            $this->data->update(['domain_opendk' => $this->nama_domain_baru]);

            session()->flash('update-success', 'Berhasil Ubah Domain OpenDK dengan nama kecamatan ' . $this->data->nama_kecamatan . ' di SiapPakai.');

            return redirect()->to('/opendk');
        } else {
            session()->flash('message-failed', 'Domain baru sama dengan domain lama atau tidak ada perubahan.');
        }
    }

    public function render()
    {
        return view('livewire.opendk.modal-ubah-domain');
    }
}
