<?php

namespace App\Livewire\Pelanggan;

use App\Jobs\PengaturanEmailOpensidJob;
use App\Models\PelangganEmail;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Component;

class ModalPengaturanEmailOpensid extends Component
{
    public $data;
    public $smtp_protocol;
    public $smtp_host;
    public $smtp_user;
    public $smtp_pass;
    public $smtp_port;
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
            $this->smtp_protocol = $this->mail['smtp_protocol'];
            $this->smtp_host = $this->mail['smtp_host'];
            $this->smtp_user = $this->mail['smtp_user'];
            $this->smtp_pass = $this->mail['smtp_pass'];
            $this->smtp_port = $this->mail['smtp_port'];
        } else {
            $this->clear();
        }
    }

    public function clear()
    {
        $this->smtp_protocol = '';
        $this->smtp_host = '';
        $this->smtp_user = '';
        $this->smtp_pass = '';
        $this->smtp_port = '';
    }

    public function SimpanPengaturan()
    {
        $data = [
            'kode_desa' => $this->data->kode_desa,
            'pelanggan_id' => $this->data->id,
            'smtp_protocol' => $this->smtp_protocol,
            'smtp_host' => $this->smtp_host,
            'smtp_user' => $this->smtp_user,
            'smtp_pass' => $this->smtp_pass,
            'smtp_port' => $this->smtp_port,
            'token_premium' => $this->data->token_premium,
        ];

        if (!is_null($this->mail)) {
            $this->mail->update($data);
        } else {
            PelangganEmail::create($data);
        }

        PengaturanEmailOpensidJob::dispatch($data);

        $this->dispatch('closeModalPengaturanEmailOpensid-' . $this->data->id);
        return redirect()->to('/pelanggan')->with('update-success', 'Pengaturan Email telah diubah.');
    }

    public function Batal()
    {
        $this->mount();
        $this->dispatch('closeModalPengaturanEmailOpensid-' . $this->data->id);
    }

    public function render()
    {
        return view('livewire.pelanggan.modal-pengaturan-email-opensid');
    }
}
