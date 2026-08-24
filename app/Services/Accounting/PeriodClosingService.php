<?php

declare(strict_types=1);

namespace App\Services\Accounting;

use App\Models\FiscalPeriod;
use DomainException;
use Illuminate\Support\Facades\DB;

final class PeriodClosingService
{
    /**
     * Lock a fiscal period, preventing any past transaction insertions or mutations.
     */
    public function closeAndLockPeriod(string $periodCode, int $userId): FiscalPeriod
    {
        return DB::transaction(function () use ($periodCode, $userId): FiscalPeriod {
            $period = FiscalPeriod::where('period_code', $periodCode)->firstOrFail();

            if ($period->status === 'LOCKED') {
                throw new DomainException("Fiscal Period [{$periodCode}] is already LOCKED.");
            }

            $period->update([
                'status'    => 'LOCKED',
                'closed_by' => $userId,
                'closed_at' => now(),
            ]);

            return $period->loadMissing('closedByUser');
        });
    }

    /**
     * Assert that a transaction date falls into an OPEN fiscal period.
     */
    public function assertPeriodIsOpen(string $transactionDate): void
    {
        $lockedPeriod = FiscalPeriod::where('start_date', '<=', $transactionDate)
            ->where('end_date', '>=', $transactionDate)
            ->where('status', 'LOCKED')
            ->first();

        if ($lockedPeriod) {
            throw new DomainException(
                "Accounting Period Lock Error: Fiscal Period [{$lockedPeriod->period_code}] is LOCKED. No further transactions can be posted for date [{$transactionDate}]."
            );
        }
    }
}
