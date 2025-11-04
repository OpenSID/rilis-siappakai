<?php

namespace App\Livewire\Pelanggan;

use App\Jobs\PermissionBackupJob;
use App\Jobs\PindahHostingJob;
use App\Models\Pelanggan;
use Exception;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;

class ModalPindahHosting extends Component
{
    use WithFileUploads;

    public $data;
    public $folderdesa;
    public $database_opensid;
    public $database_pbb;
    public $sukses;
    public $hide = false;
    public $proses = false;
    public $link;
    public $link_close;
    public int $desa;

    protected $rules = [
        'folderdesa' => 'max:67108864',
        'database_opensid' => 'max:67108864',
        'database_pbb' => 'max:67108864',
    ];

    protected $messages = [
        'folderdesa.file' => 'Silakan pilih kembali file yang menggunakan ekstensi .zip',
        'folderdesa.max' => 'Tidak boleh lebih dari 64 GB',
        'database_opensid.file' => 'Silakan pilih kembali file yang menggunakan ekstensi .sql',
        'database_opensid.max' => 'Tidak boleh lebih dari 64 GB',
        'database_pbb.file' => 'Silakan pilih kembali file yang menggunakan ekstensi .sql',
        'database_pbb.max' => 'Tidak boleh lebih dari 64 GB',
    ];

    public function mount()
    {
        $this->data = Pelanggan::find($this->desa);
    }

    public function pindahHosting($data)
    {
        $kode_desa = str_replace('.', '', $data['kode_desa']);

        /** path */
        $path = public_path() . DIRECTORY_SEPARATOR . "backup";
        $path_desa =  $path . DIRECTORY_SEPARATOR . "folder-desa" . DIRECTORY_SEPARATOR;
        $path_db = $path . DIRECTORY_SEPARATOR . "database" . DIRECTORY_SEPARATOR;

        /** permission path */
        if (file_exists($path)) {
            PermissionBackupJob::dispatch();
            $this->sukses = 'Silakan tekan tombol Ya';
        }

        /** buat path */
        $this->buatPath($path, $path_desa, $path_db);

        /** nama file */
        $filename_desa = 'desa_' . $kode_desa;
        $filename_db_opensid = 'db_' . $kode_desa . '.sql';
        $filename_db_pbb = 'db_' . $kode_desa . '_pbb.sql';

        /** validasi */
        $this->validate();

        try {
            /** Upload File*/
            if (!is_null($this->folderdesa)) {
                $this->unggahFolderDesa($filename_desa, $path_desa);
            }

            if (!is_null($this->database_opensid)) {
                $this->unggahDatabaseOpensid($filename_db_opensid, $path_db);
            }

            if (!is_null($this->database_pbb)) {
                $this->unggahDatabasePbb($filename_db_pbb, $path_db);
            }

            /** Job */
            if (file_exists($path_desa . $filename_desa) || file_exists($path_db . $filename_db_opensid) || file_exists($path_db . $filename_db_pbb)) {
                PindahHostingJob::dispatch($data);
                $this->hide = true;
                $this->proses = true;
                $this->sukses = 'Pindah hosting saat ini sedang dalam proses';
                $this->link = '/jobs';
                $this->link_close = '/pelanggan';
            }
        } catch (Exception $e) {
            $this->sukses = 'Gagal Pindah Hosting';
        }
    }

    public function buatPath($path, $path_desa, $path_db)
    {
        /** buat folder backup */
        if (!file_exists($path)) {
            exec('mkdir ' . $path);
        }

        /** buat folder folder-desa */
        if (!file_exists($path_desa)) {
            exec('mkdir ' . $path_desa);
        }

        /** buat folder database */
        if (!file_exists($path_db)) {
            exec('mkdir ' . $path_db);
        }
    }

    public function unggahFolderDesa($filename, $path_desa)
    {
        $filezip = $filename . '.zip';
        $this->folderdesa->storeAs('upload', $filezip); // upload file zip

        $storage = Storage::path('upload' . DIRECTORY_SEPARATOR);
        if (file_exists($storage . $filezip)) {
            $extract = 'unzip ' . $storage . $filezip . ' -d ' .  Storage::path('upload');
            if (file_exists($storage . $filename) || $storage . 'desa') {
                File::deleteDirectory($storage . 'desa'); // hapus folder desa
                File::deleteDirectory($storage . $filename); // hapus file filename jika sudah ada
            }
            exec($extract); // extract file zip

            if (file_exists($storage . 'desa')) {
                rename($storage . 'desa', $storage . $filename); // rename folder desa
            }

            if (file_exists($path_desa . $filename)) {
                File::deleteDirectory($path_desa . $filename); // hapus file desa public/backup
            }

            if (file_exists($storage . $filename)) {
                File::moveDirectory($storage . $filename, $path_desa . $filename); // pindah ke public/backup/folder-desa
                File::delete($storage . $filezip); // hapus file zip
            }
        }
    }

    public function unggahDatabaseOpensid($filename, $path_db)
    {
        $this->database_opensid->storeAs('upload', $filename); // upload file sql

        $file_db_opensid = Storage::path('upload' . DIRECTORY_SEPARATOR . $filename);
        if (file_exists($file_db_opensid)) {
            File::move($file_db_opensid, $path_db . $filename); // pindah ke public/backup/database
        }
    }

    public function unggahDatabasePbb($filename, $path_db)
    {
        $this->database_pbb->storeAs('upload', $filename); // upload file sql

        $file_db_pbb = Storage::path('upload' . DIRECTORY_SEPARATOR . $filename);
        if (file_exists($file_db_pbb)) {
            File::move($file_db_pbb, $path_db . $filename); // pindah ke public/backup/database
        }
    }

    public function Batal()
    {
        $this->clearUpload();
        $this->dispatch('closeModalPindahHosting');
        return redirect()->to('/pelanggan');
    }

    public function clearUpload()
    {
        $this->folderdesa = null;
        $this->database_opensid = null;
        $this->database_pbb = null;
    }

    public function render()
    {
        $openkab = env('OPENKAB');
        return view('livewire.pelanggan.modal-pindah-hosting', ['openkab' => $openkab]);
    }
}
