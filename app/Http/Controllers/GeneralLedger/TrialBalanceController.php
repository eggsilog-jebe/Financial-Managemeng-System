<?php

declare(strict_types=1);

namespace App\Http\Controllers\GeneralLedger;

use App\Http\Controllers\Controller;
use App\Services\Accounting\TrialBalanceService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class TrialBalanceController extends Controller
{
    public function __construct(
        private readonly TrialBalanceService $trialBalanceService,
    ) {}

    public function index(Request $request): View
    {
        $asOfDate = $request->query('as_of_date') ?? date('Y-m-d');
        $hideZeroBalances = filter_var($request->query('hide_zero_balances', false), FILTER_VALIDATE_BOOLEAN);
        $category = $request->query('category');
        $search = $request->query('q') ?? $request->query('search');

        $report = $this->trialBalanceService->getTrialBalanceReport(
            asOfDate: $asOfDate,
            hideZeroBalances: $hideZeroBalances,
            category: $category,
            search: $search,
        );

        return view('general-ledger.trial-balance', [
            'report'             => $report,
            'asOfDate'           => $asOfDate,
            'hideZeroBalances'   => $hideZeroBalances,
            'selectedCategory'   => $category,
            'search'             => $search,
            'totalDebitBalance'  => (float) $report['total_debit_balance'],
            'totalCreditBalance' => (float) $report['total_credit_balance'],
            'discrepancy'        => (float) $report['discrepancy'],
            'isBalanced'         => $report['is_balanced'],
            'rows'               => $report['rows'],
        ]);
    }

    public function export(Request $request): StreamedResponse
    {
        $asOfDate = $request->query('as_of_date') ?? date('Y-m-d');
        $hideZeroBalances = filter_var($request->query('hide_zero_balances', false), FILTER_VALIDATE_BOOLEAN);
        $category = $request->query('category');

        return $this->trialBalanceService->exportCsv(
            asOfDate: $asOfDate,
            hideZeroBalances: $hideZeroBalances,
            category: $category,
        );
    }
}
