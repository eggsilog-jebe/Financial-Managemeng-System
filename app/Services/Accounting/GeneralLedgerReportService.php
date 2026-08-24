<?php

declare(strict_types=1);

namespace App\Services\Accounting;

use App\Models\JournalEntryLine;
use Illuminate\Support\LazyCollection;

final class GeneralLedgerReportService
{
    /**
     * Process high-volume ledger records using cursor chunking to prevent memory bloat
     */
    public function streamLedgerLinesForPeriod(string $startDate, string $endDate): LazyCollection
    {
        return JournalEntryLine::query()
            ->with(['account', 'journalEntry'])
            ->whereHas('journalEntry', function ($query) use ($startDate, $endDate): void {
                $query->whereBetween('entry_date', [$startDate, $endDate])
                      ->where('status', 'POSTED');
            })
            ->lazy(1000);
    }
}
