<?php

namespace App\Livewire\Pelanggan;

use App\Jobs\UnduhFolderDesaJob;
use Illuminate\Support\Facades\Artisan;
use Livewire\Component;

class ModalUnduhFolderDesa extends Component
{
    public $data;
    public $pathToFile;
    public $namaFile;
    public $headers;
    public $show = 'false';

    protected $listeners = ['getUnduhFolderDesa'];

    // Menambahkan Data ke Table
    public function getUnduhFolderDesa($pathToFile, $namaFile, $headers)
    {
        $this->pathToFile = $pathToFile;
        $this->namaFile = $namaFile;
        $this->headers = $headers;

        sleep(3);
        if (file_exists($this->pathToFile)) {
            $this->show = 'true';
        }
    }

    public function unduhFolderDesa()
    {
        // perintah untuk unduh
        if (file_exists($this->pathToFile)) {
            response()->json(['download-success' => "File berhasil diunduh"]);
            return response()->download($this->pathToFile, $this->namaFile, $this->headers);
        }
    }

    public function Batal()
    {
        $jobs = [
            'file' => $this->pathToFile,
            'directory_from' => '',
            'directory' => '',
        ];

        // proses hapus file zip setelah diunduh
        UnduhFolderDesaJob::dispatch($jobs);
        Artisan::call('siappakai:config-clear');

        $this->dispatch('closeModalUnduhFolderDesa-' . $this->data->id);
        return redirect()->to('/pelanggan');
    }

    public function render()
    {
        return view('livewire.pelanggan.modal-unduh-folder-desa');
    }
}
