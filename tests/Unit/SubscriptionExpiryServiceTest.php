<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\SubscriptionExpiryService;
use App\Services\PelangganService;
use App\Models\Pelanggan;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;

class SubscriptionExpiryServiceTest extends TestCase
{
    use RefreshDatabase;

    protected $service;
    protected $pelangganServiceMock;

    protected function setUp(): void
    {
        parent::setUp();
        $this->pelangganServiceMock = Mockery::mock(PelangganService::class);
        $this->service = new SubscriptionExpiryService($this->pelangganServiceMock);
    }

    public function test_should_deactivate_website_with_expired_premium_when_saas_year_is_9999()
    {
        $pelanggan = new Pelanggan([
            'tgl_akhir_premium' => Carbon::now()->subDays(1)->toDateString(),
            'tgl_akhir_saas' => Carbon::createFromDate(9999, 12, 31)->toDateString(),
        ]);

        $result = $this->service->shouldDeactivateWebsite($pelanggan);

        $this->assertTrue($result);
    }

    public function test_should_not_deactivate_website_with_valid_premium_when_saas_year_is_9999()
    {
        $pelanggan = new Pelanggan([
            'tgl_akhir_premium' => Carbon::now()->addDays(30)->toDateString(),
            'tgl_akhir_saas' => Carbon::createFromDate(9999, 12, 31)->toDateString(),
        ]);

        $result = $this->service->shouldDeactivateWebsite($pelanggan);

        $this->assertFalse($result);
    }

    public function test_should_deactivate_website_with_expired_saas_when_saas_year_is_not_9999()
    {
        $pelanggan = new Pelanggan([
            'tgl_akhir_premium' => Carbon::now()->addDays(30)->toDateString(),
            'tgl_akhir_saas' => Carbon::now()->subDays(1)->toDateString(),
        ]);

        $result = $this->service->shouldDeactivateWebsite($pelanggan);

        $this->assertTrue($result);
    }

    public function test_should_not_deactivate_website_with_valid_saas_when_saas_year_is_not_9999()
    {
        $pelanggan = new Pelanggan([
            'tgl_akhir_premium' => Carbon::now()->subDays(1)->toDateString(),
            'tgl_akhir_saas' => Carbon::now()->addDays(30)->toDateString(),
        ]);

        $result = $this->service->shouldDeactivateWebsite($pelanggan);

        $this->assertFalse($result);
    }

    public function test_should_not_deactivate_website_with_missing_dates()
    {
        $pelanggan = new Pelanggan([
            'tgl_akhir_premium' => null,
            'tgl_akhir_saas' => null,
        ]);

        $result = $this->service->shouldDeactivateWebsite($pelanggan);

        $this->assertFalse($result);
    }

    public function test_update_expired_status_updates_premium_status()
    {
        $pelanggan = new Pelanggan([
            'id' => 1,
            'domain_opensid' => 'test.example.com',
            'tgl_akhir_premium' => Carbon::now()->subDays(1)->toDateString(),
            'tgl_akhir_saas' => Carbon::now()->addDays(30)->toDateString(),
            'status_langganan_opensid' => 1,
            'status_langganan_saas' => 1,
        ]);

        $this->pelangganServiceMock
            ->shouldReceive('updatePelanggan')
            ->once()
            ->with(['status_langganan_opensid' => 3], 1);

        $result = $this->service->updateExpiredStatus($pelanggan, false);

        $this->assertEquals(1, $result['expired_count']);
        $this->assertCount(1, $result['messages']);
        $this->assertStringContainsString('Premium subscription expired', $result['messages'][0]);
    }

    public function test_update_expired_status_updates_saas_status()
    {
        $pelanggan = new Pelanggan([
            'id' => 1,
            'domain_opensid' => 'test.example.com',
            'tgl_akhir_premium' => Carbon::now()->addDays(30)->toDateString(),
            'tgl_akhir_saas' => Carbon::now()->subDays(1)->toDateString(),
            'status_langganan_opensid' => 1,
            'status_langganan_saas' => 1,
        ]);

        $this->pelangganServiceMock
            ->shouldReceive('updatePelanggan')
            ->once()
            ->with(['status_langganan_saas' => 3], 1);

        $result = $this->service->updateExpiredStatus($pelanggan, false);

        $this->assertEquals(1, $result['expired_count']);
        $this->assertCount(1, $result['messages']);
        $this->assertStringContainsString('SaaS subscription expired', $result['messages'][0]);
    }

    public function test_update_expired_status_dry_run_does_not_update()
    {
        $pelanggan = new Pelanggan([
            'id' => 1,
            'domain_opensid' => 'test.example.com',
            'tgl_akhir_premium' => Carbon::now()->subDays(1)->toDateString(),
            'tgl_akhir_saas' => Carbon::now()->subDays(1)->toDateString(),
            'status_langganan_opensid' => 1,
            'status_langganan_saas' => 1,
        ]);

        $this->pelangganServiceMock
            ->shouldNotReceive('updatePelanggan');

        $result = $this->service->updateExpiredStatus($pelanggan, true);

        $this->assertEquals(2, $result['expired_count']);
        $this->assertCount(2, $result['messages']);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}