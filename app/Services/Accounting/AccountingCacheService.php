<?php

declare(strict_types=1);

namespace App\Services\Accounting;

use App\Services\Cache\MultiLayerCacheService;
use Closure;

/**
 * Enterprise In-Memory & Distributed Cache Service for Financial Engine & Executive Dashboards.
 *
 * Built on MultiLayerCacheService:
 * - Layer 1: Sub-microsecond In-Memory local process cache.
 * - Layer 2: Distributed Redis/store cache-aside.
 * - Mutex single-flight locking to prevent database flooding during concurrent lookups.
 * - Expiration jitter to eliminate thundering herd cache stampedes.
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

    public function __construct(
        private readonly MultiLayerCacheService $multiLayerCache,
    ) {}

    /**
     * Cache executive dashboard metrics using 3-layer caching and single-flight lock.
     */
    public function rememberDashboardMetrics(Closure $callback, int $ttlSeconds = 300): array
    {
        return $this->multiLayerCache->remember(
            key: self::KEY_DASHBOARD_METRICS,
            baseTtl: $ttlSeconds,
            callback: $callback,
            tags: [self::TAG_DASHBOARD],
            l1Ttl: 60,
        );
    }

    /**
     * Cache Chart of Accounts classification totals.
     */
    public function rememberCoaTotals(Closure $callback, int $ttlSeconds = 300): array
    {
        return $this->multiLayerCache->remember(
            key: self::KEY_COA_TOTALS,
            baseTtl: $ttlSeconds,
            callback: $callback,
            tags: [self::TAG_GL, self::TAG_COA],
            l1Ttl: 60,
        );
    }

    /**
     * Cache General Ledger YTD summary totals.
     */
    public function rememberLedgerTotals(Closure $callback, int $ttlSeconds = 300): array
    {
        return $this->multiLayerCache->remember(
            key: self::KEY_LEDGER_TOTALS,
            baseTtl: $ttlSeconds,
            callback: $callback,
            tags: [self::TAG_GL, self::TAG_LEDGER],
            l1Ttl: 60,
        );
    }

    /**
     * Cache Financial KPI deck calculations.
     */
    public function rememberKpiMetrics(Closure $callback, int $ttlSeconds = 300): array
    {
        return $this->multiLayerCache->remember(
            key: self::KEY_KPI_METRICS,
            baseTtl: $ttlSeconds,
            callback: $callback,
            tags: [self::TAG_REPORTS],
            l1Ttl: 60,
        );
    }

    /**
     * Invalidate all financial calculation caches across GL, Reports, and Dashboards.
     * Invoked immediately whenever a journal entry is posted, reversed, or period closed.
     */
    public function invalidateFinancialCaches(): void
    {
        $this->multiLayerCache->invalidateTags([
            self::TAG_DASHBOARD,
            self::TAG_REPORTS,
            self::TAG_GL,
            self::TAG_COA,
            self::TAG_LEDGER,
        ]);

        $this->multiLayerCache->forget(self::KEY_DASHBOARD_METRICS);
        $this->multiLayerCache->forget(self::KEY_COA_TOTALS);
        $this->multiLayerCache->forget(self::KEY_LEDGER_TOTALS);
        $this->multiLayerCache->forget(self::KEY_KPI_METRICS);
    }

    /**
     * Invalidate dashboard metrics cache only.
     */
    public function invalidateDashboard(): void
    {
        $this->multiLayerCache->invalidateTags([self::TAG_DASHBOARD]);
        $this->multiLayerCache->forget(self::KEY_DASHBOARD_METRICS);
    }

    /**
     * Expose direct access to underlying multi-layer cache engine.
     */
    public function multiLayer(): MultiLayerCacheService
    {
        return $this->multiLayerCache;
    }
}
