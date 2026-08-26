<?php

declare(strict_types=1);

namespace App\Services\Accounting;

use App\Models\Account;
use App\Models\JournalEntryLine;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class LedgerBookService
{
    /**
     * Compute comprehensive account statement and chronological running balances.
     */
    public function getAccountLedgerStatement(
        int $accountId,
        ?string $startDate = null,
        ?string $endDate = null,
        ?string $fiscalYear = null,
    ): array {
        $account = Account::findOrFail($accountId);
        $isDebitNormal = strtoupper((string) $account->normal_balance) === 'DEBIT';

        // 1. Calculate Beginning Balance (all posted transactions before $startDate)
        $beginningBalance = '0.0000';
        $begDebit = '0.0000';
        $begCredit = '0.0000';

        if ($startDate) {
            $priorLines = JournalEntryLine::with('journalEntry')
                ->where('account_id', $accountId)
                ->whereHas('journalEntry', function ($q) use ($startDate): void {
                    $q->where('status', 'POSTED')
                      ->whereDate('entry_date', '<', $startDate);
                })
                ->get();

            foreach ($priorLines as $line) {
                $begDebit = bcadd($begDebit, (string) $line->debit, 4);
                $begCredit = bcadd($begCredit, (string) $line->credit, 4);
            }

            $beginningBalance = $isDebitNormal
                ? bcsub($begDebit, $begCredit, 4)
                : bcsub($begCredit, $begDebit, 4);
        }

        // 2. Fetch Period Transactions (posted entries between $startDate and $endDate)
        $linesQuery = JournalEntryLine::with(['journalEntry.creator', 'account'])
            ->where('account_id', $accountId)
            ->whereHas('journalEntry', function ($q) use ($startDate, $endDate, $fiscalYear): void {
                $q->where('status', 'POSTED');

                if ($startDate) {
                    $q->whereDate('entry_date', '>=', $startDate);
                }

                if ($endDate) {
                    $q->whereDate('entry_date', '<=', $endDate);
                }

                if ($fiscalYear) {
                    $q->whereYear('entry_date', (int) $fiscalYear);
                }
            })
            ->join('journal_entries', 'journal_entry_lines.journal_entry_id', '=', 'journal_entries.id')
            ->orderBy('journal_entries.entry_date')
            ->orderBy('journal_entries.id')
            ->orderBy('journal_entry_lines.id')
            ->select('journal_entry_lines.*');

        $periodLines = $linesQuery->get();

        // 3. Compute Chronological Running Balance
        $runningBalance = $beginningBalance;
        $periodDebitTotal = '0.0000';
        $periodCreditTotal = '0.0000';

        $statementRows = [];

        foreach ($periodLines as $line) {
            $debit = (string) $line->debit;
            $credit = (string) $line->credit;

            $periodDebitTotal = bcadd($periodDebitTotal, $debit, 4);
            $periodCreditTotal = bcadd($periodCreditTotal, $credit, 4);

            if ($isDebitNormal) {
                // For DEBIT normal: Running Balance = Previous + Debit - Credit
                $runningBalance = bcadd($runningBalance, bcsub($debit, $credit, 4), 4);
            } else {
                // For CREDIT normal: Running Balance = Previous + Credit - Debit
                $runningBalance = bcadd($runningBalance, bcsub($credit, $debit, 4), 4);
            }

            $statementRows[] = [
                'id'               => $line->id,
                'entry_id'         => $line->journalEntry->id,
                'reference_number' => $line->journalEntry->reference_number,
                'entry_date'       => $line->journalEntry->entry_date->format('Y-m-d'),
                'description'      => $line->journalEntry->description,
                'memo'             => $line->memo ?? $line->journalEntry->description,
                'type'             => $line->journalEntry->type,
                'debit'            => $debit,
                'credit'           => $credit,
                'running_balance'  => $runningBalance,
                'created_by'       => $line->journalEntry->creator?->name ?? 'System',
            ];
        }

        $endingBalance = $runningBalance;

        return [
            'account'             => $account,
            'start_date'          => $startDate,
            'end_date'            => $endDate,
            'fiscal_year'         => $fiscalYear,
            'beginning_balance'   => $beginningBalance,
            'period_debits'       => $periodDebitTotal,
            'period_credits'      => $periodCreditTotal,
            'ending_balance'      => $endingBalance,
            'rows'                => $statementRows,
        ];
    }

    /**
     * Stream CSV Export of the Account General Ledger statement.
     */
    public function exportAccountCsv(int $accountId, ?string $startDate = null, ?string $endDate = null, ?string $fiscalYear = null): StreamedResponse
    {
        $statement = $this->getAccountLedgerStatement($accountId, $startDate, $endDate, $fiscalYear);
        $account = $statement['account'];

        $filename = 'GL_Ledger_' . $account->code . '_' . date('Ymd_His') . '.csv';

        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma'              => 'no-cache',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Expires'             => '0',
        ];

        $callback = function () use ($statement, $account): void {
            $handle = fopen('php://output', 'w');
            fputs($handle, "\xEF\xBB\xBF"); // UTF-8 BOM

            fputcsv($handle, ['St. Jude Metropolitan Medical Center']);
            fputcsv($handle, ['General Ledger Account Book & T-Account Statement']);
            fputcsv($handle, ["Account: [{$account->code}] {$account->name}"]);
            fputcsv($handle, ["Category: {$account->category} | Normal Balance: {$account->normal_balance}"]);
            fputcsv($handle, ["Statement Period: " . ($statement['start_date'] ?? 'Earliest') . " to " . ($statement['end_date'] ?? 'Latest')]);
            fputcsv($handle, ["Generated: " . date('Y-m-d H:i:s')]);
            fputcsv($handle, []);

            // Summary Header
            fputcsv($handle, ['SUMMARY OVERVIEW']);
            fputcsv($handle, ['Beginning Balance (PHP)', number_format((float) $statement['beginning_balance'], 2, '.', '')]);
            fputcsv($handle, ['Total Period Debits (PHP)', number_format((float) $statement['period_debits'], 2, '.', '')]);
            fputcsv($handle, ['Total Period Credits (PHP)', number_format((float) $statement['period_credits'], 2, '.', '')]);
            fputcsv($handle, ['Ending Running Balance (PHP)', number_format((float) $statement['ending_balance'], 2, '.', '')]);
            fputcsv($handle, []);

            // Transaction Detail Columns
            fputcsv($handle, [
                'Date',
                'Journal Ref #',
                'Transaction Type',
                'Description / Memo',
                'Debit (PHP)',
                'Credit (PHP)',
                'Cumulative Balance (PHP)',
            ]);

            // Initial row for Beginning Balance
            fputcsv($handle, [
                $statement['start_date'] ?? date('Y-m-01'),
                'BEGINNING BALANCE',
                'OPENING',
                'Beginning Balance forward',
                '0.00',
                '0.00',
                number_format((float) $statement['beginning_balance'], 2, '.', ''),
            ]);

            foreach ($statement['rows'] as $row) {
                fputcsv($handle, [
                    $row['entry_date'],
                    $row['reference_number'],
                    $row['type'],
                    $row['memo'],
                    number_format((float) $row['debit'], 2, '.', ''),
                    number_format((float) $row['credit'], 2, '.', ''),
                    number_format((float) $row['running_balance'], 2, '.', ''),
                ]);
            }

            fputcsv($handle, []);
            fputcsv($handle, [
                'ENDING BALANCE',
                '',
                '',
                '',
                number_format((float) $statement['period_debits'], 2, '.', ''),
                number_format((float) $statement['period_credits'], 2, '.', ''),
                number_format((float) $statement['ending_balance'], 2, '.', ''),
            ]);

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }
}
