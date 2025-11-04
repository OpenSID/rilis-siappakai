<?php

namespace App\Livewire\Pelanggan;

use App\Http\Controllers\Helpers\AttributeSiapPakaiController;
use App\Models\Pelanggan;
use App\Models\Tema;
use Illuminate\Support\Facades\Http;
use Livewire\Component;

class ModalTambahTema extends Component
{
    public $data;
    public $temas;
    public $token_premium;
    public $pilihtema;
    public $table_tema;
    public $selectedTema = null;
    public $aktif = 'false';    
    public int $desa;  

    public function mount()
    {
        $this->data = Pelanggan::find($this->desa);
        $this->tampilTemaPro();
    }

    // ditampilkan setelah ada pemesanan tema melalui layanan
    public function tampilTemaPro()
    {
        $this->temas = array(['nama' => '']);
        if(!$this->data) return;
        $att = new AttributeSiapPakaiController();           
        if (!is_null($this->data->kode_desa)) {            
            $token = $this->data->token_premium;
            $response = Http::withOptions([
                'base_uri' => $att->getServerLayanan(),
            ])
                ->withHeaders([
                    'X-Requested-With' => 'XMLHttpRequest',
                ])
                ->withToken($token)
                ->get('api/v1/pelanggan/pemesanan-tema');

            if ($response->clientError()) {                
                $this->temas = array(['nama' => '']); 
                session()->flash('message-failed', 'gagal ambil data tema dari layanan '.$response->body());
            }

            if ($response->getStatusCode() == 200) {
                $this->temas = $response->throw()->json();                             
            }
        }
        return;        
    }

    public function updatedSelectedTema($pilihtema)
    {
        $prefix = 'Tema ';
        $this->pilihtema = strtolower(substr($pilihtema, strlen($prefix)));
        $this->table_tema = Tema::where('tema', $this->pilihtema)
            ->where('kode_desa', $this->data->kode_desa)->first();

        if (!is_null($this->table_tema)) {
            if ($this->table_tema->tema == $this->pilihtema) {
                session()->flash('message-failed', 'tema ' . $this->pilihtema . ' sudah ada');
            }
        } else {
            $this->aktif = 'true';
        }
    }

    public function SimpanTema()
    {
        if (is_null($this->pilihtema) || $this->pilihtema == '') {
            return session()->flash('message-failed', 'Silakan pilih tema terlebih dahulu');
        }

        $tema = [
            'pelanggan_id' => $this->data->id,
            'kode_desa' => $this->data->kode_desa,
            'tema' => $this->pilihtema,
        ];

        Tema::create($tema);
        session()->flash('message-success', 'Berhasil tambah tema ' . $this->pilihtema);
        $this->aktif = 'false';
    }    

    public function render()
    {
        return view('livewire.pelanggan.modal-tambah-tema');
    }
}
