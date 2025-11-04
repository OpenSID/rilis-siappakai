<?php

namespace App\Repositories;

use App\Contracts\TemplateRepositoryInterface;
use App\Enums\Level;
use App\Http\Controllers\Helpers\AttributeSiapPakaiController;
use App\Models\Aplikasi;
use App\Models\Pelanggan;
use App\Services\EmailService;
use App\Services\FileService;
use App\Services\TemaService;
use Illuminate\Support\Collection;

class TemplateRepository implements TemplateRepositoryInterface
{
    private FileService $fileService;
    private AttributeSiapPakaiController $attributeController;

    public function __construct()
    {
        $this->fileService = new FileService();
        $this->attributeController = new AttributeSiapPakaiController();
    }

    /**
     * Memproses template untuk semua pelanggan
     *
     * @return void
     */
    public function processAllCustomerTemplates(): void
    {
        $customers = $this->getAllCustomers();
        $templateConfigPath = $this->getTemplateConfigPath();

        foreach ($customers as $customer) {
            $this->processSingleCustomerTemplate($customer, $templateConfigPath);
        }
    }

    /**
     * Memproses template untuk pelanggan tertentu
     *
     * @param Pelanggan $customer
     * @return void
     */
    public function processCustomerTemplate(Pelanggan $customer): void
    {
        $templateConfigPath = $this->getTemplateConfigPath();
        $this->processSingleCustomerTemplate($customer, $templateConfigPath);
    }

    /**
     * Mendapatkan semua pelanggan
     *
     * @return Collection
     */
    public function getAllCustomers(): Collection
    {
        return Pelanggan::all();
    }

    /**
     * Mendapatkan path template konfigurasi
     *
     * @return string
     */
    public function getTemplateConfigPath(): string
    {
        $folderTemplate = base_path() . DIRECTORY_SEPARATOR . 'master-template' . DIRECTORY_SEPARATOR . 'template-opensid';
        return $folderTemplate . DIRECTORY_SEPARATOR . 'desa' . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'config.php';
    }

    /**
     * Mendapatkan path konfigurasi pelanggan
     *
     * @param Pelanggan $customer
     * @return string
     */
    public function getCustomerConfigPath(Pelanggan $customer): string
    {
        $folder_opensid = str_replace('.', '', $customer->kode_desa);
        return config('siappakai.root.folder_multisite') . $folder_opensid . DIRECTORY_SEPARATOR . 'desa' . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'config.php';
    }

    /**
     * Memproses template untuk satu pelanggan
     *
     * @param Pelanggan $customer
     * @param string $templateConfigPath
     * @return void
     */
    private function processSingleCustomerTemplate(Pelanggan $customer, string $templateConfigPath): void
    {
        $customerConfigPath = $this->getCustomerConfigPath($customer);
        
        $replace = $this->buildReplaceArray($customer);
        
        if ($customer->domain_opensid != '-') {
            $additionalConfig = $this->buildAdditionalConfiguration($customer);
            $replace = array_merge($replace, $additionalConfig);
        }

        $this->fileService->processTemplate($templateConfigPath, $customerConfigPath, $replace);
    }

    /**
     * Membuat array replacement untuk template
     *
     * @param Pelanggan $customer
     * @return array
     */
    private function buildReplaceArray(Pelanggan $customer): array
    {
        $serverLayanan = $this->attributeController->getServerLayanan();
        $temaBawaan = Aplikasi::where('key', 'tema_bawaan')->first()->value ?? '';
        $tingkatanDatabase = Aplikasi::where('key', 'level')->first()->value ?? Level::Kabupaten->value;

        return [
            '{$kodedesa}' => $customer->kode_desa_without_dot,
            '{$token_premium}' => $customer->token_premium,
            '{$server_layanan}' => $serverLayanan,
            '{$web_theme}' => $temaBawaan,
            '{$tingkatan_database}' => $tingkatanDatabase
        ];
    }

    /**
     * Membuat konfigurasi tambahan untuk domain khusus
     *
     * @param Pelanggan $customer
     * @return array
     */
    private function buildAdditionalConfiguration(Pelanggan $customer): array
    {
        $configTema = TemaService::getAtribute($customer->kode_desa);
        $configEmail = EmailService::getEmailConfiguration($customer->id);

        return [
            '{$aktivasi_tema}' => $configTema['aktivasi_tema'],
            '{$config_logo}' => $configTema['logo'],
            '{$config_kode_kota}' => $configTema['kode_kota'],
            '{$config_fbadmin}' => $configTema['fbadmin'],
            '{$config_fbappid}' => $configTema['fbappid'],
            '{$config_ip_address}' => $configTema['ip_address'],
            '{$config_color}' => $configTema['color'],
            '{$config_fluid}' => $configTema['fluid'],
            '{$config_menu}' => $configTema['menu'],
            '{$config_chats}' => $configTema['chats'],
            '{$config_widget}' => $configTema['widget'],
            '{$config_style}' => $configTema['style'],
            '{$config_hide_layanan}' => $configTema['hide_layanan'],
            '{$config_hide_banner_layanan}' => $configTema['hide_banner_layanan'],
            '{$config_hide_banner_laporan}' => $configTema['hide_banner_laporan'],
            '{$smtp_protocol}' => $configEmail['smtp_protocol'],
            '{$smtp_host}' => $configEmail['smtp_host'],
            '{$smtp_user}' => $configEmail['smtp_user'],
            '{$smtp_pass}' => $configEmail['smtp_pass'],
            '{$smtp_port}' => $configEmail['smtp_port'],
        ];
    }
}