<?php

declare(strict_types=1);

namespace App\Services\Accounting;

use App\Models\Account;
use App\Models\FiscalPeriod;
use App\Models\JournalEntry;
use App\Models\JournalEntryLine;
use Carbon\Carbon;
use DomainException;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

final class PeriodClosingService
{
    public function __construct(
        private readonly CasAuditTrailService $auditTrailService,
    ) {}

    /**
     * Initialize 12 monthly periods for a fiscal year.
     *
     * @return Collection<int, FiscalPeriod>
     */
    public function initializeFiscalYear(string $fiscalYear, ?int $userId = null): Collection
    {
        return DB::transaction(function () use ($fiscalYear, $userId): Collection {
            $createdPeriods = [];

            for ($m = 1; $m <= 12; $m++) {
                $monthStr = str_pad((string) $m, 2, '0', STR_PAD_LEFT);
                $periodCode = "{$fiscalYear}-M{$monthStr}";

                $startDate = Carbon::createFromDate((int) $fiscalYear, $m, 1)->startOfMonth()->toDateString();
                $endDate = Carbon::createFromDate((int) $fiscalYear, $m, 1)->endOfMonth()->toDateString();

                $period = FiscalPeriod::firstOrCreate(
                    ['period_code' => $periodCode],
                    [
                        'fiscal_year'   => $fiscalYear,
                        'period_number' => $m,
                        'start_date'    => $startDate,
                        'end_date'      => $endDate,
                        'status'        => 'OPEN',
                    ]
                );

                $createdPeriods[] = $period;
            }

            $firstPeriod = $createdPeriods[0];
            $this->auditTrailService->logFinancialEvent(
                auditable: $firstPeriod,
                action: 'INSERT',
                oldValues: null,
                newValues: ['fiscal_year' => $fiscalYear, 'periods_created' => 12],
                userId: $userId ?? auth()->id() ?? 1,
                userName: auth()->user()?->name ?? 'System Administrator',
                ipAddress: request()?->ip() ?? '127.0.0.1',
            );

            return FiscalPeriod::where('fiscal_year', $fiscalYear)->orderBy('period_number')->get();
        });
    }

    /**
     * Lock a fiscal period, preventing regular staff from modifying transactions.
     */
    public function lockPeriod(int|string $periodIdOrCode, int $userId): FiscalPeriod
    {
        return DB::transaction(function () use ($periodIdOrCode, $userId): FiscalPeriod {
            $period = is_numeric($periodIdOrCode)
                ? FiscalPeriod::findOrFail((int) $periodIdOrCode)
                : FiscalPeriod::where('period_code', $periodIdOrCode)->firstOrFail();

            if ($period->isLocked()) {
                throw new DomainException("Fiscal Period [{$period->period_code}] is already LOCKED.");
            }

            if ($period->isClosed()) {
                throw new DomainException("Fiscal Period [{$period->period_code}] is already CLOSED/AUDITED and cannot be modified.");
            }

            $oldValues = $period->toArray();

            $period->update([
                'status'    => 'LOCKED',
                'closed_by' => $userId,
                'closed_at' => now(),
            ]);

            $this->auditTrailService->logFinancialEvent(
                auditable: $period,
                action: 'LOCK',
                oldValues: $oldValues,
                newValues: $period->toArray(),
                userId: $userId,
                userName: auth()->user()?->name ?? 'Finance Manager',
                ipAddress: request()?->ip() ?? '127.0.0.1',
            );

            return $period->loadMissing(['closedByUser', 'closingJournalEntry']);
        });
    }

    /**
     * Unlock a LOCKED fiscal period (CFO override).
     */
    public function unlockPeriod(int|string $periodIdOrCode, int $userId): FiscalPeriod
    {
        return DB::transaction(function () use ($periodIdOrCode, $userId): FiscalPeriod {
            $period = is_numeric($periodIdOrCode)
                ? FiscalPeriod::findOrFail((int) $periodIdOrCode)
                : FiscalPeriod::where('period_code', $periodIdOrCode)->firstOrFail();

            if ($period->isClosed()) {
                throw new DomainException("Fiscal Period [{$period->period_code}] is CLOSED and cannot be unlocked.");
            }

            $oldValues = $period->toArray();

            $period->update([
                'status'    => 'OPEN',
                'closed_by' => null,
                'closed_at' => null,
            ]);

            $this->auditTrailService->logFinancialEvent(
                auditable: $period,
                action: 'UPDATE',
                oldValues: $oldValues,
                newValues: $period->toArray(),
                userId: $userId,
                userName: auth()->user()?->name ?? 'CFO',
                ipAddress: request()?->ip() ?? '127.0.0.1',
            );

            return $period->loadMissing(['closedByUser', 'closingJournalEntry']);
        });
    }

    /**
     * Execute Hard Close & Rollover:
     * Generates closing journal entry zeroing out REVENUE and EXPENSE accounts into 3020 Retained Earnings,
     * stamps closed_at, sets status = 'AUDITED' / 'CLOSED', and links closing_journal_entry_id.
     */
    public function closePeriodAndRollover(int|string $periodIdOrCode, int $userId): array
    {
        return DB::transaction(function () use ($periodIdOrCode, $userId): array {
            $period = is_numeric($periodIdOrCode)
                ? FiscalPeriod::findOrFail((int) $periodIdOrCode)
                : FiscalPeriod::where('period_code', $periodIdOrCode)->firstOrFail();

            if ($period->isClosed()) {
                throw new DomainException("Fiscal Period [{$period->period_code}] is already hard-closed.");
            }

            // Find or create Retained Earnings account (Code 3020 or 3000)
            $retainedEarningsAccount = Account::where('code', '3020')->first()
                ?? Account::where('category', 'EQUITY')->where('name', 'LIKE', '%Retained Earnings%')->first()
                ?? Account::where('category', 'EQUITY')->first();

            if (! $retainedEarningsAccount) {
                $retainedEarningsAccount = Account::create([
                    'code'           => '3020',
                    'name'           => 'Retained Earnings',
                    'category'       => 'EQUITY',
                    'normal_balance' => 'CREDIT',
                    'is_active'      => true,
                ]);
            }

            // Fetch all revenue and expense accounts with activity in this period
            $nominalAccounts = Account::whereIn('category', ['REVENUE', 'EXPENSE'])
                ->with(['journalEntryLines' => function ($q) use ($period): void {
                    $q->whereHas('journalEntry', function ($je) use ($period): void {
                        $je->where('status', 'POSTED')
                           ->whereBetween('entry_date', [$period->start_date->format('Y-m-d'), $period->end_date->format('Y-m-d')]);
                    });
                }])
                ->get();

            $closingLines = [];
            $totalRevenueToZero = '0.0000';
            $totalExpenseToZero = '0.0000';

            foreach ($nominalAccounts as $acc) {
                $debits = (string) $acc->journalEntryLines->sum('debit');
                $credits = (string) $acc->journalEntryLines->sum('credit');

                if ($acc->category === 'REVENUE') {
                    // Net Revenue Credit Balance = Credits - Debits
                    $netCredit = bcsub($credits, $debits, 4);
                    if (bccomp($netCredit, '0.0000', 4) > 0) {
                        // To zero out Revenue: Debit the revenue account
                        $closingLines[] = [
                            'account_id' => $acc->id,
                            'debit'      => $netCredit,
                            'credit'     => '0.0000',
                            'memo'       => "Close {$acc->code} - {$acc->name} for period {$period->period_code}",
                        ];
                        $totalRevenueToZero = bcadd($totalRevenueToZero, $netCredit, 4);
                    }
                } elseif ($acc->category === 'EXPENSE') {
                    // Net Expense Debit Balance = Debits - Credits
                    $netDebit = bcsub($debits, $credits, 4);
                    if (bccomp($netDebit, '0.0000', 4) > 0) {
                        // To zero out Expense: Credit the expense account
                        $closingLines[] = [
                            'account_id' => $acc->id,
                            'debit'      => '0.0000',
                            'credit'     => $netDebit,
                            'memo'       => "Close {$acc->code} - {$acc->name} for period {$period->period_code}",
                        ];
                        $totalExpenseToZero = bcadd($totalExpenseToZero, $netDebit, 4);
                    }
                }
            }

            // Net Income / (Loss) = Total Revenue - Total Expense
            $netIncome = bcsub($totalRevenueToZero, $totalExpenseToZero, 4);
            $closingJournalEntry = null;

            if (! empty($closingLines)) {
                // Balance into Retained Earnings
                if (bccomp($netIncome, '0.0000', 4) > 0) {
                    // Net profit: Credit Retained Earnings
                    $closingLines[] = [
                        'account_id' => $retainedEarningsAccount->id,
                        'debit'      => '0.0000',
                        'credit'     => $netIncome,
                        'memo'       => "Net Income rollover for period {$period->period_code}",
                    ];
                } elseif (bccomp($netIncome, '0.0000', 4) < 0) {
                    // Net loss: Debit Retained Earnings
                    $closingLines[] = [
                        'account_id' => $retainedEarningsAccount->id,
                        'debit'      => bcmul($netIncome, '-1.0000', 4),
                        'credit'     => '0.0000',
                        'memo'       => "Net Loss rollover for period {$period->period_code}",
                    ];
                }

                // Create Closing Journal Entry
                $closingJournalEntry = JournalEntry::create([
                    'reference_number' => "CLOSE-{$period->period_code}-" . strtoupper(bin2hex(random_bytes(2))),
                    'entry_date'       => $period->end_date->format('Y-m-d'),
                    'description'      => "Closing Entry for Fiscal Period [{$period->period_code}]",
                    'type'             => 'CLOSING',
                    'status'           => 'POSTED',
                    'posted_by'        => $userId,
                    'posted_at'        => now(),
                ]);

                foreach ($closingLines as $line) {
                    JournalEntryLine::create([
                        'journal_entry_id' => $closingJournalEntry->id,
                        'account_id'       => $line['account_id'],
                        'debit'            => $line['debit'],
                        'credit'           => $line['credit'],
                        'memo'             => $line['memo'],
                    ]);
                }
            }

            $oldPeriodValues = $period->toArray();

            // Set period status to AUDITED / CLOSED
            $period->update([
                'status'                   => 'AUDITED',
                'closed_by'                => $userId,
                'closed_at'                => now(),
                'closing_journal_entry_id' => $closingJournalEntry?->id,
            ]);

            $this->auditTrailService->logFinancialEvent(
                auditable: $period,
                action: 'LOCK',
                oldValues: $oldPeriodValues,
                newValues: $period->toArray(),
                userId: $userId,
                userName: auth()->user()?->name ?? 'CFO',
                ipAddress: request()?->ip() ?? '127.0.0.1',
            );

            return [
                'period'                => $period->loadMissing(['closedByUser', 'closingJournalEntry']),
                'closing_journal_entry' => $closingJournalEntry,
                'net_income'            => $netIncome,
                'total_revenue_closed'  => $totalRevenueToZero,
                'total_expense_closed'  => $totalExpenseToZero,
            ];
        });
    }

    /**
     * Assert that a transaction date falls into an OPEN fiscal period.
     */
    public function assertPeriodIsOpen(string $transactionDate): void
    {
        $lockedPeriod = FiscalPeriod::where('start_date', '<=', $transactionDate)
            ->where('end_date', '>=', $transactionDate)
            ->whereIn('status', ['LOCKED', 'AUDITED', 'CLOSED'])
            ->first();

        if ($lockedPeriod) {
            throw new DomainException(
                "Accounting Period Lock Error: Fiscal Period [{$lockedPeriod->period_code}] is {$lockedPeriod->status}. No further transactions can be posted for date [{$transactionDate}]."
            );
        }
    }
}
