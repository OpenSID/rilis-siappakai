<?php

namespace App\Repositories;

use App\Models\ApiHealthCheck;
use App\Models\Pelanggan;
use App\Repositories\Contracts\ApiHealthCheckRepositoryInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ApiHealthCheckRepository implements ApiHealthCheckRepositoryInterface
{
    /**
     * Insert or update health check untuk pelanggan (simpan 1 record terakhir per pelanggan)
     */
    public function upsert(Pelanggan $pelanggan, array $data): ApiHealthCheck
    {
        return ApiHealthCheck::updateOrCreate(
            ['pelanggan_id' => $pelanggan->id],
            [
                'status'        => $data['status'],
                'http_status'   => $data['http_status'] ?? null,
                'message'       => $data['message'] ?? null,
                'response_data' => $data['response_data'] ?? null,
                'checked_at'    => $data['checked_at'] ?? now(),
            ]
        );
    }

    /**
     * Get latest health check per pelanggan (join dengan tabel pelanggan)
     */
    public function getLatestPerPelanggan(): Collection
    {
        return Pelanggan::select([
                'pelanggan.id',
                'pelanggan.kode_desa',
                'pelanggan.nama_desa',
                'pelanggan.domain_opensid',
                'pelanggan.nama_kecamatan',
                'pelanggan.nama_kabupaten',
                'api_health_checks.status as health_status',
                'api_health_checks.http_status',
                'api_health_checks.message',
                'api_health_checks.response_data',
                'api_health_checks.checked_at',
            ])
            ->leftJoin('api_health_checks', 'pelanggan.id', '=', 'api_health_checks.pelanggan_id')
            ->whereNotNull('pelanggan.domain_opensid')
            ->where('pelanggan.domain_opensid', '!=', '-')
            ->where('pelanggan.domain_opensid', '!=', '')
            ->orderByRaw("CASE WHEN api_health_checks.status = 'error' THEN 0 WHEN api_health_checks.status IS NULL THEN 1 ELSE 2 END")
            ->orderBy('pelanggan.nama_desa')
            ->get();
    }

    /**
     * Get health check untuk satu pelanggan
     */
    public function getByPelanggan(Pelanggan $pelanggan): ?ApiHealthCheck
    {
        return ApiHealthCheck::where('pelanggan_id', $pelanggan->id)->first();
    }

    /**
     * Get summary counts
     */
    public function getSummary(): array
    {
        $totalPelanggan = Pelanggan::whereNotNull('domain_opensid')
            ->where('domain_opensid', '!=', '-')
            ->where('domain_opensid', '!=', '')
            ->count();

        $checks = ApiHealthCheck::select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();

        $success = $checks['success'] ?? 0;
        $error   = $checks['error'] ?? 0;
        $pending = $checks['pending'] ?? 0;
        $checked = $success + $error;

        $lastChecked = ApiHealthCheck::max('checked_at');

        return [
            'total'              => $totalPelanggan,
            'checked'            => $checked,
            'belum_dicek'        => $totalPelanggan - $checked - $pending,
            'success'            => $success,
            'error'              => $error,
            'pending'            => $pending,
            'success_percentage' => $checked > 0
                ? round(($success / $checked) * 100, 1)
                : 0,
            'last_checked'       => $lastChecked,
        ];
    }
}
