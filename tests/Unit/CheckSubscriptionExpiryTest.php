<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Console\Commands\CheckSubscriptionExpiry;
use App\Contracts\SubscriptionExpiryServiceInterface;
use App\Contracts\WebsiteDeactivationServiceInterface;
use App\Models\Pelanggan;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;

class CheckSubscriptionExpiryTest extends TestCase
{
    use RefreshDatabase;

    protected $command;
    protected $subscriptionServiceMock;
    protected $deactivationServiceMock;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->subscriptionServiceMock = Mockery::mock(SubscriptionExpiryServiceInterface::class);
        $this->deactivationServiceMock = Mockery::mock(WebsiteDeactivationServiceInterface::class);
        
        $this->command = new CheckSubscriptionExpiry(
            $this->subscriptionServiceMock,
            $this->deactivationServiceMock
        );
    }

    public function test_command_exists()
    {
        $this->assertInstanceOf(CheckSubscriptionExpiry::class, $this->command);
    }

    public function test_command_has_correct_signature()
    {
        $this->assertEquals('siappakai:check-subscription-expiry', $this->command->getName());
        $this->assertStringContainsString('dry-run', $this->command->getDefinition()->getOption('dry-run')->getDescription());
    }

    public function test_command_uses_dependency_injection()
    {
        // Test that the command properly uses the injected services
        $reflection = new \ReflectionClass($this->command);
        $subscriptionServiceProperty = $reflection->getProperty('subscriptionExpiryService');
        $subscriptionServiceProperty->setAccessible(true);
        
        $deactivationServiceProperty = $reflection->getProperty('websiteDeactivationService');
        $deactivationServiceProperty->setAccessible(true);

        $this->assertInstanceOf(SubscriptionExpiryServiceInterface::class, $subscriptionServiceProperty->getValue($this->command));
        $this->assertInstanceOf(WebsiteDeactivationServiceInterface::class, $deactivationServiceProperty->getValue($this->command));
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}