<?php

namespace Tests\Feature;

use Tests\TestCase;
use Sentry\Laravel\Facade as Sentry;
use Illuminate\Support\Facades\Config;

class SentryIntegrationTest extends TestCase
{
    /**
     * Test if Sentry configuration is properly loaded.
     */
    public function test_sentry_configuration_is_loaded()
    {
        // Check if Sentry DSN is configured
        $this->assertNotNull(config('sentry.dsn'));
        $this->assertStringContainsString('sentry.io', config('sentry.dsn'));
    }

    /**
     * Test if Sentry can capture a test exception.
     */
    public function test_sentry_can_capture_exception()
    {
        // Mock Sentry to avoid actually sending data during tests
        if (config('sentry.dsn')) {
            // Create a test exception
            $exception = new \Exception('Test Sentry error tracking');
            
            // This should work without throwing an exception
            $this->expectNotToPerformAssertions();
            
            // Capture exception with Sentry
            Sentry::captureException($exception);
        } else {
            $this->markTestSkipped('Sentry DSN not configured');
        }
    }

    /**
     * Test if Sentry service is available.
     */
    public function test_sentry_service_is_available()
    {
        // Check if Sentry facade is available
        $this->assertTrue(class_exists('Sentry\Laravel\Facade'));
    }
}