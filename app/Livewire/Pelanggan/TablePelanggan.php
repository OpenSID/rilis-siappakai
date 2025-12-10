<?php

namespace App\Livewire\Pelanggan;

use App\Http\Controllers\Helpers\AttributeSiapPakaiController;
use App\Jobs\SslJob;
use App\Jobs\UnduhFolderDesaJob;
use App\Models\Aplikasi;
use App\Models\Pelanggan;
use App\Services\AplikasiService;
use Illuminate\Support\Facades\Artisan;
use Livewire\Component;

class TablePelanggan extends Component
{
    public $langganan = null;
    public $statusOpenSID = null;
    public $statusSaas = null;
    public $masaAktif = null;
    public $provinsi = null;
    public $kabupaten = null;
    public $remain;
    public $pathDB;
    public $pathDesa;
    public $sebutan;
    public $table = 'pelanggan';
    public $tombolNonAktif = 'disabled';
    public $apacheConfDir;
    public $cert;

    protected $listeners = [
        'setPilihLangganan',
        'setPilihStatusLanggananOpenSID',
        'setPilihStatusLanggananSaas',
        'setPilihMasaAktif',
        'setPilihProvinsi',
        'setPilihKabupaten',
        '$refresh'
    ];

    // Filter Langganan
    public function setPilihLangganan($langganan)
    {
        $this->langganan = $langganan;
    }

    public function konfigurasiSSL()
    {
        $serverPanel = Aplikasi::pengaturan_aplikasi()['server_panel'];
        if ($serverPanel == '1') {
            $this->apacheConfDir = '/www/server/panel/vhost/cert/';
            $this->cert = '/privkey.pem';
        } else {
            $this->apacheConfDir = '/etc/apache/site-available/';
            $this->cert = '-le-ssl.conf';
        }
    }

    // Filter Status OpenSID
    public function setPilihStatusLanggananOpenSID($status)
    {
        $this->statusOpenSID = $status;
    }

    // Filter Status Saas
    public function setPilihStatusLanggananSaas($status)
    {
        $this->statusSaas = $status;
    }

    // Filter Masa Aktif
    public function setPilihMasaAktif($status)
    {
        $this->masaAktif = $status;
    }

    public function setPilihProvinsi($status)
    {
        $this->provinsi = $status;
    }

    public function setPilihKabupaten($status)
    {
        $this->kabupaten = $status;
    }


    public function queryPelanggans()
    {
        $q_pelanggans = Pelanggan::latest('updated_at');

        if ($this->langganan) {
            $q_pelanggans = $q_pelanggans->where('langganan_opensid', $this->langganan);
        }

        if ($this->statusOpenSID) {
            $q_pelanggans = $q_pelanggans->where('status_langganan_opensid', $this->statusOpenSID);
        }

        if ($this->statusSaas) {
            $q_pelanggans = $q_pelanggans->where('status_langganan_saas', $this->statusSaas);
        }

        if ($this->masaAktif) {
            switch ($this->masaAktif) {
                case '1':
                    $q_pelanggans = $q_pelanggans->where('Remaining', '<', 30)->where('Remaining', '>', 0);
                    break;

                case '2':
                    $q_pelanggans = $q_pelanggans->where('Remaining', '<=', 0);
                    break;
            }
        }

        if ($this->remain) {
            $q_pelanggans = $q_pelanggans->where('Remaining', '<=', 0);
        }

        if ($this->kabupaten) {
            $q_pelanggans = $q_pelanggans->where('kode_kabupaten', $this->kabupaten);
        }

        if ($this->provinsi) {
            $q_pelanggans = $q_pelanggans->where('kode_provinsi', $this->provinsi);
        }

        return $q_pelanggans->get();
    }

    // Tombol Unduh Database OpenSID
    public function unduhDatabaseOpensid($data)
    {
        if (file_exists($this->pathDB)) {
            $namaFile = "db_" . str_replace('.', '', $data['kode_desa']) . ".sql";
            $pathToFile = $this->pathDB . DIRECTORY_SEPARATOR . $namaFile;
            $headers = array(
                'Content-Type: application/sql',
            );

            response()->json(['download-success' => "File berhasil diunduh"]);
            return response()->download($pathToFile, $namaFile, $headers);
        }
    }

    // Tombol Unduh Database Pbb
    public function unduhDatabasePbb($data)
    {
        if (file_exists($this->pathDB)) {
            $namaFile = "db_" . str_replace('.', '', $data['kode_desa']) . "_pbb.sql";
            $pathToFile = $this->pathDB . DIRECTORY_SEPARATOR . $namaFile;
            $headers = array(
                'Content-Type: application/sql',
            );

            response()->json(['download-success' => "File berhasil diunduh"]);
            return response()->download($pathToFile, $namaFile, $headers);
        }
    }

    // Tombol Unduh FolderDesa
    public function unduhFolderDesa($data)
    {
        $att = new AttributeSiapPakaiController();

        if (file_exists($this->pathDesa)) {
            $namaFolder = "desa_" . str_replace('.', '', $data['kode_desa']);
            $namaFile = $namaFolder . '.zip';

            $pathToFolder = $this->pathDesa . DIRECTORY_SEPARATOR . $namaFolder;
            $pathToFile = $this->pathDesa . DIRECTORY_SEPARATOR . $namaFile;
            $headers = array(
                'Content-Type: application/zip',
            );

            $jobs = [
                'directory_from' => $att->getMultisiteFolder() . str_replace('.', '', $data['kode_desa']) . DIRECTORY_SEPARATOR . 'desa',
                'file' => $pathToFile,
                'directory' => $pathToFolder,
            ];

            // Proses copy folder desa dan dibuat menjadi zip
            UnduhFolderDesaJob::dispatch($jobs);
            Artisan::call('siappakai:config-clear');

            $this->dispatch('getUnduhFolderDesa', $pathToFile, $namaFile, $headers);
            $this->dispatch('openModalUnduhFolderDesa-' . $data['id']);
        }
    }

    // Tombol status SSL
    public function statusSSL($data)
    {
        $request = [
            'domain' => $data['domain_opensid'],
            'update' => true
        ];

        SslJob::dispatchSync($request, false);
        sleep(3);
        redirect()->to('/pelanggan');
    }

    // Tombol status SSL
    public function statusSSLWildcard($data)
    {
        $request = [
            'domain' => $data['domain_opensid'],
            'kodewilayah' => $data['kode_desa'],
            'jenis' => 'wildcard',
            'app' => 'opensid'
        ];

        SslJob::dispatchSync($request, true);
        sleep(3);
        redirect()->to('/pelanggan');
    }

    public function render()
    {
        $aplikasiservice = new AplikasiService();
        $opensid = $aplikasiservice->pengaturanApikasi('opensid');
        $this->konfigurasiSSL();
        $port = Aplikasi::pengaturan_aplikasi()['pengaturan_domain'];
        $openkab = env('OPENKAB');
        $backup = env('ROOT_OPENSID') . "storage" . DIRECTORY_SEPARATOR . "backup";
        $pelanggans = $this->queryPelanggans();

        $this->pathDB = $backup . DIRECTORY_SEPARATOR .  "database";
        $this->pathDesa = $backup . DIRECTORY_SEPARATOR .  "folder-desa";

        return view('livewire.pelanggan.table-pelanggan', ['pelanggans' => $pelanggans, 'port' => $port, 'openkab' => $openkab, 'opensid' => $opensid]);
    }
}
