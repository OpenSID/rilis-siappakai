<?php

namespace App\Console\Commands;

use App\Services\OpenLiteSpeedService;
use Illuminate\Console\Command;

class InstallAcme extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'ssl:install-acme 
                           {--check : Only check if acme.sh is installed}
                           {--force : Force reinstallation even if already installed}
                           {--email= : Email address for acme.sh registration}';

    /**
     * The console command description.
     */
    protected $description = 'Install acme.sh for SSL certificate management';

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
        $this->info('Acme.sh Installation Manager for OpenLiteSpeed');
        $this->line('==============================================');

        try {
            // Check if only checking installation status
            if ($this->option('check')) {
                return $this->checkInstallation();
            }

            // Check current installation status
            $isInstalled = $this->isAcmeInstalled();
            
            if ($isInstalled && !$this->option('force')) {
                $this->info('✓ acme.sh is already installed and ready to use!');
                $this->showInstallationInfo();
                return 0;
            }

            if ($isInstalled && $this->option('force')) {
                $this->warn('acme.sh is already installed. Forcing reinstallation...');
            }

            // Proceed with installation
            return $this->performInstallation();

        } catch (\Exception $e) {
            $this->error('Error: ' . $e->getMessage());
            return 1;
        }
    }

    /**
     * Check installation status
     */
    private function checkInstallation(): int
    {
        $this->info('Checking acme.sh installation status...');
        $this->line('');

        $acmePath = config('siappakai.ssl.acme_path', '/root/.acme.sh/acme.sh');
        $isInstalled = file_exists($acmePath) && is_executable($acmePath);

        if ($isInstalled) {
            $this->info('✓ acme.sh is installed and executable');
            $this->line("Location: {$acmePath}");
            
            // Try to get version
            try {
                $process = \Symfony\Component\Process\Process::fromShellCommandline("{$acmePath} --version");
                $process->run();
                
                if ($process->isSuccessful()) {
                    $version = trim($process->getOutput());
                    $this->line("Version: {$version}");
                }
            } catch (\Exception $e) {
                $this->warn('Could not determine version');
            }

            $this->showInstallationInfo();
            return 0;
        } else {
            $this->warn('✗ acme.sh is not installed or not executable');
            $this->line("Expected location: {$acmePath}");
            $this->line('');
            $this->info('To install acme.sh, run:');
            $this->line('  php artisan ssl:install-acme');
            return 1;
        }
    }

    /**
     * Perform acme.sh installation
     */
    private function performInstallation(): int
    {
        $email = $this->option('email') ?? config('siappakai.ssl.default_email');
        
        if (!$email) {
            $email = $this->ask('Enter email address for acme.sh registration');
            
            if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $this->error('Valid email address is required');
                return 1;
            }
        }

        $this->info("Installing acme.sh with email: {$email}");
        $this->line('');

        // Show what will be installed
        $this->line('Installation details:');
        $this->line('  • Download acme.sh from: https://get.acme.sh');
        $this->line('  • Install to: /root/.acme.sh/');
        $this->line('  • Set default CA: Let\'s Encrypt');
        $this->line('  • Register with email: ' . $email);
        $this->line('');

        if (!$this->confirm('Proceed with installation?')) {
            $this->info('Installation cancelled.');
            return 0;
        }

        $this->info('Starting installation...');
        
        try {
            // Use the service to install acme.sh
            $success = $this->openLiteSpeedService->installAcmeIfNeeded();
            
            if ($success) {
                $this->info('✓ acme.sh installed successfully!');
                $this->line('');
                $this->showInstallationInfo();
                $this->showNextSteps();
                return 0;
            } else {
                $this->error('✗ Installation failed');
                $this->showManualInstallation();
                return 1;
            }

        } catch (\Exception $e) {
            $this->error('✗ Installation failed: ' . $e->getMessage());
            $this->showManualInstallation();
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

    /**
     * Show installation information
     */
    private function showInstallationInfo(): void
    {
        $acmePath = config('siappakai.ssl.acme_path', '/root/.acme.sh/acme.sh');
        $certDir = config('siappakai.ssl.cert_directory', '/usr/local/lsws/conf/cert');
        
        $this->line('');
        $this->info('Installation Information:');
        $this->line("  Executable: {$acmePath}");
        $this->line("  Certificate directory: {$certDir}");
        $this->line("  Default CA: " . config('siappakai.ssl.ca_server', 'letsencrypt'));
        $this->line("  Staging mode: " . (config('siappakai.ssl.staging', false) ? 'Enabled' : 'Disabled'));
    }

    /**
     * Show next steps after installation
     */
    private function showNextSteps(): void
    {
        $this->line('');
        $this->info('Next Steps:');
        $this->line('1. Generate SSL certificate for a domain:');
        $this->line('   php artisan ssl:manage generate example.com');
        $this->line('');
        $this->line('2. Check certificate status:');
        $this->line('   php artisan ssl:manage check example.com');
        $this->line('');
        $this->line('3. Create virtual host with SSL:');
        $this->line('   Use OpenLiteSpeedService::createVirtualHost() with enableSSL=true');
        $this->line('');
        $this->comment('For more information, see: docs/SSL-Certificate-Management.md');
    }

    /**
     * Show manual installation instructions
     */
    private function showManualInstallation(): void
    {
        $this->line('');
        $this->warn('Manual Installation Instructions:');
        $this->line('');
        $this->line('1. Install acme.sh manually:');
        $this->line('   curl https://get.acme.sh | sh -s email=your@email.com');
        $this->line('');
        $this->line('2. Set default CA:');
        $this->line('   ~/.acme.sh/acme.sh --set-default-ca --server letsencrypt');
        $this->line('');
        $this->line('3. Update your .env file:');
        $this->line('   ACME_PATH=/root/.acme.sh/acme.sh');
        $this->line('   SSL_DEFAULT_EMAIL=your@email.com');
        $this->line('');
        $this->line('4. Test installation:');
        $this->line('   php artisan ssl:install-acme --check');
    }
}