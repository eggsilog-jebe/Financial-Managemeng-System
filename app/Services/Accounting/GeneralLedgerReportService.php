<?php

declare(strict_types=1);

namespace App\Services\Accounting;

use App\Models\Account;
use App\Models\JournalEntryLine;
use Illuminate\Support\Collection;

final class GeneralLedgerReportService
{
    /**
     * Compute real-time Trial Balance with double-entry balance verification.
     */
    public function getTrialBalance(): array
    {
        $accounts = Account::with(['journalEntryLines.journalEntry' => function ($q) {
            $q->where('status', 'POSTED');
        }])->orderBy('code')->get();

        $rows = [];
        $totalDebit = '0.0000';
        $totalCredit = '0.0000';

        foreach ($accounts as $acc) {
            $debits = (string) $acc->journalEntryLines->sum('debit');
            $credits = (string) $acc->journalEntryLines->sum('credit');

            $netBalance = bcsub($debits, $credits, 4);

            $debitCol = '0.0000';
            $creditCol = '0.0000';

            if ($acc->normal_balance === 'DEBIT') {
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

            $totalDebit = bcadd($totalDebit, $debitCol, 4);
            $totalCredit = bcadd($totalCredit, $creditCol, 4);

            $rows[] = [
                'code'           => $acc->code,
                'name'           => $acc->name,
                'category'       => $acc->category,
                'normal_balance' => $acc->normal_balance,
                'debit'          => $debitCol,
                'credit'         => $creditCol,
            ];
        }

        return [
            'accounts'     => $rows,
            'total_debit'  => $totalDebit,
            'total_credit' => $totalCredit,
            'is_balanced'  => bccomp($totalDebit, $totalCredit, 4) === 0,
        ];
    }

    /**
     * Compute Balance Sheet (Assets = Liabilities + Equity + Current Period Net Income).
     */
    public function getBalanceSheet(): array
    {
        $trial = $this->getTrialBalance();

        $assets = [];
        $liabilities = [];
        $equity = [];

        $totalAssets = '0.0000';
        $totalLiabilities = '0.0000';
        $totalEquity = '0.0000';

        foreach ($trial['accounts'] as $acc) {
            if ($acc['category'] === 'ASSET') {
                $bal = bcsub($acc['debit'], $acc['credit'], 4);
                $totalAssets = bcadd($totalAssets, $bal, 4);
                $assets[] = ['code' => $acc['code'], 'name' => $acc['name'], 'balance' => $bal];
            } elseif ($acc['category'] === 'LIABILITY') {
                $bal = bcsub($acc['credit'], $acc['debit'], 4);
                $totalLiabilities = bcadd($totalLiabilities, $bal, 4);
                $liabilities[] = ['code' => $acc['code'], 'name' => $acc['name'], 'balance' => $bal];
            } elseif ($acc['category'] === 'EQUITY') {
                $bal = bcsub($acc['credit'], $acc['debit'], 4);
                $totalEquity = bcadd($totalEquity, $bal, 4);
                $equity[] = ['code' => $acc['code'], 'name' => $acc['name'], 'balance' => $bal];
            }
        }

        $pnl = $this->getIncomeStatement();
        $retainedEarnings = $pnl['net_income'];
        $totalEquityAndLiabilities = bcadd(bcadd($totalLiabilities, $totalEquity, 4), $retainedEarnings, 4);

        return [
            'assets'                        => $assets,
            'total_assets'                  => $totalAssets,
            'liabilities'                   => $liabilities,
            'total_liabilities'             => $totalLiabilities,
            'equity'                        => $equity,
            'total_equity'                  => $totalEquity,
            'current_period_net_income'     => $retainedEarnings,
            'total_liabilities_and_equity'  => $totalEquityAndLiabilities,
            'is_balanced'                   => bccomp($totalAssets, $totalEquityAndLiabilities, 4) === 0,
        ];
    }

    /**
     * Compute Income Statement (Profit & Loss: Revenue - Operating Expenses).
     */
    public function getIncomeStatement(): array
    {
        $trial = $this->getTrialBalance();

        $revenues = [];
        $expenses = [];

        $totalRevenue = '0.0000';
        $totalExpense = '0.0000';

        foreach ($trial['accounts'] as $acc) {
            if ($acc['category'] === 'REVENUE') {
                $bal = bcsub($acc['credit'], $acc['debit'], 4);
                $totalRevenue = bcadd($totalRevenue, $bal, 4);
                $revenues[] = ['code' => $acc['code'], 'name' => $acc['name'], 'balance' => $bal];
            } elseif ($acc['category'] === 'EXPENSE') {
                $bal = bcsub($acc['debit'], $acc['credit'], 4);
                $totalExpense = bcadd($totalExpense, $bal, 4);
                $expenses[] = ['code' => $acc['code'], 'name' => $acc['name'], 'balance' => $bal];
            }
        }

        $netIncome = bcsub($totalRevenue, $totalExpense, 4);

        return [
            'revenues'      => $revenues,
            'total_revenue' => $totalRevenue,
            'expenses'      => $expenses,
            'total_expense' => $totalExpense,
            'net_income'    => $netIncome,
        ];
    }
}
