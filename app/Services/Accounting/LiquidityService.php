<?php

declare(strict_types=1);

namespace App\Services\Accounting;

use App\Models\BankAccount;
use App\Models\PayrollRun;
use App\Models\PurchaseBill;

final class LiquidityService
{
    /**
     * Compute Executive Treasury Liquidity, Bank Concentration, and Days Cash on Hand (DCOH).
     */
    public function getLiquidityMetrics(): array
    {
        $accounts = BankAccount::with(['glAccount'])->orderByDesc('balance')->get();
        $activeAccounts = $accounts->where('status', 'Active')->where('is_active', true);

        $totalCash = '0.0000';
        foreach ($activeAccounts as $acc) {
            $totalCash = bcadd($totalCash, (string) $acc->balance, 4);
        }

        // Calculate Daily Operating Expense Burn Rate
        // Trailing AP and Payroll 30-day commitments
        $recentAp = PurchaseBill::whereDate('created_at', '>=', now()->subDays(30))->sum('total_amount');
        $recentPayroll = PayrollRun::whereDate('created_at', '>=', now()->subDays(30))->sum('total_net_pay');
        $monthlyOperatingExpense = bcadd((string) $recentAp, (string) $recentPayroll, 4);

        if (bccomp($monthlyOperatingExpense, '0.0000', 4) <= 0) {
            $monthlyOperatingExpense = '1500000.0000'; // Baseline estimate for hospital operations
        }

        $dailyBurnRate = bcdiv($monthlyOperatingExpense, '30', 4);
        $dcoh = (bccomp($dailyBurnRate, '0.0000', 4) > 0)
            ? (float) bcdiv($totalCash, $dailyBurnRate, 2)
            : 0.0;

        // Health Status Indicator
        $liquidityStatus = match (true) {
            $dcoh >= 60.0 => ['rating' => 'EXCELLENT', 'badge' => 'bg-success text-white', 'desc' => 'Over 60 Days Operating Buffer (Optimal Liquidity)'],
            $dcoh >= 30.0 => ['rating' => 'ADEQUATE', 'badge' => 'bg-primary text-white', 'desc' => '30-59 Days Operating Buffer (Healthy Runway)'],
            $dcoh >= 15.0 => ['rating' => 'CAUTION', 'badge' => 'bg-warning text-dark', 'desc' => '15-29 Days Operating Buffer (Monitor Inflows Closely)'],
            default       => ['rating' => 'CRITICAL', 'badge' => 'bg-danger text-white', 'desc' => '< 15 Days Operating Buffer (Immediate Capital Injection Required)'],
        };

        // Concentration Analysis & Safety Thresholds
        $concentrationData = [];
        $belowMinimumCount = 0;

        foreach ($accounts as $acc) {
            $bal = (string) $acc->balance;
            $min = (string) $acc->minimum_balance;
            $isBelowMin = (bccomp($bal, $min, 4) < 0);
            if ($isBelowMin && $acc->is_active) {
                $belowMinimumCount++;
            }

            $percentage = (bccomp($totalCash, '0.0000', 4) > 0)
                ? (float) bcmul(bcdiv($bal, $totalCash, 4), '100', 2)
                : 0.0;

            $concentrationData[] = [
                'id'              => $acc->id,
                'name'            => $acc->name,
                'bank_name'       => $acc->bank_name,
                'account_number'  => $acc->account_number,
                'gl_code'         => $acc->gl_code,
                'balance'         => $bal,
                'minimum_balance' => $min,
                'percentage'      => $percentage,
                'is_below_min'    => $isBelowMin,
                'status'          => $acc->status,
                'is_active'       => $acc->is_active,
            ];
        }

        return [
            'total_cash'                 => $totalCash,
            'monthly_operating_expense'  => $monthlyOperatingExpense,
            'daily_burn_rate'            => $dailyBurnRate,
            'days_cash_on_hand'          => $dcoh,
            'liquidity_status'           => $liquidityStatus,
            'concentration'              => $concentrationData,
            'below_minimum_count'        => $belowMinimumCount,
            'total_accounts_count'       => count($accounts),
            'active_accounts_count'      => count($activeAccounts),
        ];
    }
}
