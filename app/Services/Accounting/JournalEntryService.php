<?php

declare(strict_types=1);

namespace App\Services\Accounting;

use App\DTOs\Accounting\JournalEntryData as AccountingJournalEntryData;
use App\DTOs\JournalEntryData;
use App\Exceptions\Accounting\UnbalancedJournalEntryException;
use App\Models\FiscalPeriod;
use App\Models\JournalEntry;
use App\Models\JournalEntryLine;
use DomainException;
use Illuminate\Support\Facades\DB;

final class JournalEntryService
{
    public function __construct(
        private readonly CasAuditTrailService $auditTrailService,
        private readonly PeriodClosingService $periodClosingService,
    ) {}

    /**
     * Create and post a journal entry immediately.
     */
    public function createAndPostEntry(JournalEntryData|AccountingJournalEntryData $data): JournalEntry
    {
        return $this->createEntry($data, autoPost: true);
    }

    /**
     * Create a journal entry (Draft or Posted).
     */
    public function createEntry(JournalEntryData|AccountingJournalEntryData $data, bool $autoPost = true): JournalEntry
    {
        // 1. Assert Double-Entry Balance using BCMath
        $this->assertBalancedDoubleEntry($data->lines);

        // 2. Guard: Check that entry_date does not fall into a LOCKED or CLOSED period
        $this->periodClosingService->assertPeriodIsOpen($data->entryDate);

        return DB::transaction(function () use ($data, $autoPost): JournalEntry {
            $refNumber = method_exists($data, 'getReferenceNumber')
                ? $data->getReferenceNumber()
                : ($data->referenceNumber ?? ('JE-' . date('Ymd') . '-' . strtoupper(bin2hex(random_bytes(3)))));

            $postedBy = property_exists($data, 'postedBy')
                ? $data->postedBy
                : (property_exists($data, 'userId') ? $data->userId : (auth()->id() ?? 1));

            $status = $autoPost ? 'POSTED' : 'DRAFT';
            $postedAt = $autoPost ? now() : null;

            $entry = JournalEntry::create([
                'reference_number' => $refNumber,
                'entry_date'       => $data->entryDate,
                'description'      => $data->description,
                'type'             => $data->type ?? 'GENERAL',
                'status'           => $status,
                'posted_by'        => $postedBy,
                'posted_at'        => $postedAt,
            ]);

            foreach ($data->lines as $line) {
                $accountId = is_array($line) ? (int) $line['account_id'] : (int) $line->accountId;
                $debit = is_array($line) ? (string) ($line['debit'] ?? '0.0000') : (string) ($line->debit ?? '0.0000');
                $credit = is_array($line) ? (string) ($line['credit'] ?? '0.0000') : (string) ($line->credit ?? '0.0000');
                $memo = is_array($line) ? ($line['memo'] ?? null) : ($line->memo ?? null);

                JournalEntryLine::create([
                    'journal_entry_id' => $entry->id,
                    'account_id'       => $accountId,
                    'debit'            => $debit,
                    'credit'           => $credit,
                    'memo'             => $memo,
                ]);
            }

            // Log event in BIR CAS audit trail
            $this->auditTrailService->logFinancialEvent(
                auditable: $entry,
                action: $autoPost ? 'POST' : 'INSERT',
                oldValues: null,
                newValues: $entry->loadMissing('lines')->toArray(),
                userId: $postedBy,
                userName: auth()->user()?->name ?? 'System User',
                ipAddress: request()?->ip() ?? '127.0.0.1',
            );

            return $entry->loadMissing(['lines.account', 'creator']);
        });
    }

    /**
     * Post an existing DRAFT Journal Entry to the General Ledger.
     */
    public function postEntry(JournalEntry $entry, int $userId): JournalEntry
    {
        if ($entry->status === 'POSTED') {
            throw new DomainException("Journal Entry [{$entry->reference_number}] is already POSTED.");
        }

        if ($entry->status === 'REVERSED') {
            throw new DomainException("Reversed Journal Entry [{$entry->reference_number}] cannot be posted.");
        }

        $this->periodClosingService->assertPeriodIsOpen($entry->entry_date->format('Y-m-d'));

        // Assert balance of lines
        $this->assertBalancedDoubleEntry($entry->lines->toArray());

        return DB::transaction(function () use ($entry, $userId): JournalEntry {
            $oldValues = $entry->toArray();

            $entry->update([
                'status'    => 'POSTED',
                'posted_by' => $userId,
                'posted_at' => now(),
            ]);

            $this->auditTrailService->logFinancialEvent(
                auditable: $entry,
                action: 'POST',
                oldValues: $oldValues,
                newValues: $entry->toArray(),
                userId: $userId,
                userName: auth()->user()?->name ?? 'Finance Approver',
                ipAddress: request()?->ip() ?? '127.0.0.1',
            );

            return $entry->loadMissing(['lines.account', 'creator']);
        });
    }

    /**
     * Reverse a POSTED Journal Entry by creating a mirroring entry with swapped Debits and Credits.
     */
    public function reverseEntry(JournalEntry $originalEntry, string|int $reasonOrUserId = 1, string|int $reasonOrUserIdSecond = 'Adjustment'): JournalEntry
    {
        $reason = is_string($reasonOrUserId) ? $reasonOrUserId : (is_string($reasonOrUserIdSecond) ? $reasonOrUserIdSecond : 'Manual Reversal');
        $userId = is_int($reasonOrUserId) ? $reasonOrUserId : (is_int($reasonOrUserIdSecond) ? $reasonOrUserIdSecond : (auth()->id() ?? 1));

        if ($originalEntry->status !== 'POSTED') {
            throw new DomainException("Only posted journal entries can be reversed.");
        }

        return DB::transaction(function () use ($originalEntry, $userId, $reason): JournalEntry {
            $originalEntry->loadMissing('lines');

            $reversal = JournalEntry::create([
                'reference_number' => 'REV-' . $originalEntry->reference_number . '-' . strtoupper(bin2hex(random_bytes(2))),
                'entry_date'       => now()->toDateString(),
                'description'      => "Reversal of [{$originalEntry->reference_number}]: {$reason}",
                'type'             => 'ADJUSTING',
                'status'           => 'POSTED',
                'posted_by'        => $userId,
                'posted_at'        => now(),
            ]);

            foreach ($originalEntry->lines as $line) {
                JournalEntryLine::create([
                    'journal_entry_id' => $reversal->id,
                    'account_id'       => $line->account_id,
                    'debit'            => $line->credit, // Swap Debit & Credit
                    'credit'           => $line->debit,
                    'memo'             => "Reversal of Line #{$line->id} ({$originalEntry->reference_number})",
                ]);
            }

            $oldOriginalValues = $originalEntry->toArray();

            $originalEntry->update([
                'status'               => 'REVERSED',
                'reversed_by_entry_id' => $reversal->id,
            ]);

            // Audit log for reversal
            $this->auditTrailService->logFinancialEvent(
                auditable: $originalEntry,
                action: 'REVERSE',
                oldValues: $oldOriginalValues,
                newValues: $originalEntry->toArray(),
                userId: $userId,
                userName: auth()->user()?->name ?? 'Finance Approver',
                ipAddress: request()?->ip() ?? '127.0.0.1',
            );

            $this->auditTrailService->logFinancialEvent(
                auditable: $reversal,
                action: 'POST',
                oldValues: null,
                newValues: $reversal->loadMissing('lines')->toArray(),
                userId: $userId,
                userName: auth()->user()?->name ?? 'Finance Approver',
                ipAddress: request()?->ip() ?? '127.0.0.1',
            );

            return $reversal->loadMissing(['lines.account', 'creator']);
        });
    }

    /**
     * Enforce Invariance Rule: sum(debit) === sum(credit) using BCMath
     */
    public function assertBalancedDoubleEntry(array $lines): void
    {
        if (count($lines) < 2) {
            throw new UnbalancedJournalEntryException("Double-Entry Error: A valid journal entry requires at least two account lines.");
        }

        $totalDebit = '0.0000';
        $totalCredit = '0.0000';

        foreach ($lines as $line) {
            $debit = is_array($line) ? (string) ($line['debit'] ?? '0.0000') : (string) ($line->debit ?? '0.0000');
            $credit = is_array($line) ? (string) ($line['credit'] ?? '0.0000') : (string) ($line->credit ?? '0.0000');

            $totalDebit = bcadd($totalDebit, $debit, 4);
            $totalCredit = bcadd($totalCredit, $credit, 4);
        }

        if (bccomp($totalDebit, $totalCredit, 4) !== 0) {
            throw new UnbalancedJournalEntryException(
                "Double-Entry Unbalanced Error: Total Debits [{$totalDebit}] do not equal Total Credits [{$totalCredit}]."
            );
        }

        if (bccomp($totalDebit, '0.0000', 4) === 0) {
            throw new UnbalancedJournalEntryException("Double-Entry Error: Transaction total amount cannot be zero.");
        }
    }
}
