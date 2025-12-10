<?php

namespace App\Livewire\Opendk;

use App\Jobs\SslJob;
use App\Models\Aplikasi;
use App\Models\Opendk;
use App\Services\AplikasiService;
use Livewire\Component;

class TableOpendk extends Component
{
    public $provinsi = null;
    public $kabupaten = null;
    public $remain;
    public $table = 'opendk';
    public $tombolNonAktif = 'disabled';
    public $apacheConfDir;
    public $cert;

    protected $listeners = [
        'setPilihProvinsi',
        'setPilihKabupaten',
        '$refresh'
    ];

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
        $q_opendks = Opendk::latest('updated_at');

        if ($this->kabupaten) {
            $q_opendks = $q_opendks->where('kode_kabupaten', $this->kabupaten);
        }

        if ($this->provinsi) {
            $q_opendks = $q_opendks->where('kode_provinsi', $this->provinsi);
        }

        return $q_opendks->get();
    }

    // Tombol status SSL
    public function statusSSL($data)
    {
        $request = [
            'domain' => $data['domain_opendk'],
            'update' => true
        ];

        SslJob::dispatchSync($request, false);
        sleep(3);
        redirect()->to('/opendk');
    }

    // Tombol status SSL
    public function statusSSLWildcard($data)
    {
        $request = [
            'domain' => $data['domain_opendk'],
            'kodewilayah' => $data['kode_kecamatan'],
            'jenis' => 'wildcard',
            'app' => 'opendk'
        ];

        SslJob::dispatchSync($request, true);
        sleep(3);
        redirect()->to('/opendk');
    }

    public function render()
    {
        $aplikasiservice = new AplikasiService();
        $opensid = $aplikasiservice->pengaturanApikasi('opensid');
        $this->konfigurasiSSL();
        $port = Aplikasi::pengaturan_aplikasi()['pengaturan_domain'];
        $openkab = env('OPENKAB');
        $opendks = $this->queryPelanggans();

        return view('livewire.opendk.table-opendk', ['opendks' => $opendks, 'port' => $port, 'openkab' => $openkab, 'opensid' => $opensid]);
    }
}
