<?php

namespace App\Services;

use App\Contracts\WebsiteDeactivationServiceInterface;
use App\Http\Controllers\Helpers\AttributeSiapPakaiController;
use App\Http\Controllers\Helpers\CommandController;
use App\Http\Controllers\Helpers\VhostController;
use App\Models\Pelanggan;
use App\Services\ProcessService;
use Illuminate\Filesystem\Filesystem;

class WebsiteDeactivationService implements WebsiteDeactivationServiceInterface
{
    public function __construct(
        private readonly AttributeSiapPakaiController $attributeController,
        private readonly CommandController $commandController,
        private readonly VhostController $vhostController,
        private readonly ProcessService $processService,
        private readonly Filesystem $filesystem
    ) {}

    /**
     * Deactivate website by redirecting to expired page
     *
     * @param Pelanggan $pelanggan
     * @param bool $isDryRun
     * @return bool
     */
    public function deactivateWebsite(Pelanggan $pelanggan, bool $isDryRun = false): bool
    {
        $domain = $pelanggan->domain_opensid;

        if (!$domain) {
            return false;
        }

        // Sanitize domain for safety
        $domain = preg_replace('/[^a-zA-Z0-9.-]/', '', $domain);
        if (!$domain) {
            return false;
        }

        if ($isDryRun) {
            return true; // Would deactivate in real execution
        }

        try {
            // Check if already expired - skip vhost update if already configured
            if ($this->isAlreadyExpired($domain)) {
                return true; // Already expired, no need to update
            }

            // Backup existing vhost if it exists
            $this->backupExistingVhost($domain);

            // Remove SSL configuration file if it exists
            $this->removeSslConfiguration($domain);

            // Create expired vhost configuration
            $this->createExpiredVhost($domain);

            // Restart Apache to apply changes
            $this->restartApache();

            // Revalidate SSL certificate (non-blocking)
            $this->revalidateSsl($domain);

            return true;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Validate environment requirements
     *
     * @return array
     */
    public function validateEnvironment(): array
    {
        $errors = [];

        $rootFolder = config('siappakai.root.folder');
        if (!$rootFolder) {
            $errors[] = 'Configuration siappakai.root.folder not found';
        }

        $expiredPagePath = $rootFolder . 'dasbor-siappakai/storage/app/halaman/expired.html';
        if (!file_exists($expiredPagePath)) {
            $errors[] = "Expired page not found: {$expiredPagePath}";
        }

        $apacheConfDir = $this->attributeController->getApacheConfDir();
        if (!is_dir($apacheConfDir)) {
            $errors[] = "Apache configuration directory not found: {$apacheConfDir}";
        }

        if (!is_writable($apacheConfDir)) {
            $errors[] = "Apache configuration directory is not writable: {$apacheConfDir}";
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }

    /**
     * Check if domain is already configured for expired.html
     *
     * @param string $domain
     * @return bool
     */
    private function isAlreadyExpired(string $domain): bool
    {
        $confFilePath = $this->attributeController->getApacheConfDir() . $domain . '.conf';

        if (!file_exists($confFilePath)) {
            return false;
        }

        $confContent = file_get_contents($confFilePath);
        if ($confContent === false) {
            return false;
        }

        // Check if the vhost configuration contains expired.html references
        return str_contains($confContent, 'expired.html') &&
               str_contains($confContent, 'DirectoryIndex expired.html') &&
               str_contains($confContent, 'RewriteRule ^.*$ expired.html [L]');
    }

    /**
     * Backup existing vhost configuration if it exists
     *
     * @param string $domain
     * @return void
     */
    private function backupExistingVhost(string $domain): void
    {
        $confFilePath = $this->attributeController->getApacheConfDir() . $domain . '.conf';

        if (file_exists($confFilePath)) {
            $backupPath = $confFilePath . '.backup.' . date('Y-m-d_H-i-s');
            copy($confFilePath, $backupPath);
        }
    }

    /**
     * Remove SSL configuration file if it exists
     *
     * @param string $domain
     * @return void
     */
    private function removeSslConfiguration(string $domain): void
    {
        $sslConfFilePath = $this->attributeController->getApacheConfDir() . $domain . '-le-ssl.conf';

        if (file_exists($sslConfFilePath)) {
            // Backup SSL configuration before removing
            $backupPath = $sslConfFilePath . '.backup.' . date('Y-m-d_H-i-s');
            copy($sslConfFilePath, $backupPath);

            // Disable the SSL site first
            $process = $this->processService::runProcess(
                ['sudo', 'a2dissite', "{$domain}-le-ssl.conf"],
                base_path(),
                "Disabling Apache SSL site {$domain}-le-ssl.conf..."
            );

            if (!$process->isSuccessful()) {
                // Log warning but don't throw exception - site might already be disabled
            }

            // Remove SSL configuration file
            if (!unlink($sslConfFilePath)) {
                throw new \Exception("Failed to remove SSL configuration file: {$sslConfFilePath}");
            }
        }
    }    /**
     * Create vhost configuration that redirects to expired page
     *
     * @param string $domain
     * @return void
     */
    private function createExpiredVhost(string $domain): void
    {
        $rootFolder = config('siappakai.root.folder');
        $expiredPagePath = $rootFolder . 'dasbor-siappakai/storage/app/halaman/expired.html';
        $confFilePath = $this->attributeController->getApacheConfDir() . $domain . '.conf';

        // Validate paths
        if (!file_exists($expiredPagePath)) {
            throw new \Exception("Expired page not found: {$expiredPagePath}");
        }

        $confDir = dirname($confFilePath);
        if (!is_dir($confDir)) {
            throw new \Exception("Apache configuration directory not found: {$confDir}");
        }

        if (!is_writable($confDir)) {
            throw new \Exception("Apache configuration directory is not writable: {$confDir}");
        }

        // Create expired vhost configuration
        $vhostContent = $this->generateExpiredVhostContent($domain, $expiredPagePath);

        // Write the configuration file
        if (file_put_contents($confFilePath, $vhostContent) === false) {
            throw new \Exception("Failed to write vhost configuration: {$confFilePath}");
        }

        // Enable the site
        $process = $this->processService::runProcess(
            ['sudo', 'a2ensite', "{$domain}.conf"],
            base_path(),
            "Enabling Apache site {$domain}.conf..."
        );

        if (!$process->isSuccessful()) {
            // Log warning but don't throw exception - site might already be enabled
        }
    }

    /**
     * Generate expired vhost configuration content
     *
     * @param string $domain
     * @param string $expiredPagePath
     * @return string
     */
    private function generateExpiredVhostContent(string $domain, string $expiredPagePath): string
    {
        $expiredPageDir = dirname($expiredPagePath);
        $domainLog = str_replace('.', '', $domain);

        return <<<CONF
<VirtualHost *:80>
    ServerName {$domain}
    ServerAlias www.{$domain}
    ServerAdmin webmaster@localhost
    DocumentRoot {$expiredPageDir}

    <Directory {$expiredPageDir}>
        Options FollowSymLinks Indexes Includes
        AllowOverride All
        Require all granted
        Allow from all
        DirectoryIndex expired.html
        RewriteEngine On
        RewriteRule ^.*$ expired.html [L]
    </Directory>

    ErrorLog \${APACHE_LOG_DIR}/{$domainLog}_error.log
    CustomLog \${APACHE_LOG_DIR}/{$domainLog}_access.log combined
</VirtualHost>
CONF;
    }

    /**
     * Restart Apache server
     *
     * @return void
     */
    private function restartApache(): void
    {
        try {
            $this->processService::runProcess(
                ['sudo', 'service', 'apache2', 'restart'],
                base_path(),
                "Restarting Apache server..."
            );
        } catch (\Exception $e) {
            // Log error but don't throw - Apache restart failure shouldn't stop the process
        }
    }

    /**
     * Revalidate SSL certificate for the domain
     *
     * @param string $domain
     * @return void
     */
    private function revalidateSsl(string $domain): void
    {
        try {
            $this->commandController->certbotSsl($domain);
        } catch (\Exception $e) {
            // SSL revalidation failed - log but don't throw
        }
    }
}
