<?php

declare(strict_types=1);

namespace App\Services\Accounting;

use App\DTOs\Accounting\TrialBalanceFilterData;
use App\Models\Account;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class TrialBalanceService
{
    /**
     * Compute comprehensive Trial Balance with double-entry balance verification.
     */
    public function getTrialBalanceReport(
        ?string $asOfDate = null,
        bool $hideZeroBalances = false,
        ?string $category = null,
        ?string $search = null,
    ): array {
        $query = Account::with(['journalEntryLines' => function ($q) use ($asOfDate): void {
            $q->whereHas('journalEntry', function ($je) use ($asOfDate): void {
                $je->where('status', 'POSTED');
                if ($asOfDate) {
                    $je->whereDate('entry_date', '<=', $asOfDate);
                }
            });
        }])->orderBy('code');

        if ($category) {
            $query->where('category', strtoupper($category));
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('code', 'LIKE', "%{$search}%")
                  ->orWhere('name', 'LIKE', "%{$search}%");
            });
        }

        $accounts = $query->get();

        $rows = [];
        $totalDebitBalance = '0.0000';
        $totalCreditBalance = '0.0000';

        foreach ($accounts as $acc) {
            $debits = (string) $acc->journalEntryLines->sum('debit');
            $credits = (string) $acc->journalEntryLines->sum('credit');

            $netBalance = bcsub($debits, $credits, 4);

            $debitCol = '0.0000';
            $creditCol = '0.0000';

            if (strtoupper((string) $acc->normal_balance) === 'DEBIT') {
                if (bccomp($netBalance, '0.0000', 4) >= 0) {
                    $debitCol = $netBalance;
                } else {
                    $creditCol = bcmul($netBalance, '-1.0000', 4);
                }
            } else {
                $creditNet = bcsub($credits, $debits, 4);
                if (bccomp($creditNet, '0.0000', 4) >= 0) {
                    $creditCol = $creditNet;
                } else {
                    $debitCol = bcmul($creditNet, '-1.0000', 4);
                }
            }

            $hasActivity = (bccomp($debitCol, '0.0000', 4) !== 0) || (bccomp($creditCol, '0.0000', 4) !== 0);

            if ($hideZeroBalances && ! $hasActivity) {
                continue;
            }

            $totalDebitBalance = bcadd($totalDebitBalance, $debitCol, 4);
            $totalCreditBalance = bcadd($totalCreditBalance, $creditCol, 4);

            $rows[] = [
                'id'             => $acc->id,
                'code'           => $acc->code,
                'name'           => $acc->name,
                'category'       => $acc->category,
                'normal_balance' => $acc->normal_balance,
                'debit'          => $debitCol,
                'credit'         => $creditCol,
                'is_zero'        => ! $hasActivity,
            ];
        }

        $discrepancy = bcsub($totalDebitBalance, $totalCreditBalance, 4);
        $isBalanced = bccomp($totalDebitBalance, $totalCreditBalance, 4) === 0;

        return [
            'as_of_date'           => $asOfDate ?? date('Y-m-d'),
            'hide_zero_balances'   => $hideZeroBalances,
            'category_filter'      => $category,
            'search'               => $search,
            'rows'                 => $rows,
            'total_debit_balance'  => $totalDebitBalance,
            'total_credit_balance' => $totalCreditBalance,
            'discrepancy'          => $discrepancy,
            'is_balanced'          => $isBalanced,
            'accounts_count'       => count($rows),
        ];
    }

    /**
     * Stream CSV export of Trial Balance report.
     */
    public function exportCsv(?string $asOfDate = null, bool $hideZeroBalances = false, ?string $category = null): StreamedResponse
    {
        $report = $this->getTrialBalanceReport($asOfDate, $hideZeroBalances, $category);
        $filename = 'Trial_Balance_' . ($asOfDate ?? date('Ymd')) . '_' . date('His') . '.csv';

        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma'              => 'no-cache',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Expires'             => '0',
        ];

        $callback = function () use ($report): void {
            $handle = fopen('php://output', 'w');
            fputs($handle, "\xEF\xBB\xBF"); // UTF-8 BOM

            fputcsv($handle, ['St. Jude Metropolitan Medical Center']);
            fputcsv($handle, ['Statement of General Ledger Trial Balance']);
            fputcsv($handle, ["As-of Date: " . ($report['as_of_date'] ?? date('Y-m-d'))]);
            fputcsv($handle, ["Audit Status: " . ($report['is_balanced'] ? 'BALANCED' : 'OUT OF BALANCE')]);
            fputcsv($handle, ["Generated: " . date('Y-m-d H:i:s')]);
            fputcsv($handle, []);

            fputcsv($handle, [
                'Account Code',
                'Account Title',
                'Classification',
                'Normal Balance',
                'Debit Balance (PHP)',
                'Credit Balance (PHP)',
            ]);

            foreach ($report['rows'] as $row) {
                fputcsv($handle, [
                    $row['code'],
                    $row['name'],
                    $row['category'],
                    $row['normal_balance'],
                    number_format((float) $row['debit'], 2, '.', ''),
                    number_format((float) $row['credit'], 2, '.', ''),
                ]);
            }

            fputcsv($handle, []);
            fputcsv($handle, [
                'TOTALS',
                '',
                '',
                $report['is_balanced'] ? 'BALANCED (0.00)' : 'OUT OF BALANCE (Variance: ' . $report['discrepancy'] . ')',
                number_format((float) $report['total_debit_balance'], 2, '.', ''),
                number_format((float) $report['total_credit_balance'], 2, '.', ''),
            ]);

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }
}
