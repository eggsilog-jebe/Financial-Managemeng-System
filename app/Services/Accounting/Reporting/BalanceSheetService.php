<?php

declare(strict_types=1);

namespace App\Services\Accounting\Reporting;

use App\Models\Account;
use App\Models\JournalEntryLine;
use Illuminate\Support\Facades\DB;

final class BalanceSheetService
{
    /**
     * Compute Balance Sheet as of a specific cutoff date.
     */
    public function getBalanceSheetData(?string $asOfDate = null, string $comparison = 'none'): array
    {
        $cutoff = $asOfDate ?: date('Y-m-d');

        $currentData = $this->calculateBalancesForDate($cutoff);

        $comparisonData = null;
        $compDate = null;

        if ($comparison === 'prior_year') {
            $compDate = date('Y-m-d', strtotime($cutoff . ' -1 year'));
            $comparisonData = $this->calculateBalancesForDate($compDate);
        } elseif ($comparison === 'prior_quarter') {
            $compDate = date('Y-m-d', strtotime($cutoff . ' -3 months'));
            $comparisonData = $this->calculateBalancesForDate($compDate);
        }

        return [
            'as_of_date'       => $cutoff,
            'comparison_type'  => $comparison,
            'comparison_date'  => $compDate,
            'current'          => $currentData,
            'comparison'       => $comparisonData,
        ];
    }

    private function calculateBalancesForDate(string $cutoffDate): array
    {
        $accounts = Account::orderBy('code')->get();

        // Get posted line aggregates up to cutoffDate
        $lines = DB::table('journal_entry_lines')
            ->join('journal_entries', 'journal_entry_lines.journal_entry_id', '=', 'journal_entries.id')
            ->where('journal_entries.status', 'POSTED')
            ->whereDate('journal_entries.entry_date', '<=', $cutoffDate)
            ->select(
                'journal_entry_lines.account_id',
                DB::raw('SUM(journal_entry_lines.debit) as total_debit'),
                DB::raw('SUM(journal_entry_lines.credit) as total_credit')
            )
            ->groupBy('journal_entry_lines.account_id')
            ->get()
            ->keyBy('account_id');

        $assetRows = [];
        $liabilityRows = [];
        $equityRows = [];

        $totalAssets = '0.0000';
        $totalLiabilities = '0.0000';
        $totalEquity = '0.0000';

        // Calculate Revenue & Expense for Net Operating Surplus to Equity
        $totalRevenue = '0.0000';
        $totalExpense = '0.0000';

        foreach ($accounts as $acc) {
            $agg = $lines->get($acc->id);
            $deb = $agg ? (string) $agg->total_debit : '0.0000';
            $crd = $agg ? (string) $agg->total_credit : '0.0000';

            $isDebitNormal = strtoupper((string) $acc->normal_balance) === 'DEBIT';
            $bal = $isDebitNormal ? bcsub($deb, $crd, 4) : bcsub($crd, $deb, 4);

            $category = strtoupper((string) $acc->category);

            $row = [
                'id'             => $acc->id,
                'code'           => $acc->code,
                'name'           => $acc->name,
                'category'       => $category,
                'normal_balance' => $acc->normal_balance,
                'balance'        => $bal,
            ];

            if ($category === 'ASSET') {
                $assetRows[] = $row;
                $totalAssets = bcadd($totalAssets, $bal, 4);
            } elseif ($category === 'LIABILITY') {
                $liabilityRows[] = $row;
                $totalLiabilities = bcadd($totalLiabilities, $bal, 4);
            } elseif ($category === 'EQUITY') {
                $equityRows[] = $row;
                $totalEquity = bcadd($totalEquity, $bal, 4);
            } elseif ($category === 'REVENUE') {
                $totalRevenue = bcadd($totalRevenue, $bal, 4);
            } elseif ($category === 'EXPENSE') {
                $totalExpense = bcadd($totalExpense, $bal, 4);
            }
        }

        // Current Period Net Operating Surplus (Revenues - Expenses) flows into Equity
        $netIncome = bcsub($totalRevenue, $totalExpense, 4);
        $adjustedEquity = bcadd($totalEquity, $netIncome, 4);

        $totalLiabAndEquity = bcadd($totalLiabilities, $adjustedEquity, 4);
        $variance = bcsub($totalAssets, $totalLiabAndEquity, 4);
        $isBalanced = (bccomp($variance, '0.0000', 4) === 0);

        // Current Working Ratio: Current Assets / Current Liabilities
        $currentRatio = (bccomp($totalLiabilities, '0.0000', 4) > 0)
            ? (float) bcdiv($totalAssets, $totalLiabilities, 2)
            : 0.0;

        return [
            'assets'                => $assetRows,
            'liabilities'           => $liabilityRows,
            'equity'                => $equityRows,
            'total_assets'          => $totalAssets,
            'total_liabilities'     => $totalLiabilities,
            'total_equity_base'     => $totalEquity,
            'current_year_surplus'  => $netIncome,
            'total_equity'          => $adjustedEquity,
            'total_liab_and_equity' => $totalLiabAndEquity,
            'variance'              => $variance,
            'is_balanced'           => $isBalanced,
            'current_ratio'         => $currentRatio,
        ];
    }
}
