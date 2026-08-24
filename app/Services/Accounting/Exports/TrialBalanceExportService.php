<?php

declare(strict_types=1);

namespace App\Services\Accounting\Exports;

use App\Models\Account;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class TrialBalanceExportService
{
    /**
     * Generate and stream a CSV export of the Trial Balance with active accounts, debits, credits, and ending net balances.
     */
    public function exportCsv(): StreamedResponse
    {
        $filename = 'Trial_Balance_' . date('Ymd_His') . '.csv';

        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma'              => 'no-cache',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Expires'             => '0',
        ];

        $callback = function (): void {
            $handle = fopen('php://output', 'w');

            // UTF-8 BOM for Microsoft Excel compliance
            fputs($handle, "\xEF\xBB\xBF");

            // Company & Report Header
            fputcsv($handle, ['St. Jude Metropolitan Medical Center']);
            fputcsv($handle, ['General Ledger Trial Balance Report']);
            fputcsv($handle, ['As of Date: ' . date('F d, Y h:i A')]);
            fputcsv($handle, ['BIR CAS Audit Compliant Export']);
            fputcsv($handle, []);

            // Table Header
            fputcsv($handle, [
                'Account Code',
                'Account Name',
                'Classification',
                'Department',
                'Normal Balance',
                'Total Debit (PHP)',
                'Total Credit (PHP)',
                'Ending Net Balance (PHP)',
            ]);

            $accounts = Account::with(['journalEntryLines.journalEntry' => function ($q) {
                $q->where('status', 'POSTED');
            }])->orderBy('code')->get();

            $totalDebits = '0.0000';
            $totalCredits = '0.0000';

            foreach ($accounts as $acc) {
                $debits = (string) $acc->journalEntryLines->sum('debit');
                $credits = (string) $acc->journalEntryLines->sum('credit');

                $netBalance = $acc->normal_balance === 'DEBIT'
                    ? bcsub($debits, $credits, 4)
                    : bcsub($credits, $debits, 4);

                $totalDebits = bcadd($totalDebits, $debits, 4);
                $totalCredits = bcadd($totalCredits, $credits, 4);

                fputcsv($handle, [
                    $acc->code,
                    $acc->name,
                    $acc->category,
                    $acc->department ?? 'FINANCE',
                    $acc->normal_balance,
                    number_format((float) $debits, 2, '.', ''),
                    number_format((float) $credits, 2, '.', ''),
                    number_format((float) $netBalance, 2, '.', ''),
                ]);
            }

            // Summary Totals
            fputcsv($handle, []);
            fputcsv($handle, [
                'GRAND TOTALS',
                '',
                '',
                '',
                '',
                number_format((float) $totalDebits, 2, '.', ''),
                number_format((float) $totalCredits, 2, '.', ''),
                bccomp($totalDebits, $totalCredits, 4) === 0 ? 'BALANCED (0.00)' : 'OUT OF BALANCE',
            ]);

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }
}
