<?php

namespace App\Livewire\Pelanggan;

use App\Http\Controllers\Helpers\KoneksiController;
use App\Jobs\PembaruanTokenJob;
use Livewire\Component;

class ModalPembaruanToken extends Component
{
    public $data;
    public $tokenConfig; // old
    public $tokenBaru; //new


    public function dataPelanggan($data_layanan, $koneksi)
    {
        $data = [];

        if ($data_layanan['status_langganan'] == 'aktif') {
            $langganan_opensid = 'premium';
            $status_langganan_opensid = '1';
            $status_langganan_saas = '1';
        } else {
            $langganan_opensid = 'umum';
            $status_langganan_opensid = '3';
            $status_langganan_saas = '3';
        }

        $domain = $data_layanan['domain'];
        $domain = rtrim(str_replace('https://', '', $domain), "/");

        if ($data_layanan['tanggal_berlangganan']['akhir']) {
            $data = [
                'kode_provinsi' => $data_layanan['desa']['kode_prov'],
                'nama_provinsi' => $data_layanan['desa']['nama_prov'],
                'kode_kabupaten' => $data_layanan['desa']['kode_kab'],
                'nama_kabupaten' => $data_layanan['desa']['nama_kab'],
                'kode_kecamatan' => $data_layanan['desa']['kode_kec'],
                'nama_kecamatan' => $data_layanan['desa']['nama_kec'],
                'nama_desa' => $data_layanan['desa']['nama_desa'],
                'token_premium' => $this->tokenBaru,
                'langganan_opensid' => $langganan_opensid,
                'versi_opensid' => opensid_version($data_layanan),
                'domain_opensid' => $domain,
                'domain_pbb' => $domain . '/pbb',
                'domain_api' => $domain . '/api',
                'status_langganan_opensid' => $status_langganan_opensid,
                'status_langganan_saas' => $status_langganan_saas,
                'tgl_akhir_premium' => $data_layanan['tanggal_berlangganan']['akhir'],
                'tgl_akhir_saas' => $data_layanan['tanggal_berlangganan']['akhir'],
            ];
        } else {
            $koneksi->pesanCekToken();
        }

        return $data;
    }

    public function pembaruanToken()
    {
        $koneksi = new KoneksiController();

        if (!$this->tokenBaru) {
            $this->dispatch('closeModalPembaruanToken-' . $this->data->id);
            $koneksi->pesanCekToken();
        }

        $data_layanan = $koneksi->cekDatapelanggan($this->tokenBaru, $this->data->kode_desa);
        if ($data_layanan != 'error') {
            $data = $this->dataPelanggan($data_layanan, $koneksi);
        } else {
            $koneksi->pesanCekToken();
        }

        if ($this->tokenBaru && $data != []) {
            $this->data->update($data);
            PembaruanTokenJob::dispatch($this->data);

            $this->dispatch('closeModalPembaruanToken-' . $this->data->id);
            session()->flash('message-success', 'Pembaruan token berhasil.');
        }

        $this->mount();
    }

    public function mount()
    {
        $this->tokenConfig = $this->data->token_premium;
        $this->tokenBaru = '';
    }

    public function Batal()
    {
        $this->mount();
        $this->dispatch('closeModalPembaruanToken-' . $this->data->id);
        return redirect()->to('/pelanggan');
    }

    public function render()
    {
        return view('livewire.pelanggan.modal-pembaruan-token');
    }
}
