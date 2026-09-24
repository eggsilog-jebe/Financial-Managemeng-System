<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use App\Models\UserActiveSession;
use App\Services\Accounting\AccountingCacheService;
use App\Services\Security\ActiveSessionManagerService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;
use Tests\TestCase;

final class RedisCacheIntegrationTest extends TestCase
{
    use RefreshDatabase;

    private bool $redisAvailable = false;

    protected function setUp(): void
    {
        parent::setUp();

        // Check if Redis server is available on 127.0.0.1:6379
        try {
            $client = new \Redis();
            $connected = @$client->connect('127.0.0.1', 6379, 1.0);
            if ($connected) {
                $client->ping();
                $this->redisAvailable = true;
            }
        } catch (\Throwable) {
            $this->redisAvailable = false;
        }
    }

    public function test_redis_connection_and_cache_operations(): void
    {
        if (! $this->redisAvailable) {
            $this->markTestSkipped('Redis server is not running on 127.0.0.1:6379.');
        }

        config(['cache.default' => 'redis']);

        Cache::store('redis')->put('fms:test:ping', 'pong', 60);
        $value = Cache::store('redis')->get('fms:test:ping');

        $this->assertSame('pong', $value);
    }

    public function test_accounting_cache_service_tagged_remember_and_invalidation(): void
    {
        if (! $this->redisAvailable) {
            $this->markTestSkipped('Redis server is not running on 127.0.0.1:6379.');
        }

        config(['cache.default' => 'redis']);
        $service = app(AccountingCacheService::class);
        $service->invalidateFinancialCaches();

        $callCount = 0;
        $callback = function () use (&$callCount): array {
            $callCount++;
            return ['totalLedgerBalance' => 1500000.50, 'totalAR' => 45000.00];
        };

        // First call: executes callback
        $result1 = $service->rememberDashboardMetrics($callback, 300);
        $this->assertSame(1, $callCount);
        $this->assertEquals(1500000.50, $result1['totalLedgerBalance']);

        // Second call: served from Redis cache (callback is not called again)
        $result2 = $service->rememberDashboardMetrics($callback, 300);
        $this->assertSame(1, $callCount);
        $this->assertEquals($result1, $result2);

        // Invalidate financial caches
        $service->invalidateFinancialCaches();

        // Third call: cache was flushed, callback is executed again
        $result3 = $service->rememberDashboardMetrics($callback, 300);
        $this->assertSame(2, $callCount);
        $this->assertEquals(1500000.50, $result3['totalLedgerBalance']);
    }

    public function test_active_session_manager_redis_fast_path(): void
    {
        if (! $this->redisAvailable) {
            $this->markTestSkipped('Redis server is not running on 127.0.0.1:6379.');
        }

        config(['cache.default' => 'redis']);
        $sessionManager = app(ActiveSessionManagerService::class);

        $user = User::factory()->create([
            'email' => 'doctor@hospital.local',
        ]);

        $sessionId1 = 'session_redis_alpha_001';
        $sessionId2 = 'session_redis_beta_002';
        $request = Request::create('/login', 'POST', [], [], [], ['REMOTE_ADDR' => '192.168.1.100']);

        // 1. Register first session
        $sessionManager->registerSession($user, $sessionId1, null, $request);

        // Assert session 1 is cached as active in Redis
        $this->assertSame($sessionId1, Cache::get("user:active_session:{$user->id}"));

        // checkSessionDisplacement for session 1 should immediately return null (active, valid)
        $this->assertNull($sessionManager->checkSessionDisplacement($sessionId1, $user));

        // 2. Register second session from another workstation (displacement event)
        $sessionManager->registerSession($user, $sessionId2, null, $request);

        // Assert session 2 is now cached as active in Redis
        $this->assertSame($sessionId2, Cache::get("user:active_session:{$user->id}"));

        // Assert session 1 is recorded as displaced in Redis
        $this->assertSame(UserActiveSession::REASON_DISPLACED, Cache::get("session:terminated:{$sessionId1}"));

        // Fast-path displacement check for session 1 immediately returns DISPLACED
        $this->assertSame(UserActiveSession::REASON_DISPLACED, $sessionManager->checkSessionDisplacement($sessionId1, $user));

        // Session 2 is active and valid
        $this->assertNull($sessionManager->checkSessionDisplacement($sessionId2, $user));
    }
}
