<?php

declare(strict_types=1);

namespace App\Services\Accounting\Reporting;

use App\Models\Account;
use App\Models\BankAccount;
use App\Models\Invoice;
use App\Models\PurchaseBill;
use Illuminate\Support\Facades\DB;

final class FinancialKpiService
{
    /**
     * Compute Executive KPI Metrics Deck & 12-Month Trajectory.
     */
    public function getKpiMetrics(): array
    {
        // 1. Total Outstanding Accounts Receivable (AR)
        $totalAr = Invoice::whereIn('status', ['ISSUED', 'PARTIALLY_PAID', 'PENDING', 'UNPAID'])->sum('patient_payable');
        $totalArStr = (string) $totalAr;

        // 2. Total Outstanding Accounts Payable (AP)
        $totalAp = PurchaseBill::whereIn('status', ['UNPAID', 'PARTIAL', 'APPROVED', 'OVERDUE'])->sum(DB::raw('total_amount - paid_amount'));
        $totalApStr = (string) $totalAp;

        // 3. Liquid Cash Pool
        $bankAccounts = BankAccount::where('status', 'Active')->where('is_active', true)->get();
        $totalCash = '0.0000';
        foreach ($bankAccounts as $acc) {
            $totalCash = bcadd($totalCash, (string) $acc->balance, 4);
        }

        // 4. Financial Statement Line Aggregates (Year-to-Date)
        $accounts = Account::orderBy('code')->get();
        $lines = DB::table('journal_entry_lines')
            ->join('journal_entries', 'journal_entry_lines.journal_entry_id', '=', 'journal_entries.id')
            ->where('journal_entries.status', 'POSTED')
            ->select(
                'journal_entry_lines.account_id',
                DB::raw('SUM(journal_entry_lines.debit) as total_debit'),
                DB::raw('SUM(journal_entry_lines.credit) as total_credit')
            )
            ->groupBy('journal_entry_lines.account_id')
            ->get()
            ->keyBy('account_id');

        $totalRevenue = '0.0000';
        $totalExpense = '0.0000';
        $totalAssets = '0.0000';
        $totalLiabilities = '0.0000';
        $supplyExpenses = '0.0000';

        foreach ($accounts as $acc) {
            $agg = $lines->get($acc->id);
            $deb = $agg ? (string) $agg->total_debit : '0.0000';
            $crd = $agg ? (string) $agg->total_credit : '0.0000';

            $isDebitNormal = strtoupper((string) $acc->normal_balance) === 'DEBIT';
            $bal = $isDebitNormal ? bcsub($deb, $crd, 4) : bcsub($crd, $deb, 4);

            $category = strtoupper((string) $acc->category);

            if ($category === 'REVENUE') {
                $totalRevenue = bcadd($totalRevenue, $bal, 4);
            } elseif ($category === 'EXPENSE') {
                $totalExpense = bcadd($totalExpense, $bal, 4);
                if (str_contains(strtolower($acc->name), 'supply') || str_contains(strtolower($acc->name), 'inventory') || str_starts_with($acc->code, '5000')) {
                    $supplyExpenses = bcadd($supplyExpenses, $bal, 4);
                }
            } elseif ($category === 'ASSET') {
                $totalAssets = bcadd($totalAssets, $bal, 4);
            } elseif ($category === 'LIABILITY') {
                $totalLiabilities = bcadd($totalLiabilities, $bal, 4);
            }
        }

        if (bccomp($supplyExpenses, '0.0000', 4) <= 0) {
            $supplyExpenses = bcmul($totalExpense, '0.40', 4); // Estimated 40% supplies baseline
        }

        // 5. Compute Ratios
        // Operating Margin
        $netIncome = bcsub($totalRevenue, $totalExpense, 4);
        $operatingMargin = (bccomp($totalRevenue, '0.0000', 4) > 0)
            ? (float) bcmul(bcdiv($netIncome, $totalRevenue, 4), '100', 2)
            : 0.0;

        // Current Ratio
        $currentRatio = (bccomp($totalLiabilities, '0.0000', 4) > 0)
            ? (float) bcdiv($totalAssets, $totalLiabilities, 2)
            : 0.0;

        // Quick Ratio: (Cash + AR) / Liabilities
        $quickAssets = bcadd($totalCash, $totalArStr, 4);
        $quickRatio = (bccomp($totalLiabilities, '0.0000', 4) > 0)
            ? (float) bcdiv($quickAssets, $totalLiabilities, 2)
            : 0.0;

        // Days Sales Outstanding (DSO)
        $avgDailyRevenue = (bccomp($totalRevenue, '0.0000', 4) > 0)
            ? bcdiv($totalRevenue, '365', 4)
            : '1.0000';
        $dso = (float) bcdiv($totalArStr, $avgDailyRevenue, 1);

        // Days Payable Outstanding (DPO)
        $avgDailySupplyCost = (bccomp($supplyExpenses, '0.0000', 4) > 0)
            ? bcdiv($supplyExpenses, '365', 4)
            : '1.0000';
        $dpo = (float) bcdiv($totalApStr, $avgDailySupplyCost, 1);

        // Daily Burn & Days Cash on Hand (DCOH)
        $dailyBurnRate = (bccomp($totalExpense, '0.0000', 4) > 0)
            ? bcdiv($totalExpense, '365', 4)
            : '50000.0000';
        $dcoh = (float) bcdiv($totalCash, $dailyBurnRate, 1);

        // 6. 12-Month Revenue vs Expense Trajectory
        $months = [];
        for ($i = 11; $i >= 0; $i--) {
            $mStart = date('Y-m-01', strtotime("-{$i} months"));
            $mEnd = date('Y-m-t', strtotime("-{$i} months"));
            $mLabel = date('M Y', strtotime($mStart));

            $mRev = DB::table('journal_entry_lines')
                ->join('journal_entries', 'journal_entry_lines.journal_entry_id', '=', 'journal_entries.id')
                ->join('accounts', 'journal_entry_lines.account_id', '=', 'accounts.id')
                ->where('journal_entries.status', 'POSTED')
                ->whereBetween('journal_entries.entry_date', [$mStart, $mEnd])
                ->where('accounts.category', 'REVENUE')
                ->sum(DB::raw('journal_entry_lines.credit - journal_entry_lines.debit'));

            $mExp = DB::table('journal_entry_lines')
                ->join('journal_entries', 'journal_entry_lines.journal_entry_id', '=', 'journal_entries.id')
                ->join('accounts', 'journal_entry_lines.account_id', '=', 'accounts.id')
                ->where('journal_entries.status', 'POSTED')
                ->whereBetween('journal_entries.entry_date', [$mStart, $mEnd])
                ->where('accounts.category', 'EXPENSE')
                ->sum(DB::raw('journal_entry_lines.debit - journal_entry_lines.credit'));

            $months[] = [
                'label'   => $mLabel,
                'revenue' => (float) $mRev,
                'expense' => (float) $mExp,
                'surplus' => (float) ($mRev - $mExp),
            ];
        }

        return [
            'total_ar'                => $totalArStr,
            'total_ap'                => $totalApStr,
            'total_cash'              => $totalCash,
            'total_revenue'           => $totalRevenue,
            'totalRevenue'            => (float) $totalRevenue,
            'total_expense'           => $totalExpense,
            'totalExpense'            => (float) $totalExpense,
            'total_assets'            => $totalAssets,
            'totalAssets'             => (float) $totalAssets,
            'total_liabilities'       => $totalLiabilities,
            'totalLiabilities'        => (float) $totalLiabilities,
            'net_income'              => $netIncome,
            'operating_margin'        => $operatingMargin,
            'operatingProfitMargin'   => $operatingMargin,
            'current_ratio'           => $currentRatio,
            'currentRatio'            => $currentRatio,
            'quick_ratio'             => $quickRatio,
            'dso'                     => $dso,
            'dpo'                     => $dpo,
            'daily_burn_rate'         => $dailyBurnRate,
            'days_cash_on_hand'       => $dcoh,
            'trajectory'              => $months,
        ];
    }
}
