<?php

namespace App\Console\Commands;

use App\Http\Controllers\Helpers\AttributeSiapPakaiController;
use App\Models\Pelanggan;
use Illuminate\Console\Command;

class Mitra extends Command
{
    /**
     * Nama dan signature dari console command.
     *
     * @var string
     */
    protected $signature = 'siappakai:mitra {--kode_desa=} {--mitra=} {--all=}';

    /**
     * Deskripsi dari console command.
     *
     * @var string
     */
    protected $description = 'Menampilkan logo Mitra pada halaman web';

    private $all;
    private $att;
    private $mitra;

    /**
     * Membuat instance baru dari command.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->att = new AttributeSiapPakaiController();
    }

    /**
     * Menjalankan console command.
     *
     * @return int
     */
    public function handle()
    {
        // Mengambil opsi dari command
        $kodedesa = $this->option('kode_desa');
        $this->mitra = $this->option('mitra');
        $this->all = $this->option('all');

        // Validasi input
        if ($this->mitra == 'true' && empty($kodedesa) && empty($this->all)) {
            return $this->error("Silakan masukan --kode_desa=");
        }

        // Menjalankan fungsi pelangganSiapPakai jika opsi all diaktifkan
        if ($this->all == 'true') {
            $this->pelangganSiapPakai();
        } else {
            // Mengatur folder situs berdasarkan kode desa
            $this->att->setSiteFolderOpensid($this->att->getMultisiteFolder() . $kodedesa);
        }

        // Mengecek apakah folder situs ada
        if (file_exists($this->att->getSiteFolderOpensid())) {
            $this->mitra($kodedesa);
        } else {
            return $this->error("Kode desa " . $kodedesa . " tidak tersedia");
        }
    }

    /**
     * Menjalankan perintah untuk semua pelanggan.
     *
     * @return void
     */
    private function pelangganSiapPakai()
    {
        $pelanggans = Pelanggan::get();
        foreach ($pelanggans as $item) {
            $this->att->setSiteFolderOpensid($this->att->getMultisiteFolder() . str_replace('.', '', $item->kode_desa));
            $this->mitra($item->kode_desa);
        }
    }

    /**
     * Menampilkan atau menghapus logo mitra berdasarkan kode desa.
     *
     * @param string $kodedesa
     * @return void
     */
    public function mitra($kodedesa)
    {
        $touchMitra = $this->att->getSiteFolderOpensid() . DIRECTORY_SEPARATOR . 'mitra';

        // Menampilkan atau menghapus logo mitra berdasarkan opsi
        if ($this->mitra == 'true') {
            $this->tampilkanLogoMitra($touchMitra, $kodedesa);
        } elseif ($this->mitra == 'false') {
            $this->hapusLogoMitra($touchMitra, $kodedesa);
        } else {
            return $this->error("Opsi mitra tidak valid.");
        }
    }

    /**
     * Menampilkan logo mitra.
     *
     * @param string $touchMitra
     * @param string $kodedesa
     * @return void
     */
    private function tampilkanLogoMitra($touchMitra, $kodedesa)
    {
        if (!file_exists($touchMitra)) {
            exec('sudo touch ' . $touchMitra);
            $this->info("Berhasil menampilkan logo mitra pada " . $kodedesa);
        } else {
            $this->info("File mitra sudah ada.");
        }
    }

    /**
     * Menghapus logo mitra.
     *
     * @param string $touchMitra
     * @param string $kodedesa
     * @return void
     */
    private function hapusLogoMitra($touchMitra, $kodedesa)
    {
        if (file_exists($touchMitra)) {
            exec('sudo rm ' . $touchMitra);
            $this->info("Berhasil menghapus logo mitra pada " . $kodedesa);
        } else {
            $this->info("File mitra tidak ditemukan.");
        }
    }
}