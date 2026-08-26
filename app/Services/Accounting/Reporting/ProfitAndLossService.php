<?php

declare(strict_types=1);

namespace App\Services\Accounting\Reporting;

use App\Models\Account;
use Illuminate\Support\Facades\DB;

final class ProfitAndLossService
{
    /**
     * Compute Profit & Loss / Income Statement for a given date range.
     */
    public function getProfitAndLossData(?string $dateFrom = null, ?string $dateTo = null, ?string $department = null): array
    {
        $from = $dateFrom ?: date('Y-01-01');
        $to = $dateTo ?: date('Y-m-d');

        $query = DB::table('journal_entry_lines')
            ->join('journal_entries', 'journal_entry_lines.journal_entry_id', '=', 'journal_entries.id')
            ->join('accounts', 'journal_entry_lines.account_id', '=', 'accounts.id')
            ->where('journal_entries.status', 'POSTED')
            ->whereBetween('journal_entries.entry_date', [$from, $to])
            ->whereIn('accounts.category', ['REVENUE', 'EXPENSE']);

        if ($department) {
            $query->where('accounts.department', $department);
        }

        $lines = $query->select(
            'accounts.id as account_id',
            'accounts.code',
            'accounts.name',
            'accounts.category',
            'accounts.department',
            'accounts.normal_balance',
            DB::raw('SUM(journal_entry_lines.debit) as total_debit'),
            DB::raw('SUM(journal_entry_lines.credit) as total_credit')
        )
        ->groupBy('accounts.id', 'accounts.code', 'accounts.name', 'accounts.category', 'accounts.department', 'accounts.normal_balance')
        ->orderBy('accounts.code')
        ->get();

        $revenueRows = [];
        $expenseRows = [];

        $grossRevenue = '0.0000';
        $salesDiscounts = '0.0000';
        $totalExpenses = '0.0000';

        foreach ($lines as $line) {
            $deb = (string) $line->total_debit;
            $crd = (string) $line->total_credit;

            $isDebitNormal = strtoupper((string) $line->normal_balance) === 'DEBIT';
            $bal = $isDebitNormal ? bcsub($deb, $crd, 4) : bcsub($crd, $deb, 4);

            $row = [
                'id'         => $line->account_id,
                'code'       => $line->code,
                'name'       => $line->name,
                'category'   => $line->category,
                'department' => $line->department ?? 'General Hospital',
                'balance'    => $bal,
            ];

            if ($line->category === 'REVENUE') {
                // If account is Sales Discount / Statutory Discount (code 5010 or 4090 or contra)
                if (str_contains(strtolower($line->name), 'discount') || str_starts_with($line->code, '5010')) {
                    $salesDiscounts = bcadd($salesDiscounts, $bal, 4);
                } else {
                    $grossRevenue = bcadd($grossRevenue, $bal, 4);
                }
                $revenueRows[] = $row;
            } elseif ($line->category === 'EXPENSE') {
                $totalExpenses = bcadd($totalExpenses, $bal, 4);
                $expenseRows[] = $row;
            }
        }

        $netRevenue = bcsub($grossRevenue, $salesDiscounts, 4);
        $netIncome = bcsub($netRevenue, $totalExpenses, 4);

        // Operating Profit Margin %
        $profitMargin = (bccomp($netRevenue, '0.0000', 4) > 0)
            ? (float) bcmul(bcdiv($netIncome, $netRevenue, 4), '100', 2)
            : 0.0;

        return [
            'date_from'        => $from,
            'date_to'          => $to,
            'department'       => $department,
            'revenues'         => $revenueRows,
            'expenses'         => $expenseRows,
            'gross_revenue'    => $grossRevenue,
            'sales_discounts'  => $salesDiscounts,
            'net_revenue'      => $netRevenue,
            'total_revenue'    => $netRevenue,
            'total_expense'    => $totalExpenses,
            'total_expenses'   => $totalExpenses,
            'net_income'       => $netIncome,
            'profit_margin'    => $profitMargin,
        ];
    }
}
