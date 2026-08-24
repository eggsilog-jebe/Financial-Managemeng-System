<?php

declare(strict_types=1);

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Services\Accounting\BirTaxScheduleService;
use App\Services\Accounting\GeneralLedgerReportService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

final class FinancialReportsViewController extends Controller
{
    public function index(
        Request $request,
        GeneralLedgerReportService $glReportService,
        BirTaxScheduleService $taxService
    ): View {
        $tab = $request->query('tab', 'trial-balance');

        $trialBalance = $glReportService->getTrialBalance();
        $pnl = $glReportService->getIncomeStatement();
        $balanceSheet = $glReportService->getBalanceSheet();

        $bir1601eq = $taxService->getBir1601EQSummary('2026-01-01', '2026-12-31');
        $birVat = $taxService->getBirVatSummary('2026-01-01', '2026-12-31');

        return view('accounting.reports.index', [
            'tab'          => $tab,
            'trialBalance' => $trialBalance,
            'pnl'          => $pnl,
            'balanceSheet' => $balanceSheet,
            'bir1601eq'    => $bir1601eq,
            'birVat'       => $birVat,
        ]);
    }
}
