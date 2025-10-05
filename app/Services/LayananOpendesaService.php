<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class LayananOpendesaService
{
    protected $client;
    protected $baseUrl;

    public function __construct()
    {
        $server_layanan = env('SERVER_LAYANAN', 'https://layanan.opendesa.id');
        $this->baseUrl = rtrim($server_layanan, '/') . '/api/v1/';
        $this->client = new Client([
            'base_uri' => $this->baseUrl,
            'timeout'  => 120,
        ]);
    }

    /**
     * Melakukan request ke endpoint pemesanan pelanggan
     * @param string $token Bearer token
     * @param array $data Data untuk dikirim (opsional)
     * @return array|null
     */
    public function pemesananPelanggan(string $token, array $data = [])
    {
        try {
            $response = $this->client->request('GET', 'pelanggan/pemesanan/', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $token,
                    'X-Requested-With' => 'XMLHttpRequest',
                ],
                'query' => $data,
                'timeout' => 120, // 2 menit
            ]);
            return json_decode($response->getBody()->getContents(), true);
        } catch (RequestException $e) {
            // Log error jika perlu
            return [
                'error' => true,
                'message' => $e->getMessage(),
                'response' => $e->hasResponse() ? $e->getResponse()->getBody()->getContents() : null,
            ];
        }
    }

    public function pemesananTema(string $token, array $data = [])
    {
        try {
            $response = $this->client->request('GET', 'pelanggan/pemesanan-tema/', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $token,
                    'X-Requested-With' => 'XMLHttpRequest',
                ],
                'query' => $data,
                'timeout' => 120, // 2 menit
            ]);
            return json_decode($response->getBody()->getContents(), true);
        } catch (RequestException $e) {
            // Log error jika perlu
            return [
                'error' => true,
                'message' => $e->getMessage(),
                'response' => $e->hasResponse() ? $e->getResponse()->getBody()->getContents() : null,
            ];
        }
    }
}
