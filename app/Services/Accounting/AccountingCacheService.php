<?php

declare(strict_types=1);

namespace App\Services\Accounting;

use Closure;
use Illuminate\Cache\TaggableStore;
use Illuminate\Support\Facades\Cache;

/**
 * Enterprise In-Memory Cache Service for Financial Engine & Executive Dashboards.
 *
 * Utilizes Redis tags for rapid retrieval and instantaneous invalidation upon ledger mutations.
 * Falls back gracefully to standard keyed cache if taggable store is unavailable.
 */
final class AccountingCacheService
{
    public const TAG_DASHBOARD = 'dashboard';
    public const TAG_REPORTS   = 'reports';
    public const TAG_GL        = 'gl';
    public const TAG_COA       = 'coa';
    public const TAG_LEDGER    = 'ledger';

    public const KEY_DASHBOARD_METRICS = 'accounting:dashboard:metrics';
    public const KEY_COA_TOTALS        = 'accounting:coa:classification_totals';
    public const KEY_LEDGER_TOTALS     = 'accounting:ledger:ytd_totals';
    public const KEY_KPI_METRICS       = 'accounting:kpi:metrics';

    /**
     * Cache executive dashboard metrics.
     */
    public function rememberDashboardMetrics(Closure $callback, int $ttlSeconds = 300): array
    {
        return $this->rememberTagged(
            tags: [self::TAG_DASHBOARD],
            key: self::KEY_DASHBOARD_METRICS,
            ttl: $ttlSeconds,
            callback: $callback
        );
    }

    /**
     * Cache Chart of Accounts classification totals.
     */
    public function rememberCoaTotals(Closure $callback, int $ttlSeconds = 300): array
    {
        return $this->rememberTagged(
            tags: [self::TAG_GL, self::TAG_COA],
            key: self::KEY_COA_TOTALS,
            ttl: $ttlSeconds,
            callback: $callback
        );
    }

    /**
     * Cache General Ledger YTD summary totals.
     */
    public function rememberLedgerTotals(Closure $callback, int $ttlSeconds = 300): array
    {
        return $this->rememberTagged(
            tags: [self::TAG_GL, self::TAG_LEDGER],
            key: self::KEY_LEDGER_TOTALS,
            ttl: $ttlSeconds,
            callback: $callback
        );
    }

    /**
     * Cache Financial KPI deck calculations.
     */
    public function rememberKpiMetrics(Closure $callback, int $ttlSeconds = 300): array
    {
        return $this->rememberTagged(
            tags: [self::TAG_REPORTS],
            key: self::KEY_KPI_METRICS,
            ttl: $ttlSeconds,
            callback: $callback
        );
    }

    /**
     * Invalidate all financial calculation caches across GL, Reports, and Dashboards.
     * Invoked immediately whenever a journal entry is posted, reversed, or period closed.
     */
    public function invalidateFinancialCaches(): void
    {
        if ($this->supportsTags()) {
            Cache::tags([
                self::TAG_DASHBOARD,
                self::TAG_REPORTS,
                self::TAG_GL,
                self::TAG_COA,
                self::TAG_LEDGER,
            ])->flush();
        } else {
            Cache::forget(self::KEY_DASHBOARD_METRICS);
            Cache::forget(self::KEY_COA_TOTALS);
            Cache::forget(self::KEY_LEDGER_TOTALS);
            Cache::forget(self::KEY_KPI_METRICS);
        }
    }

    /**
     * Invalidate dashboard metrics cache only.
     */
    public function invalidateDashboard(): void
    {
        if ($this->supportsTags()) {
            Cache::tags([self::TAG_DASHBOARD])->flush();
        } else {
            Cache::forget(self::KEY_DASHBOARD_METRICS);
        }
    }

    /**
     * Execute tagged cache remember or fallback to key-only remember.
     *
     * @param string[] $tags
     */
    private function rememberTagged(array $tags, string $key, int $ttl, Closure $callback): mixed
    {
        if ($this->supportsTags()) {
            return Cache::tags($tags)->remember($key, $ttl, $callback);
        }

        return Cache::remember($key, $ttl, $callback);
    }

    /**
     * Determine if current active cache store supports tags.
     */
    public function supportsTags(): bool
    {
        return Cache::getStore() instanceof TaggableStore;
    }
}
