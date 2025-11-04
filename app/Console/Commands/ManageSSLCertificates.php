<?php

namespace App\Console\Commands;

use App\Services\OpenLiteSpeedService;
use Illuminate\Console\Command;

class ManageSSLCertificates extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'ssl:manage 
                           {action : Action to perform (generate|renew|check|remove)}
                           {domain : Domain name for the certificate}
                           {--email= : Email address for certificate registration}
                           {--force : Force renewal even if not near expiration}';

    /**
     * The console command description.
     */
    protected $description = 'Manage SSL certificates using acme.sh for OpenLiteSpeed';

    public function __construct(
        private readonly OpenLiteSpeedService $openLiteSpeedService
    ) {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $action = $this->argument('action');
        $domain = $this->argument('domain');
        $email = $this->option('email');

        $this->info("SSL Certificate Management for OpenLiteSpeed");
        $this->line("==============================================");
        $this->line("Domain: {$domain}");
        $this->line("Action: {$action}");
        $this->line('');

        try {
            // Check if acme.sh is installed first (except for check action)
            if ($action !== 'check' && !$this->isAcmeInstalled()) {
                $this->warn('acme.sh is not installed or not found!');
                
                if ($this->confirm('Would you like to install acme.sh now?')) {
                    $this->call('ssl:install-acme');
                    
                    // Check again after installation
                    if (!$this->isAcmeInstalled()) {
                        $this->error('acme.sh installation failed. Cannot proceed.');
                        return 1;
                    }
                } else {
                    $this->info('Please install acme.sh first:');
                    $this->line('  php artisan ssl:install-acme');
                    return 1;
                }
            }

            switch ($action) {
                case 'generate':
                    return $this->generateCertificate($domain, $email);
                
                case 'renew':
                    return $this->renewCertificate($domain);
                
                case 'check':
                    return $this->checkCertificate($domain);
                
                case 'remove':
                    return $this->removeCertificate($domain);
                
                default:
                    $this->error("Invalid action: {$action}");
                    $this->info("Available actions: generate, renew, check, remove");
                    return 1;
            }

        } catch (\Exception $e) {
            $this->error("Error: " . $e->getMessage());
            return 1;
        }
    }

    /**
     * Generate SSL certificate
     */
    private function generateCertificate(string $domain, ?string $email): int
    {
        $this->info("Generating SSL certificate for {$domain}...");

        // Check if certificate already exists
        $certStatus = $this->openLiteSpeedService->checkSSLCertificate($domain);
        
        if ($certStatus['exists'] && $certStatus['valid']) {
            $this->warn("Certificate already exists and is valid until: {$certStatus['expires_at']}");
            
            if (!$this->confirm('Do you want to regenerate the certificate?')) {
                return 0;
            }
        }

        $email = $email ?? config('siappakai.ssl.default_email');
        
        if (!$email) {
            $email = $this->ask('Enter email address for certificate registration');
        }

        $this->line("Using email: {$email}");
        $this->line('');

        $result = $this->openLiteSpeedService->generateSSLCertificate($domain, $email);

        if ($result['success']) {
            $this->info('✓ SSL certificate generated successfully!');
            $this->line("Certificate directory: {$result['cert_dir']}");
            $this->line("Certificate files:");
            $this->line("  - Certificate: {$result['cert_file']}");
            $this->line("  - Private Key: {$result['key_file']}");
            $this->line("  - CA Certificate: {$result['ca_file']}");
            $this->line("  - Full Chain: {$result['fullchain_file']}");
            
            return 0;
        } else {
            $this->error('✗ Failed to generate SSL certificate');
            return 1;
        }
    }

    /**
     * Renew SSL certificate
     */
    private function renewCertificate(string $domain): int
    {
        $this->info("Renewing SSL certificate for {$domain}...");

        // Check current certificate status
        $certStatus = $this->openLiteSpeedService->checkSSLCertificate($domain);
        
        if (!$certStatus['exists']) {
            $this->error("Certificate does not exist for {$domain}");
            $this->info("Use 'ssl:manage generate {$domain}' to create a new certificate");
            return 1;
        }

        if ($certStatus['valid'] && $certStatus['days_until_expiry'] > 30 && !$this->option('force')) {
            $this->info("Certificate is still valid for {$certStatus['days_until_expiry']} days");
            $this->info("Use --force to renew anyway");
            return 0;
        }

        $success = $this->openLiteSpeedService->renewSSLCertificate($domain);

        if ($success) {
            $this->info('✓ SSL certificate renewed successfully!');
            return 0;
        } else {
            $this->error('✗ Failed to renew SSL certificate');
            return 1;
        }
    }

    /**
     * Check SSL certificate status
     */
    private function checkCertificate(string $domain): int
    {
        $this->info("Checking SSL certificate for {$domain}...");
        $this->line('');

        $certStatus = $this->openLiteSpeedService->checkSSLCertificate($domain);

        if (!$certStatus['exists']) {
            $this->warn("No certificate found for {$domain}");
            $this->info("Use 'ssl:manage generate {$domain}' to create a certificate");
            return 1;
        }

        $this->info("Certificate Status:");
        $this->line("  Domain: {$domain}");
        $this->line("  Exists: " . ($certStatus['exists'] ? 'Yes' : 'No'));
        $this->line("  Valid: " . ($certStatus['valid'] ? 'Yes' : 'No'));
        
        if (isset($certStatus['expires_at'])) {
            $this->line("  Expires: {$certStatus['expires_at']}");
            $this->line("  Days until expiry: {$certStatus['days_until_expiry']}");
            
            if ($certStatus['days_until_expiry'] <= 30) {
                $this->warn("⚠ Certificate expires soon! Consider renewing.");
            } elseif ($certStatus['days_until_expiry'] <= 7) {
                $this->error("⚠ Certificate expires very soon! Renewal recommended.");
            } else {
                $this->info("✓ Certificate is valid and not expiring soon.");
            }
        }

        if (isset($certStatus['cert_file'])) {
            $this->line("  Certificate file: {$certStatus['cert_file']}");
        }

        if (isset($certStatus['error'])) {
            $this->error("  Error: {$certStatus['error']}");
        }

        return 0;
    }

    /**
     * Remove SSL certificate
     */
    private function removeCertificate(string $domain): int
    {
        $this->warn("Removing SSL certificate for {$domain}...");
        
        $certStatus = $this->openLiteSpeedService->checkSSLCertificate($domain);
        
        if (!$certStatus['exists']) {
            $this->info("No certificate found for {$domain}");
            return 0;
        }

        $this->line("This will remove:");
        $this->line("  - Certificate files from /usr/local/lsws/conf/cert/{$domain}/");
        $this->line("  - Certificate registration from acme.sh");
        $this->line('');

        if (!$this->confirm('Are you sure you want to remove the SSL certificate?')) {
            $this->info('Operation cancelled.');
            return 0;
        }

        $success = $this->openLiteSpeedService->removeSSLCertificate($domain);

        if ($success) {
            $this->info('✓ SSL certificate removed successfully!');
            return 0;
        } else {
            $this->error('✗ Failed to remove SSL certificate');
            return 1;
        }
    }

    /**
     * Check if acme.sh is installed
     */
    private function isAcmeInstalled(): bool
    {
        $acmePath = config('siappakai.ssl.acme_path', '/root/.acme.sh/acme.sh');
        return file_exists($acmePath) && is_executable($acmePath);
    }
}