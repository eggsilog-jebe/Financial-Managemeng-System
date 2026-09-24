<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\Accounting\AccountingCacheService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;

final class RedisCacheStatusCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'redis:status {--benchmark : Run read/write latency micro-benchmark}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check Redis server connectivity, memory diagnostics, and cache health';

    public function handle(AccountingCacheService $cacheService): int
    {
        $this->info('====================================================');
        $this->info('  FMS REDIS CACHE ENGINE DIAGNOSTICS & STATUS       ');
        $this->info('====================================================');

        // 1. Connection check
        try {
            $startTime = microtime(true);
            $client = Redis::connection('default');
            $pong = $client->ping();
            $pingLatencyMs = round((microtime(true) - $startTime) * 1000, 2);

            $this->line("✅ Redis Status: <fg=green;options=bold>ONLINE</> (Ping: {$pingLatencyMs}ms)");
        } catch (\Throwable $e) {
            $this->error("❌ Redis Connection Failed: " . $e->getMessage());
            $this->warn("Make sure the Redis server is running on 127.0.0.1:6379.");
            return self::FAILURE;
        }

        // 2. Info diagnostics
        try {
            $info = $client->info();
            $redisVersion = $info['redis_version'] ?? 'Unknown';
            $usedMemoryHuman = $info['used_memory_human'] ?? 'Unknown';
            $connectedClients = $info['connected_clients'] ?? '0';
            $uptimeDays = $info['uptime_in_days'] ?? '0';

            $this->table(
                ['Diagnostic Metric', 'Current Value'],
                [
                    ['Redis Version', $redisVersion],
                    ['Server Uptime', "{$uptimeDays} days"],
                    ['Connected Clients', $connectedClients],
                    ['Memory Allocated', $usedMemoryHuman],
                    ['Default Cache Store', config('cache.default')],
                    ['Redis Host:Port', config('database.redis.default.host') . ':' . config('database.redis.default.port')],
                    ['Tagging Supported', $cacheService->supportsTags() ? 'Yes (Native Redis Tags)' : 'No'],
                ]
            );
        } catch (\Throwable $e) {
            $this->warn("Could not retrieve detailed server info: " . $e->getMessage());
        }

        // 3. Optional Benchmark
        if ($this->option('benchmark')) {
            $this->info("\nRunning 100-operation read/write micro-benchmark...");
            $samples = 100;
            $start = microtime(true);

            for ($i = 0; $i < $samples; $i++) {
                Cache::put("fms:benchmark:key_{$i}", "payload_{$i}", 60);
                Cache::get("fms:benchmark:key_{$i}");
            }

            $totalElapsedMs = (microtime(true) - $start) * 1000;
            $avgOpTimeMs = round($totalElapsedMs / ($samples * 2), 3);

            for ($i = 0; $i < $samples; $i++) {
                Cache::forget("fms:benchmark:key_{$i}");
            }

            $this->line("⚡ Completed {$samples} writes & {$samples} reads in " . round($totalElapsedMs, 2) . "ms");
            $this->line("⚡ Average latency per operation: <fg=cyan;options=bold>{$avgOpTimeMs} ms</>");
        }

        $this->info("\nRedis caching is active and accelerating your application!");
        return self::SUCCESS;
    }
}
