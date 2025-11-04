<?php

namespace App\Livewire\Pelanggan;

use App\Jobs\PengaturanEmailApiJob;
use App\Models\PelangganEmail;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Component;

class ModalPengaturanEmail extends Component
{
    public $data;
    public $mail_host;
    public $mail_user;
    public $mail_pass;
    public $mail_address;
    public $mail;
    public $sebutan;

    public function mount()
    {
        $this->mail = PelangganEmail::with('pelanggan')
            ->whereHas('pelanggan', function (Builder $query) {
                $query->where('kode_desa', $this->data->kode_desa);
            })
            ->first();

        if ($this->mail) {
            $this->mail_host = $this->mail['mail_host'];
            $this->mail_user = $this->mail['mail_user'];
            $this->mail_pass = $this->mail['mail_pass'];
            $this->mail_address = $this->mail['mail_address'];
        } else {
            $this->clear();
        }
    }

    public function clear()
    {
        $this->mail_host = '';
        $this->mail_user = '';
        $this->mail_pass = '';
        $this->mail_address = '';
    }

    public function SimpanPengaturan()
    {
        $data = [
            'kode_desa' => $this->data->kode_desa,
            'pelanggan_id' => $this->data->id,
            'mail_host' => $this->mail_host,
            'mail_user' => $this->mail_user,
            'mail_pass' => $this->mail_pass,
            'mail_address' => $this->mail_address,
        ];

        if ($this->mail) {
            $this->mail->update($data);
        } else {
            PelangganEmail::create($data);
        }

        PengaturanEmailApiJob::dispatch($data);

        $this->dispatch('closeModalPengaturanEmail-' . $this->data->id);
        return redirect()->to('/pelanggan')->with('update-success', 'Pengaturan Email telah diubah.');
    }

    public function Batal()
    {
        $this->mount();
        $this->dispatch('closeModalPengaturanEmail-' . $this->data->id);
    }

    public function render()
    {
        return view('livewire.pelanggan.modal-pengaturan-email');
    }
}
