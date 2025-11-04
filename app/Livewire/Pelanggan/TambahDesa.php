<?php

namespace App\Livewire\Pelanggan;

use Exception;
use Carbon\Carbon;
use App\Models\Tema;
use App\Enums\Opensid;
use App\Models\Wilayah;
use Livewire\Component;
use App\Models\Aplikasi;
use App\Models\Pelanggan;
use App\Jobs\BuildSiteJob;
use App\Enums\StatusLangganan;
use App\Services\OptionsService;
use App\Services\AplikasiService;
use Illuminate\Support\Facades\Log;
use App\Services\MasterOpensidService;
use App\Http\Controllers\Helpers\KoneksiController;
use App\Http\Controllers\Helpers\AttributeSiapPakaiController;

/**
 * Kelas TambahDesa adalah komponen Livewire untuk menambah data desa ke dalam sistem.
 * Kelas ini menangani logika pendaftaran desa baru, baik secara individu maupun massal,
 * serta mengelola status langganan dan domain terkait.
 */
class TambahDesa extends Component
{
    // Properti yang digunakan dalam komponen
    public $namakabupaten;
    public $pelanggan;
    public $sebutandesa;
    public $sebutankab;
    public $reset = "Kosongkan";
    public $submit = "Tambah";
    public $btnTambah = "";

    public $dataWilayah;
    public $kode_kab;
    public $show_port;

    public $kode_desa;
    public $nama_desa;
    public $langganan_opensid;
    public $versi_opensid;
    public $domain_opensid;
    public $domain_pbb;
    public $domain_api;
    public $status_langganan_opensid;
    public $status_langganan_saas;
    public $tgl_akhir_premium;
    public $tgl_akhir_saas;
    public $tgl_akhir_backup;
    public $port_domain;
    public $token_premium;
    public $pilih_opensid = 2;

    public $terdaftar_layanan = "true";
    public $pilih_pendaftaran = "false";

    public $selectedOpensid = null;
    protected $listeners = ['getDesa'];

    /**
     * Mengambil data desa berdasarkan kode desa dan mereset beberapa properti.
     *
     * @param string $kode
     */
    public function getDesa($kode)
    {
        $this->kode_desa = $kode;
        $this->resetFields();
    }

    /**
     * Memperbarui properti pilih_opensid ketika selectedOpensid berubah.
     *
     * @param string $opensid
     */
    public function updatedSelectedOpensid($opensid)
    {
        $this->pilih_opensid = $opensid;

        if ($this->pilih_opensid == Opensid::UMUM->value || $opensid == Opensid::UMUM->value) {
            $this->terdaftar_layanan = 'false';
        }
    }

    /**
     * Sinkronisasi jika pilih_opensid diubah langsung dari view
     */
    public function updatedPilihOpensid($opensid)
    {
        $this->selectedOpensid = $opensid;

        if ((int)$opensid === Opensid::UMUM->value) {
            $this->terdaftar_layanan = 'false';
        }
    }

    /**
     * Fallback trigger to force a render cycle when metode pendaftaran berubah
     */
    public function onPendaftaranChange(): void
    {
        // No-op: Livewire will re-render on action call
    }

    /**
     * Mereset beberapa properti ke nilai default.
     */
    public function resetFields()
    {
        $this->port_domain = "";
        $this->token_premium = "";
        $this->domain_opensid = "-";
        $this->btnTambah = "";
    }

    /**
     * Mengambil data pelanggan berdasarkan koneksi yang diberikan.
     * Mengembalikan array data pelanggan jika berhasil.
     *
     * @param KoneksiController $koneksi
     * @return array|null
     */
    public function dataPelanggan($koneksi)
    {
        $versi = new MasterOpensidService();

        $datapelanggan = $koneksi->cekDatapelanggan($this->token_premium, $this->kode_desa);
        $cek_data = Pelanggan::where('kode_desa', $this->kode_desa)->first();

        if ($datapelanggan == 'error') {
            $koneksi->pesanGagal();
        }

        if ($cek_data) {
            $koneksi->pesanTerdaftar($datapelanggan['desa']['nama_desa']);
        }

        $domain = formatDomain($datapelanggan['domain']);
        $langganan_opensid = Carbon::now()->format('Y-m-d') < $datapelanggan['tanggal_berlangganan']['akhir'] ? 'premium' : 'umum';

        return [
            'kode_provinsi' => $datapelanggan['desa']['kode_prov'],
            'nama_provinsi' => $datapelanggan['desa']['nama_prov'],
            'kode_kabupaten' => $datapelanggan['desa']['kode_kab'],
            'nama_kabupaten' => $datapelanggan['desa']['nama_kab'],
            'kode_kecamatan' => $datapelanggan['desa']['kode_kec'],
            'nama_kecamatan' => $datapelanggan['desa']['nama_kec'],
            'kode_desa' => $datapelanggan['desa']['kode_desa'],
            'nama_desa' => $datapelanggan['desa']['nama_desa'],
            'langganan_opensid' => $langganan_opensid,
            'versi_opensid' => $versi->cekVersiServer($langganan_opensid),
            'domain_opensid' => $domain,
            'domain_pbb' => $domain . '/pbb',
            'domain_api' => $domain . '/api',
            'status_langganan_opensid' => $datapelanggan['status_langganan'] == 'aktif' ? StatusLangganan::AKTIF : StatusLangganan::TIDAK_AKTIF,
            'status_langganan_saas' => $datapelanggan['status_langganan'] == 'aktif' ? StatusLangganan::AKTIF : StatusLangganan::TIDAK_AKTIF,
            'tgl_akhir_premium' => $datapelanggan['tanggal_berlangganan']['akhir'],
            'tgl_akhir_saas' => $datapelanggan['tanggal_berlangganan']['akhir'],
            'tgl_akhir_backup' => null,
            'token_premium' => $this->token_premium,
            'port_domain' => $this->port_domain,
        ];
    }

    /**
     * Mengambil data baru untuk desa berdasarkan koneksi, wilayah, dan kode desa yang diberikan.
     *
     * @param KoneksiController $koneksi
     * @param Wilayah $wilayah
     * @param string $kode_desa
     * @return array|null
     */
    public function dataBaru($koneksi, $wilayah, $kode_desa)
    {
        $versi = new MasterOpensidService();

        if (is_null($kode_desa)) {
            $koneksi->pesanGagal();
        }

        $wilayah = $wilayah->where('kode_desa', $kode_desa)->first();

        return [
            'kode_provinsi' => $wilayah->kode_prov,
            'nama_provinsi' => $wilayah->nama_prov,
            'kode_kabupaten' => $wilayah->kode_kab,
            'nama_kabupaten' => $wilayah->nama_kab,
            'kode_kecamatan' => $wilayah->kode_kec,
            'nama_kecamatan' => $wilayah->nama_kec,
            'kode_desa' => $wilayah->kode_desa,
            'nama_desa' => $wilayah->nama_desa,
            'langganan_opensid' => strtolower(Opensid::UMUM->name),
            'versi_opensid' => $versi->cekVersiServer(strtolower(Opensid::UMUM->name)),
            'domain_opensid' => $this->domain_opensid ?? '-',
            'domain_pbb' => ($this->domain_opensid ?? '-') . '/pbb',
            'domain_api' => ($this->domain_opensid ?? '-') . '/api',
            'status_langganan_opensid' => StatusLangganan::TIDAK_AKTIF,
            'status_langganan_saas' => StatusLangganan::TIDAK_AKTIF,
            'tgl_akhir_premium' => null,
            'tgl_akhir_saas' => null,
            'tgl_akhir_backup' => null,
            'token_premium' => null,
            'port_domain' => null,
        ];
    }

    /**
     * Menangani logika ketika form disubmit, memutuskan apakah akan mendaftarkan semua desa atau per desa.
     */
    public function Submit()
    {
        $att = new AttributeSiapPakaiController();
        $koneksi = new KoneksiController();
        $wilayah = new Wilayah();

        if ($this->pilih_pendaftaran == 'true') {
            $this->daftarSemuaDesa($att, $koneksi, $wilayah);
        } else {
            $this->daftarPerDesa($att, $koneksi, $wilayah);
        }
    }

    /**
     * Mendaftarkan semua desa yang belum terdaftar. Menggunakan koneksi, dan wilayah yang diberikan.
     *
     * @param AttributeSiapPakaiController $att
     * @param KoneksiController $koneksi
     * @param Wilayah $wilayah
     */
    public function daftarSemuaDesa($att, $koneksi, $wilayah)
    {
        try {
            $wilayahs = $wilayah->where('status_terdaftar', '0')->get();
            $jumlahdesa = $wilayahs->count();

            if ($jumlahdesa == 0) {
                return session()->flash('message-failed', 'Tidak ada data ' . $this->sebutandesa . ' di ' . $this->sebutankab . ' ' . ucwords(strtolower($this->namakabupaten)) . ' yang didaftarkan.');
            }

            foreach ($wilayahs as $item) {
                $data = $this->dataBaru($koneksi, $wilayah, $item->kode_desa);
                $this->prosesPendaftaranDesa($data, $att, $koneksi, $wilayah);
            }

            session()->flash('message-success', 'Berhasil Membuat ' . $jumlahdesa . ' ' . $this->sebutandesa . ' di SiapPakai.');
        } catch (Exception $e) {
            Log::error($e->getMessage());
            $pesan = $e->getMessage();
            session()->flash('message-failed', 'Gagal, menambahkan ' . $jumlahdesa . ' ' . $this->sebutandesa . ' di SiapPakai. Pesan error :' . $pesan);
        }
    }

    /**
     * Mendaftarkan desa secara individu berdasarkan koneksi, dan wilayah yang diberikan.
     *
     * @param AttributeSiapPakaiController $att
     * @param KoneksiController $koneksi
     * @param Wilayah $wilayah
     */
    public function daftarPerDesa($att, $koneksi, $wilayah)
    {
        try {
            $aplikasiService = new AplikasiService();
            $opensid = $aplikasiService->pengaturanApikasi('opensid');

            $data = $this->getDataForDesa($koneksi, $wilayah, $opensid);

            $this->prosesPendaftaranDesa($data, $att, $koneksi, $wilayah);
        } catch (Exception $e) {
            $this->handleException($e, $koneksi);
        }
    }

    /**
     * Mengambil data yang diperlukan untuk pendaftaran desa berdasarkan koneksi, wilayah, dan status OpenSID.
     *
     * @param KoneksiController $koneksi
     * @param Wilayah $wilayah
     * @param string $opensid
     * @return array
     */
    private function getDataForDesa($koneksi, $wilayah, $opensid)
    {
        if ($opensid == Opensid::UMUM->value || $this->pilih_opensid == Opensid::UMUM->value) {
            return $this->dataBaru($koneksi, $wilayah, $this->kode_desa);
        }

        if ($this->terdaftar_layanan == "true") {
            $koneksi->cekToken($this->token_premium, $this->kode_desa);
            return $this->dataPelanggan($koneksi);
        }

        return $this->dataBaru($koneksi, $wilayah, $this->kode_desa);
    }

    /**
     * Memproses pendaftaran desa dengan data yang diberikan. Menyimpan data dan memperbarui status wilayah.
     *
     * @param array $data
     * @param AttributeSiapPakaiController $att
     * @param Wilayah $wilayah
     */
    private function prosesPendaftaranDesa($data, $att, $koneksi, $wilayah)
    {
        BuildSiteJob::dispatch($data);

        $siteFolder = $att->getMultisiteFolder() . str_replace('.', '', $data['kode_desa']);

        if (file_exists($siteFolder) && !is_null($data['kode_desa'])) {
            $this->simpan($data);
            $this->btnTambah = 'disabled';

            $wilayah = $wilayah->where('kode_desa', $data['kode_desa'])->first();
            $wilayah->status_terdaftar = '1';
            $wilayah->save();

            session()->flash('message-success', 'Berhasil Membuat ' . $this->sebutandesa . ' di SiapPakai.');
        } else {
            $koneksi->pesanGagalToken($this->sebutandesa);
        }
    }

    /**
     * Menangani pengecualian yang terjadi selama proses pendaftaran desa.
     *
     * @param Exception $e
     * @param KoneksiController $koneksi
     */
    private function handleException($e, $koneksi)
    {
        $this->terdaftar_layanan == "true" ? $koneksi->pesanGagalToken($this->sebutandesa) : $koneksi->pesanGagal();
    }

    /**
     * Menyimpan data pelanggan ke dalam database dan mengatur tema jika diperlukan.
     *
     * @param array $data
     */
    public function simpan($data)
    {
        $tbl_pelanggan = new Pelanggan();
        $tbl_pelanggan::create($data);

        $kddesa = $tbl_pelanggan->where('kode_desa', $data['kode_desa'])->first();
        $web_theme = Aplikasi::pengaturan_aplikasi()['tema_bawaan'];

        if ($web_theme == '' || $web_theme == 'esensi' || $web_theme == 'natra') {
            $web_theme = false;
        }

        if ($kddesa && $web_theme != false) {
            $tema = [
                'pelanggan_id' => $kddesa->id,
                'kode_desa' => $kddesa->kode_desa,
                'tema' => $web_theme,
            ];

            Tema::create($tema);
        }
    }

    /**
     * Menginisialisasi properti yang diperlukan saat komponen dimuat.
     */
    public function mount()
    {
        // deklarasi service
        $aplikasiService = new AplikasiService();
        // ambil url dan token untuk menampilkan data desa / kelurahan
        $this->dataWilayah = '/api/wilayah/cari-desa?token=' . config('siappakai.sandi.token');
        $this->kode_kab = $aplikasiService->pengaturanApikasi('kode_provinsi') . '.' . $aplikasiService->pengaturanApikasi('kode_kabupaten');
        $this->show_port = $aplikasiService->pengaturanApikasi('pengaturan_domain');

        $sebutan = $aplikasiService->pengaturanApikasi('sebutan_kabupaten');
        $this->sebutankab = strtolower($sebutan) == 'kota' ? '' : $sebutan;
    }

    /**
     * Merender tampilan komponen dengan data yang diperlukan.
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        $options = new OptionsService();
        $aplikasiService = new AplikasiService();
        $opensid = $aplikasiService->pengaturanApikasi('opensid');

        return view('livewire.pelanggan.tambah-desa', ['options' => $options, 'opensid' => $opensid]);
    }
}
