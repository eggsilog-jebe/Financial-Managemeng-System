<?php

declare(strict_types=1);

namespace App\Services\Cache;

use Closure;
use Illuminate\Cache\TaggableStore;
use Illuminate\Contracts\Cache\LockTimeoutException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Enterprise Multi-Layer Cache Engine with Stampede Protection
 *
 * Implements:
 * - Layer 1: In-Memory static/process cache (0ms lookup).
 * - Layer 2: Distributed Redis/store cache-aside.
 * - Single-Flight Mutex Locking (atomic Cache::lock) to prevent cache stampedes / thundering herd.
 * - Expiration Jitter (base_ttl + random jitter) to prevent simultaneous cascading expirations.
 */
final class MultiLayerCacheService
{
    public function __construct(
        private readonly L1InMemoryCache $l1,
    ) {}

    /**
     * Cache-Aside evaluation across L1 (In-Memory) and L2 (Distributed) with Single-Flight Mutex Locking.
     *
     * @param string $key Cache key
     * @param int $baseTtl Base time-to-live in seconds
     * @param Closure $callback Factory callback to execute on miss
     * @param array<int, string> $tags Optional cache tags for taggable stores
     * @param int $l1Ttl In-memory L1 cache TTL in seconds (default: 60s)
     * @return mixed
     */
    public function remember(
        string $key,
        int $baseTtl,
        Closure $callback,
        array $tags = [],
        int $l1Ttl = 60,
    ): mixed {
        // 1. Layer 1 Check: Instant process in-memory cache
        $l1Value = $this->l1->get($key);
        if ($l1Value !== null) {
            return $l1Value;
        }

        // 2. Layer 2 Check: Distributed cache
        $l2Value = $this->getFromL2($key, $tags);
        if ($l2Value !== null) {
            // Warm L1 in-memory cache for subsequent requests in this process
            $this->l1->put($key, $l2Value, $l1Ttl);
            return $l2Value;
        }

        // 3. Layer 3: Cache Miss — Single-Flight Atomic Mutex Lock
        return $this->resolveWithMutexLock($key, $baseTtl, $callback, $tags, $l1Ttl);
    }

    /**
     * Single-Flight Mutex Locking to prevent cache stampedes.
     * Only ONE process computes the heavy DB query while others wait.
     */
    private function resolveWithMutexLock(
        string $key,
        int $baseTtl,
        Closure $callback,
        array $tags,
        int $l1Ttl,
    ): mixed {
        $lockKey = "lock:cache:{$key}";
        $lockDuration = 15; // 15 seconds max lock lease
        $lockWaitSeconds = 6; // Wait up to 6 seconds for the leader to populate

        try {
            $lock = Cache::lock($lockKey, $lockDuration);

            // Attempt to acquire lock or block/wait
            $acquired = $lock->get();

            if (! $acquired) {
                // Another worker holds the lock; wait up to $lockWaitSeconds for it to finish
                try {
                    $lock->block($lockWaitSeconds);
                    $lockAcquiredByWait = true;
                } catch (LockTimeoutException) {
                    $lockAcquiredByWait = false;
                }

                // Check if the previous worker has already populated the cache
                $cachedValue = $this->getFromL2($key, $tags);
                if ($cachedValue !== null) {
                    $this->l1->put($key, $cachedValue, $l1Ttl);
                    if ($lockAcquiredByWait) {
                        try {
                            $lock->release();
                        } catch (Throwable) {}
                    }
                    return $cachedValue;
                }

                // If lock timed out and still no value, we proceed to compute as failover
            }

            try {
                // Double-checked locking: re-verify L2 after acquiring lock
                $cachedValue = $this->getFromL2($key, $tags);
                if ($cachedValue !== null) {
                    $this->l1->put($key, $cachedValue, $l1Ttl);
                    return $cachedValue;
                }

                // Execute the actual expensive computation (Database query / Aggregation)
                $computedValue = $callback();

                // Compute Expiration Jitter: base_ttl + rand(0, 300)
                $jitterSeconds = $this->calculateJitter($baseTtl);
                $jitteredTtl = $baseTtl + $jitterSeconds;

                // Save to L2 and L1
                $this->putToL2($key, $computedValue, $jitteredTtl, $tags);
                $this->l1->put($key, $computedValue, min($l1Ttl, $jitteredTtl));

                return $computedValue;
            } finally {
                try {
                    $lock->release();
                } catch (Throwable) {}
            }
        } catch (Throwable $e) {
            // Graceful fallback: If locking fails (e.g. store doesn't support locks), compute directly
            Log::debug("MultiLayerCache lock fallback for key [{$key}]: " . $e->getMessage());

            $value = $callback();
            $jitteredTtl = $baseTtl + $this->calculateJitter($baseTtl);

            try {
                $this->putToL2($key, $value, $jitteredTtl, $tags);
            } catch (Throwable) {}

            $this->l1->put($key, $value, min($l1Ttl, $jitteredTtl));

            return $value;
        }
    }

    /**
     * Calculate randomized expiration jitter (base_ttl + rand(0, 300)) to prevent simultaneous stampedes.
     */
    public function calculateJitter(int $baseTtl): int
    {
        $maxJitter = min(300, max(5, (int) ($baseTtl * 0.2)));
        return random_int(0, $maxJitter);
    }

    /**
     * Retrieve an item from L2 distributed cache.
     *
     * @param array<int, string> $tags
     */
    public function getFromL2(string $key, array $tags = []): mixed
    {
        try {
            if (! empty($tags) && $this->supportsTags()) {
                return Cache::tags($tags)->get($key);
            }

            return Cache::get($key);
        } catch (Throwable $e) {
            Log::warning("MultiLayerCache L2 get failed for [{$key}]: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Store an item in L2 distributed cache with jittered TTL.
     *
     * @param array<int, string> $tags
     */
    public function putToL2(string $key, mixed $value, int $ttlSeconds, array $tags = []): void
    {
        try {
            if (! empty($tags) && $this->supportsTags()) {
                Cache::tags($tags)->put($key, $value, $ttlSeconds);
                return;
            }

            Cache::put($key, $value, $ttlSeconds);
        } catch (Throwable $e) {
            Log::warning("MultiLayerCache L2 put failed for [{$key}]: " . $e->getMessage());
        }
    }

    /**
     * Invalidate a specific key across both L1 and L2.
     */
    public function forget(string $key): void
    {
        $this->l1->forget($key);

        try {
            Cache::forget($key);
        } catch (Throwable) {}
    }

    /**
     * Invalidate tags across L1 and L2.
     *
     * @param array<int, string> $tags
     */
    public function invalidateTags(array $tags): void
    {
        $this->l1->flush();

        try {
            if ($this->supportsTags()) {
                Cache::tags($tags)->flush();
            }
        } catch (Throwable) {}
    }

    /**
     * Flush all caches.
     */
    public function flush(): void
    {
        $this->l1->flush();

        try {
            Cache::flush();
        } catch (Throwable) {}
    }

    /**
     * Check if the underlying cache driver supports tagging.
     */
    public function supportsTags(): bool
    {
        return Cache::getStore() instanceof TaggableStore;
    }

    /**
     * Direct accessor to L1 memory cache.
     */
    public function l1(): L1InMemoryCache
    {
        return $this->l1;
    }
}
