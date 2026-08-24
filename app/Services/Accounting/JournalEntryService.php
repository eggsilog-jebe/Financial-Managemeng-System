<?php

declare(strict_types=1);

namespace App\Services\Accounting;

use App\DTOs\JournalEntryData;
use App\Models\JournalEntry;
use App\Models\JournalEntryLine;
use DomainException;
use Illuminate\Support\Facades\DB;

final class JournalEntryService
{
    public function createAndPostEntry(JournalEntryData $data): JournalEntry
    {
        $this->assertBalancedDoubleEntry($data->lines);

        return DB::transaction(function () use ($data): JournalEntry {
            $entry = JournalEntry::create([
                'reference_number' => $data->referenceNumber,
                'entry_date' => $data->entryDate,
                'description' => $data->description,
                'type' => $data->type,
                'status' => 'POSTED',
                'posted_by' => $data->postedBy,
                'posted_at' => now(),
            ]);

            foreach ($data->lines as $line) {
                JournalEntryLine::create([
                    'journal_entry_id' => $entry->id,
                    'account_id' => $line->accountId,
                    'debit' => $line->debit,
                    'credit' => $line->credit,
                    'memo' => $line->memo,
                ]);
            }

            return $entry->loadMissing(['lines.account']);
        });
    }

    public function reverseEntry(JournalEntry $originalEntry, int $userId, string $reason): JournalEntry
    {
        if ($originalEntry->status !== 'POSTED') {
            throw new DomainException("Only posted journal entries can be reversed.");
        }

        return DB::transaction(function () use ($originalEntry, $userId, $reason): JournalEntry {
            $reversal = JournalEntry::create([
                'reference_number' => 'REV-' . $originalEntry->reference_number,
                'entry_date' => now()->toDateString(),
                'description' => "Reversal of [{$originalEntry->reference_number}]: {$reason}",
                'type' => 'ADJUSTING',
                'status' => 'POSTED',
                'posted_by' => $userId,
                'posted_at' => now(),
            ]);

            foreach ($originalEntry->lines as $line) {
                JournalEntryLine::create([
                    'journal_entry_id' => $reversal->id,
                    'account_id' => $line->account_id,
                    'debit' => $line->credit, // Flip Debit & Credit
                    'credit' => $line->debit,
                    'memo' => "Reversal of Line #{$line->id}",
                ]);
            }

            $originalEntry->update(['status' => 'REVERSED']);

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
            $totalDebit = bcadd($totalDebit, (string) $line->debit, 4);
            $totalCredit = bcadd($totalCredit, (string) $line->credit, 4);
        }

        if (bccomp($totalDebit, $totalCredit, 4) !== 0) {
            throw new DomainException(
                "Double-Entry Unbalanced Error: Total Debits [{$totalDebit}] do not equal Total Credits [{$totalCredit}]."
            );
        }
    }
}
