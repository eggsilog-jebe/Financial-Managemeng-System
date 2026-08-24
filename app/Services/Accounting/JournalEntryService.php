<?php

declare(strict_types=1);

namespace App\Services\Accounting;

use App\DTOs\Accounting\JournalEntryData as AccountingJournalEntryData;
use App\DTOs\JournalEntryData;
use App\Exceptions\Accounting\UnbalancedJournalEntryException;
use App\Models\JournalEntry;
use App\Models\JournalEntryLine;
use DomainException;
use Illuminate\Support\Facades\DB;

final class JournalEntryService
{
    public function createAndPostEntry(JournalEntryData|AccountingJournalEntryData $data): JournalEntry
    {
        $this->assertBalancedDoubleEntry($data->lines);

        return DB::transaction(function () use ($data): JournalEntry {
            $refNumber = method_exists($data, 'getReferenceNumber') 
                ? $data->getReferenceNumber() 
                : ($data->referenceNumber ?? ('JE-' . date('Ymd') . '-' . strtoupper(bin2hex(random_bytes(3)))));

            $postedBy = property_exists($data, 'postedBy') 
                ? $data->postedBy 
                : (property_exists($data, 'userId') ? $data->userId : auth()->id());

            $entry = JournalEntry::create([
                'reference_number' => $refNumber,
                'entry_date'       => $data->entryDate,
                'description'      => $data->description,
                'type'             => $data->type ?? 'GENERAL',
                'status'           => 'POSTED',
                'posted_by'        => $postedBy,
                'posted_at'        => now(),
            ]);

            foreach ($data->lines as $line) {
                $accountId = is_array($line) ? $line['account_id'] : $line->accountId;
                $debit = is_array($line) ? (string) $line['debit'] : (string) $line->debit;
                $credit = is_array($line) ? (string) $line['credit'] : (string) $line->credit;
                $memo = is_array($line) ? ($line['memo'] ?? null) : $line->memo;

                JournalEntryLine::create([
                    'journal_entry_id' => $entry->id,
                    'account_id'       => $accountId,
                    'debit'            => $debit,
                    'credit'           => $credit,
                    'memo'             => $memo,
                ]);
            }

            return $entry->loadMissing(['lines.account']);
        });
    }

    public function reverseEntry(JournalEntry $originalEntry, string|int $reasonOrUserId = 1, string|int $reasonOrUserIdSecond = 'Adjustment'): JournalEntry
    {
        $reason = is_string($reasonOrUserId) ? $reasonOrUserId : (is_string($reasonOrUserIdSecond) ? $reasonOrUserIdSecond : 'Manual Reversal');
        $userId = is_int($reasonOrUserId) ? $reasonOrUserId : (is_int($reasonOrUserIdSecond) ? $reasonOrUserIdSecond : 1);

        if ($originalEntry->status !== 'POSTED') {
            throw new DomainException("Only posted journal entries can be reversed.");
        }

        return DB::transaction(function () use ($originalEntry, $userId, $reason): JournalEntry {
            $reversal = JournalEntry::create([
                'reference_number' => 'REV-' . $originalEntry->reference_number,
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
                    'debit'            => $line->credit, // Flip Debit & Credit
                    'credit'           => $line->debit,
                    'memo'             => "Reversal of Line #{$line->id}",
                ]);
            }

            $originalEntry->update([
                'status'               => 'REVERSED',
                'reversed_by_entry_id' => $reversal->id,
            ]);

            return $reversal->loadMissing(['lines.account']);
        });
    }

    /**
     * Enforce Invariance Rule: sum(debit) === sum(credit) using BCMath
     */
    private function assertBalancedDoubleEntry(array $lines): void
    {
        $totalDebit = '0.0000';
        $totalCredit = '0.0000';

        foreach ($lines as $line) {
            $debit = is_array($line) ? (string) $line['debit'] : (string) $line->debit;
            $credit = is_array($line) ? (string) $line['credit'] : (string) $line->credit;

            $totalDebit = bcadd($totalDebit, $debit, 4);
            $totalCredit = bcadd($totalCredit, $credit, 4);
        }

        if (bccomp($totalDebit, $totalCredit, 4) !== 0) {
            throw new UnbalancedJournalEntryException(
                "Double-Entry Unbalanced Error: Total Debits [{$totalDebit}] do not equal Total Credits [{$totalCredit}]."
            );
        }
    }
}
