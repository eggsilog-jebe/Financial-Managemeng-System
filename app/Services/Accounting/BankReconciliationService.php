<?php

declare(strict_types=1);

namespace App\Services\Accounting;

use App\Models\BankAccount;
use App\Models\BankReconciliation;
use App\Models\BankStatementLine;
use App\Models\JournalEntryLine;
use DomainException;
use Illuminate\Support\Facades\DB;

final class BankReconciliationService
{
    /**
     * Reconcile electronic bank statement transactions with internal General Ledger cash books.
     */
    public function performReconciliation(
        int $bankAccountId,
        string $statementDate,
        string $statementBalance,
        array $statementLines
    ): BankReconciliation {
        return DB::transaction(function () use ($bankAccountId, $statementDate, $statementBalance, $statementLines): BankReconciliation {
            $bank = BankAccount::findOrFail($bankAccountId);
            $bookBalance = (string) $bank->balance;

            $reconciliation = BankReconciliation::create([
                'bank_account_id'   => $bank->id,
                'statement_date'    => $statementDate,
                'statement_balance' => $statementBalance,
                'book_balance'      => $bookBalance,
                'variance'          => '0.0000',
                'status'            => 'Reconciled',
            ]);

            $totalMatched = '0.0000';

            foreach ($statementLines as $line) {
                $matchedLineId = null;
                $matchStatus = 'UNMATCHED';

                // Look for matching journal entry line or reference
                if (isset($line['reference_number'])) {
                    $journalLine = JournalEntryLine::whereHas('journalEntry', function ($q) use ($line) {
                        $q->where('reference_number', $line['reference_number']);
                    })->first();

                    if ($journalLine) {
                        $matchedLineId = $journalLine->id;
                        $matchStatus = 'MATCHED';
                        $totalMatched = bcadd($totalMatched, (string) $line['amount'], 4);
                    }
                }

                BankStatementLine::create([
                    'bank_reconciliation_id'  => $reconciliation->id,
                    'transaction_date'        => $line['date'] ?? $statementDate,
                    'reference_number'        => $line['reference_number'] ?? null,
                    'description'             => $line['description'] ?? 'Bank Transaction',
                    'amount'                  => (string) $line['amount'],
                    'match_status'            => $matchStatus,
                    'matched_journal_line_id' => $matchedLineId,
                ]);
            }

            $computedVariance = bcsub($statementBalance, $bookBalance, 4);
            $reconciliation->update([
                'variance' => $computedVariance,
                'status'   => bccomp($computedVariance, '0.0000', 4) === 0 ? 'Reconciled' : 'Discrepancy',
            ]);

            return $reconciliation->loadMissing(['lines', 'bankAccount']);
        });
    }
}
