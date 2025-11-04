<?php

namespace App\Contracts;

use App\Models\Pelanggan;
use Illuminate\Support\Collection;

interface TemplateRepositoryInterface
{
    /**
     * Memproses template untuk semua pelanggan
     *
     * @return void
     */
    public function processAllCustomerTemplates(): void;

    /**
     * Memproses template untuk pelanggan tertentu
     *
     * @param Pelanggan $customer
     * @return void
     */
    public function processCustomerTemplate(Pelanggan $customer): void;

    /**
     * Mendapatkan semua pelanggan
     *
     * @return Collection
     */
    public function getAllCustomers(): Collection;

    /**
     * Mendapatkan path template konfigurasi
     *
     * @return string
     */
    public function getTemplateConfigPath(): string;

    /**
     * Mendapatkan path konfigurasi pelanggan
     *
     * @param Pelanggan $customer
     * @return string
     */
    public function getCustomerConfigPath(Pelanggan $customer): string;
}