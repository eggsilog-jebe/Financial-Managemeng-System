<?php

declare(strict_types=1);

namespace App\Services\Accounting\Reporting;

use App\Models\Account;
use Illuminate\Support\Facades\DB;

final class StatementOfChangesInEquityService
{
    public function __construct(
        private readonly ProfitAndLossService $pnlService
    ) {}

    /**
     * Compute Statement of Changes in Equity (SOCE) under PFRS / IAS 1.
     */
    public function getChangesInEquityData(?string $dateFrom = null, ?string $dateTo = null): array
    {
        $from = $dateFrom ?: date('Y-01-01');
        $to = $dateTo ?: date('Y-m-d');

        // 1. Get Equity Accounts (3000 series)
        $equityAccounts = Account::where('category', 'EQUITY')->orderBy('code')->get();

        // 2. Compute Opening Balances (prior to $from date)
        $openingLines = DB::table('journal_entry_lines')
            ->join('journal_entries', 'journal_entry_lines.journal_entry_id', '=', 'journal_entries.id')
            ->where('journal_entries.status', 'POSTED')
            ->where('journal_entries.entry_date', '<', $from)
            ->whereIn('journal_entry_lines.account_id', $equityAccounts->pluck('id'))
            ->select(
                'journal_entry_lines.account_id',
                DB::raw('SUM(journal_entry_lines.credit - journal_entry_lines.debit) as net_balance')
            )
            ->groupBy('journal_entry_lines.account_id')
            ->get()
            ->keyBy('account_id');

        // 3. Compute Current Period Movements (between $from and $to)
        $periodLines = DB::table('journal_entry_lines')
            ->join('journal_entries', 'journal_entry_lines.journal_entry_id', '=', 'journal_entries.id')
            ->where('journal_entries.status', 'POSTED')
            ->whereBetween('journal_entries.entry_date', [$from, $to])
            ->whereIn('journal_entry_lines.account_id', $equityAccounts->pluck('id'))
            ->select(
                'journal_entry_lines.account_id',
                DB::raw('SUM(journal_entry_lines.credit) as total_credit'),
                DB::raw('SUM(journal_entry_lines.debit) as total_debit')
            )
            ->groupBy('journal_entry_lines.account_id')
            ->get()
            ->keyBy('account_id');

        $openingTotal = '0.0000';
        $capitalAdditions = '0.0000';
        $distributions = '0.0000';
        $accountsBreakdown = [];

        foreach ($equityAccounts as $acc) {
            $open = $openingLines->get($acc->id);
            $openBal = $open ? (string) $open->net_balance : '0.0000';
            $openingTotal = bcadd($openingTotal, $openBal, 4);

            $period = $periodLines->get($acc->id);
            $cr = $period ? (string) $period->total_credit : '0.0000';
            $dr = $period ? (string) $period->total_debit : '0.0000';

            $capitalAdditions = bcadd($capitalAdditions, $cr, 4);
            $distributions = bcadd($distributions, $dr, 4);

            $closingAccBal = bcadd($openBal, bcsub($cr, $dr, 4), 4);

            $accountsBreakdown[] = [
                'code'            => $acc->code,
                'name'            => $acc->name,
                'opening_balance' => $openBal,
                'additions'       => $cr,
                'deductions'      => $dr,
                'closing_balance' => $closingAccBal,
            ];
        }

        // 4. Current Period Net Income from Profit & Loss
        $pnl = $this->pnlService->getProfitAndLossData($from, $to);
        $netSurplus = $pnl['net_income'];

        // 5. Ending Total Equity = Opening + Capital Additions - Distributions + Net Surplus
        $netEquityMovements = bcsub($capitalAdditions, $distributions, 4);
        $totalClosingEquity = bcadd(bcadd($openingTotal, $netEquityMovements, 4), $netSurplus, 4);

        return [
            'date_from'               => $from,
            'date_to'                 => $to,
            'opening_equity'          => $openingTotal,
            'capital_additions'       => $capitalAdditions,
            'distributions'           => $distributions,
            'current_period_surplus'  => $netSurplus,
            'net_equity_movements'    => $netEquityMovements,
            'total_closing_equity'    => $totalClosingEquity,
            'accounts'                => $accountsBreakdown,
        ];
    }
}
