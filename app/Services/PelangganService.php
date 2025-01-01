<?php

namespace App\Services;

use App\Models\Pelanggan;
use App\Jobs\UpdateDomainJob;

class PelangganService
{
    function perbaruiDomain(int $id, $domain)
    {
        try {
            $pelanggan = Pelanggan::find($id);
            $pelanggan->domain_opensid = $domain;
            $pelanggan->save();

            $params = [
                'kode_desa' => $pelanggan->kode_desa,
                'token_premium' => $pelanggan->token_premium,
                'kode_desa_default' => $pelanggan->kode_desa,
                'domain_opensid_lama' => $pelanggan->domain_opensid,
                'domain_opensid' => $domain
            ];

            // jalankan job update domain secara async
            UpdateDomainJob::dispatch($params);
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public static function langganan(Pelanggan $pelanggan)
    {
        $tglakhir = $pelanggan->tgl_akhir_premium;
        $hariini = date('Y-m-d');
        $selisih = (strtotime($hariini) - strtotime($tglakhir)) / 60 / 60 / 24;

        if ($selisih <= 30) {
            $langganan = 'premium';
        } else if ($selisih <= 60) {
            $langganan = 'premium_1';
        } else if ($selisih <= 90) {
            $langganan = 'premium_2';
        } else if ($selisih <= 120) {
            $langganan = 'premium_3';
        } else if ($selisih <= 150) {
            $langganan = 'premium_4';
        } else if ($selisih <= 180) {
            $langganan = 'premium_5';
        } else {
            $langganan = 'umum';
        }

        return $langganan;
    }

    public static function updatePelanggan(array $update, int $id): void
    {
        Pelanggan::where('id', $id)->update($update);
    }
}
