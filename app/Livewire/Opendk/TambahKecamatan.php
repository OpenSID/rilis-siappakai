<?php

namespace App\Livewire\Opendk;

use Exception;
use App\Models\Wilayah;
use Livewire\Component;
use App\Jobs\BuildSiteOpendkJob;
use App\Services\OptionsService;
use App\Services\AplikasiService;
use App\Http\Controllers\Helpers\KoneksiController;
use App\Http\Controllers\Helpers\AttributeSiapPakaiController;
use App\Models\Opendk;

/**
 * Kelas TambahKecamatan adalah komponen Livewire untuk menambah data kecamatan ke dalam sistem.
 * Kelas ini menangani logika pendaftaran kecamatan baru, baik secara individu maupun massal,
 * serta mengelola status langganan dan domain terkait.
 */
class TambahKecamatan extends Component
{
    // Properti yang digunakan dalam komponen
    public $namakabupaten;
    public $sebutankecamatan;
    public $sebutankab;
    public $reset = "Kosongkan";
    public $submit = "Tambah";
    public $btnTambah = "";

    public $dataWilayah;
    public $kode_kab;
    public $show_port;

    public $kode_kec;
    public $nama_kec;
    public $domain_opendk;
    public $tgl_akhir_backup;
    public $port_domain;

    protected $listeners = ['getKecamatan'];

    /**
     * Validation rules
     */
    protected function rules()
    {
        return [
            'kode_kec' => 'required',
            'domain_opendk' => 'required|string|max:255',
        ];
    }

    /**
     * Custom validation messages
     */
    protected $messages = [
        'kode_kec.required' => 'Kecamatan harus dipilih.',
        'domain_opendk.required' => 'Domain OpenDK harus diisi.',
        'domain_opendk.max' => 'Domain OpenDK maksimal 255 karakter.',
    ];

    /**
     * Mengambil data kecamatan berdasarkan kode kecamatan dan mereset beberapa properti.
     *
     * @param string $kode
     */
    public function getKecamatan($kode)
    {
        $this->kode_kec = $kode;
        
        // Fetch nama_kec from database
        $wilayah = Wilayah::where('kode_kec', $kode)->first();
        if ($wilayah) {
            $this->nama_kec = $wilayah->nama_kec;
        }
        
        $this->Kosongkan();
    }

    /**
     * Mereset beberapa properti ke nilai default.
     */
    public function Kosongkan()
    {
        $this->port_domain = "";
        $this->btnTambah = "";
        $this->domain_opendk = "";
    }

    /**
     * Mengambil data baru untuk kecamatan berdasarkan koneksi, wilayah, dan kode kecamatan yang diberikan.
     *
     * @param KoneksiController $koneksi
     * @param Wilayah $wilayah
     * @param string $kode_kec
     * @return array|null
     */
    public function dataBaru($koneksi, $wilayah, $kode_kec)
    {
        if (is_null($kode_kec)) {
            $koneksi->pesanGagal();
        }

        $wilayah = $wilayah->where('kode_kec', $kode_kec)->first();

        return [
            'kode_provinsi' => $wilayah->kode_prov,
            'nama_provinsi' => $wilayah->nama_prov,
            'kode_kabupaten' => $wilayah->kode_kab,
            'nama_kabupaten' => $wilayah->nama_kab,
            'kode_kecamatan' => $wilayah->kode_kec,
            'nama_kecamatan' => $wilayah->nama_kec,
            'domain_opendk' => $this->domain_opendk ?? '-',
            'port_domain' => null,
            'tgl_akhir_backup' => null,
        ];
    }

    /**
     * Menangani logika ketika form disubmit, memutuskan apakah akan mendaftarkan semua kecamatan atau per kecamatan.
     */
    public function Submit()
    {
        // Validate input
        $this->validate();

        $att = new AttributeSiapPakaiController();
        $koneksi = new KoneksiController();
        $wilayah = new Wilayah();

        $this->daftarPerKecamatan($att, $koneksi, $wilayah);
    }

    /**
     * Mendaftarkan kecamatan secara individu berdasarkan koneksi, dan wilayah yang diberikan.
     *
     * @param AttributeSiapPakaiController $att
     * @param KoneksiController $koneksi
     * @param Wilayah $wilayah
     */
    public function daftarPerKecamatan($att, $koneksi, $wilayah)
    {
        try {
            $data = $this->getDataForKecamatan($koneksi, $wilayah);

            $this->prosesPendaftaranOpenDK($data, $att, $koneksi, $wilayah);
        } catch (Exception $e) {
            return session()->flash('message-failed', 'Gagal: ' . $e->getMessage());
        }
    }

    /**
     * Mengambil data yang diperlukan untuk pendaftaran kecamatan berdasarkan koneksi, wilayah, dan status OpenDK.
     *
     * @param KoneksiController $koneksi
     * @param Wilayah $wilayah
     * @param string $opendk
     * @return array
     */
    private function getDataForKecamatan($koneksi, $wilayah)
    {
        return $this->dataBaru($koneksi, $wilayah, $this->kode_kec);
    }

    /**
     * Memproses pendaftaran kecamatan dengan data yang diberikan. Menyimpan data dan memperbarui status wilayah.
     *
     * @param array $data
     * @param AttributeSiapPakaiController $att
     * @param Wilayah $wilayah
     */
    private function prosesPendaftaranOpenDK($data, $att, $koneksi, $wilayah)
    {
        $this->simpan($data);
        BuildSiteOpendkJob::dispatch($data);

        $att->getMultisiteFolder() . str_replace('.', '', $data['kode_kecamatan']);

        $this->btnTambah = 'disabled';
        return session()->flash('message-success', 'Berhasil Membuat Job Untuk Install OpenDK dengan kode kecamatan ' . $data['kode_kecamatan'] . ' di SiapPakai.');
    }

    /**
     * Menyimpan data opendk ke dalam database dan mengatur tema jika diperlukan.
     *
     * @param array $data
     */
    public function simpan(array $data): Opendk
    {
        return Opendk::create($data);
    }

    /**
     * Menginisialisasi properti yang diperlukan saat komponen dimuat.
     */
    public function mount()
    {
        // deklarasi service
        $aplikasiService = new AplikasiService();
        // ambil url dan token untuk menampilkan data kecamatan
        $this->dataWilayah = '/api/wilayah/cari-kecamatan?token=' . config('siappakai.sandi.token');
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

        return view('livewire.opendk.tambah-kecamatan', ['options' => $options]);
    }
}
