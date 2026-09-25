<?php

declare(strict_types=1);

namespace App\Services\Cache;

use Closure;

/**
 * High-Speed In-Memory L1 Cache
 *
 * Provides sub-microsecond in-process caching for ultra-frequent, static, or semi-static lookups
 * (e.g., system configs, active session checks, user roles, feature flags).
 *
 * Completely eliminates network roundtrips and Redis socket calls within the request lifecycle.
 */
final class L1InMemoryCache
{
    /**
     * In-memory key-value store with expiration timestamps.
     *
     * @var array<string, array{value: mixed, expires_at: float}>
     */
    private static array $storage = [];

    /**
     * Retrieve an item from the L1 in-memory cache.
     */
    public function get(string $key): mixed
    {
        if (! isset(self::$storage[$key])) {
            return null;
        }

        $entry = self::$storage[$key];

        // Check if the cached entry has expired
        if (microtime(true) > $entry['expires_at']) {
            unset(self::$storage[$key]);
            return null;
        }

        return $entry['value'];
    }

    /**
     * Determine if an item exists in L1 in-memory cache and is not expired.
     */
    public function has(string $key): bool
    {
        return $this->get($key) !== null;
    }

    /**
     * Store an item in the L1 in-memory cache with a short TTL (default: 60s).
     */
    public function put(string $key, mixed $value, int $ttlSeconds = 60): void
    {
        self::$storage[$key] = [
            'value'      => $value,
            'expires_at' => microtime(true) + max(1, $ttlSeconds),
        ];
    }

    /**
     * Get an item from the L1 cache, or execute the given Closure and store the result.
     */
    public function remember(string $key, int $ttlSeconds, Closure $callback): mixed
    {
        $value = $this->get($key);

        if ($value !== null) {
            return $value;
        }

        $value = $callback();
        $this->put($key, $value, $ttlSeconds);

        return $value;
    }

    /**
     * Remove an item from the L1 cache.
     */
    public function forget(string $key): bool
    {
        if (isset(self::$storage[$key])) {
            unset(self::$storage[$key]);
            return true;
        }

        return false;
    }

    /**
     * Flush all items from the L1 in-memory store.
     */
    public function flush(): void
    {
        self::flushStatic();
    }

    /**
     * Static flush for resetting L1 memory across test cycles or between workers.
     */
    public static function flushStatic(): void
    {
        self::$storage = [];
    }

    /**
     * Return count of active in-memory entries (for telemetry/debugging).
     */
    public function count(): int
    {
        $now = microtime(true);
        $count = 0;

        foreach (self::$storage as $key => $entry) {
            if ($now <= $entry['expires_at']) {
                $count++;
            } else {
                unset(self::$storage[$key]);
            }
        }

        return $count;
    }
}
