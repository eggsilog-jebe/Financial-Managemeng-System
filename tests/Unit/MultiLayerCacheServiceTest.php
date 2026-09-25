<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Services\Cache\L1InMemoryCache;
use App\Services\Cache\MultiLayerCacheService;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

final class MultiLayerCacheServiceTest extends TestCase
{
    private L1InMemoryCache $l1;
    private MultiLayerCacheService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->l1 = new L1InMemoryCache();
        $this->l1->flush();
        $this->service = new MultiLayerCacheService($this->l1);
        Cache::flush();
    }

    public function test_l1_stores_and_retrieves_in_memory_immediately(): void
    {
        $this->assertNull($this->l1->get('test_key'));

        $this->l1->put('test_key', 'hello_world', 60);

        $this->assertTrue($this->l1->has('test_key'));
        $this->assertSame('hello_world', $this->l1->get('test_key'));

        $this->l1->forget('test_key');
        $this->assertNull($this->l1->get('test_key'));
    }

    public function test_remember_populates_l1_and_l2(): void
    {
        $callCount = 0;
        $callback = function () use (&$callCount): string {
            $callCount++;
            return 'computed_result';
        };

        // First call: executes callback
        $val1 = $this->service->remember('key_1', 300, $callback);
        $this->assertSame('computed_result', $val1);
        $this->assertSame(1, $callCount);

        // Second call: served from L1 memory (zero DB/Cache calls)
        $val2 = $this->service->remember('key_1', 300, $callback);
        $this->assertSame('computed_result', $val2);
        $this->assertSame(1, $callCount);

        // Flush L1: next call should be served from L2
        $this->l1->flush();
        $val3 = $this->service->remember('key_1', 300, $callback);
        $this->assertSame('computed_result', $val3);
        $this->assertSame(1, $callCount); // Callback was NOT called again
    }

    public function test_expiration_jitter_appends_randomized_seconds(): void
    {
        $baseTtl = 300;
        $jitter = $this->service->calculateJitter($baseTtl);

        $this->assertGreaterThanOrEqual(0, $jitter);
        $this->assertLessThanOrEqual(300, $jitter);
    }

    public function test_invalidation_clears_both_l1_and_l2(): void
    {
        $this->service->remember('key_to_forget', 300, fn (): string => 'data');

        $this->assertSame('data', $this->l1->get('key_to_forget'));

        $this->service->forget('key_to_forget');

        $this->assertNull($this->l1->get('key_to_forget'));
        $this->assertNull(Cache::get('key_to_forget'));
    }
}
