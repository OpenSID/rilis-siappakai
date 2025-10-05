<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Pelanggan;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;

class CheckSubscriptionExpiryCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_command_registers_successfully()
    {
        // Test that the command is registered and can be called
        $exitCode = Artisan::call('siappakai:check-subscription-expiry', ['--dry-run' => true]);
        
        // Command should execute without fatal errors
        $this->assertIsInt($exitCode);
        
        // Get the output to ensure command ran
        $output = Artisan::output();
        $this->assertStringContainsString('Starting subscription expiry check', $output);
        $this->assertStringContainsString('DRY RUN MODE', $output);
    }

    public function test_dry_run_mode_does_not_modify_data()
    {
        // Create test customer with expired premium subscription
        $pelanggan = Pelanggan::create([
            'kode_desa' => '1234567890123456',
            'nama_desa' => 'Test Desa',
            'domain_opensid' => 'test.example.com',
            'langganan_opensid' => 'premium',
            'tgl_akhir_premium' => Carbon::now()->subDays(1),
            'tgl_akhir_saas' => Carbon::createFromDate(9999, 12, 31),
            'status_langganan_opensid' => 1, // Active
            'status_langganan_saas' => 1,    // Active
        ]);

        $originalStatus = $pelanggan->status_langganan_opensid;

        // Run command in dry-run mode
        Artisan::call('siappakai:check-subscription-expiry', ['--dry-run' => true]);

        // Verify data was not changed
        $pelanggan->refresh();
        $this->assertEquals($originalStatus, $pelanggan->status_langganan_opensid);
    }

    public function test_command_signature_includes_dry_run_option()
    {
        $command = new \App\Console\Commands\CheckSubscriptionExpiry();
        $signature = $command->getSignature();
        
        $this->assertStringContainsString('--dry-run', $signature);
        $this->assertStringContainsString('Show what would be done without making changes', $signature);
    }
}