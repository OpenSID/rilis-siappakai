<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Sentry\Laravel\Facade as Sentry;

class TestSentryCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sentry:test';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test Sentry error tracking integration';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Testing Sentry integration...');

        // Check if Sentry is configured
        $dsn = config('sentry.dsn');
        if (empty($dsn)) {
            $this->error('Sentry DSN is not configured.');
            return 1;
        }

        $this->info('Sentry DSN configured: ' . substr($dsn, 0, 30) . '...');

        // Test capturing a message
        try {
            Sentry::captureMessage('Test message from Laravel command');
            $this->info('✓ Test message sent to Sentry');
        } catch (\Exception $e) {
            $this->error('Failed to send message to Sentry: ' . $e->getMessage());
            return 1;
        }

        // Test capturing an exception
        try {
            $testException = new \Exception('Test exception from Laravel - Sentry integration working');
            Sentry::captureException($testException);
            $this->info('✓ Test exception sent to Sentry');
        } catch (\Exception $e) {
            $this->error('Failed to send exception to Sentry: ' . $e->getMessage());
            return 1;
        }

        $this->info('✓ Sentry integration test completed successfully!');
        $this->info('Check your Sentry dashboard for the test messages.');

        return 0;
    }
}