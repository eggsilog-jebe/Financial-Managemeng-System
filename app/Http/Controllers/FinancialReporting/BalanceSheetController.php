<?php

declare(strict_types=1);

namespace App\Http\Controllers\FinancialReporting;

use App\Http\Controllers\Controller;
use App\Services\Accounting\Reporting\BalanceSheetService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class BalanceSheetController extends Controller
{
    public function __construct(
        private readonly BalanceSheetService $balanceSheetService
    ) {}

    public function index(Request $request): View
    {
        $asOfDate = $request->input('as_of_date', date('Y-m-d'));
        $comparison = $request->input('comparison', 'none');

        $data = $this->balanceSheetService->getBalanceSheetData($asOfDate, $comparison);
        $cur = $data['current'];

        $viewName = view()->exists('accounting.reports.balance-sheet.index')
            ? 'accounting.reports.balance-sheet.index'
            : 'financial-reporting.balance-sheet';

        return view($viewName, [
            'asOfDate'         => $data['as_of_date'],
            'comparison'       => $data['comparison_type'],
            'comparisonDate'   => $data['comparison_date'],
            'assets'           => collect($cur['assets']),
            'liabilities'      => collect($cur['liabilities']),
            'equity'           => collect($cur['equity']),
            'totalAssets'      => (float) $cur['total_assets'],
            'totalLiabilities' => (float) $cur['total_liabilities'],
            'totalEquity'      => (float) $cur['total_equity'],
            'totalEquityBase'  => (float) $cur['total_equity_base'],
            'netSurplus'       => (float) $cur['current_year_surplus'],
            'totalLiabAndEq'   => (float) $cur['total_liab_and_equity'],
            'currentRatio'     => $cur['current_ratio'],
            'isBalanced'       => $cur['is_balanced'],
            'variance'         => (float) $cur['variance'],
            'comparisonData'   => $data['comparison'],
        ]);
    }

    public function export(Request $request): StreamedResponse
    {
        $asOfDate = $request->input('as_of_date', date('Y-m-d'));
        $data = $this->balanceSheetService->getBalanceSheetData($asOfDate);
        $cur = $data['current'];
        $filename = "balance-sheet-{$asOfDate}.csv";

        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma'              => 'no-cache',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Expires'             => '0',
        ];

        return response()->stream(function () use ($asOfDate, $cur): void {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, ['HOSPITAL STATEMENT OF FINANCIAL POSITION (BALANCE SHEET)']);
            fputcsv($handle, ['As of Date', $asOfDate]);
            fputcsv($handle, ['Total Assets (PHP)', number_format((float) $cur['total_assets'], 2)]);
            fputcsv($handle, ['Total Liabilities (PHP)', number_format((float) $cur['total_liabilities'], 2)]);
            fputcsv($handle, ['Total Equity (PHP)', number_format((float) $cur['total_equity'], 2)]);
            fputcsv($handle, ['Balanced Equation Status', $cur['is_balanced'] ? 'BALANCED' : 'UNBALANCED']);
            fputcsv($handle, []);

            fputcsv($handle, ['CATEGORY / ACCOUNT CODE', 'ACCOUNT NAME', 'BALANCE (PHP)']);

            fputcsv($handle, ['--- ASSETS ---']);
            foreach ($cur['assets'] as $a) {
                fputcsv($handle, [$a['code'], $a['name'], number_format((float) $a['balance'], 2)]);
            }
            fputcsv($handle, ['TOTAL ASSETS', '', number_format((float) $cur['total_assets'], 2)]);
            fputcsv($handle, []);

            fputcsv($handle, ['--- LIABILITIES ---']);
            foreach ($cur['liabilities'] as $l) {
                fputcsv($handle, [$l['code'], $l['name'], number_format((float) $l['balance'], 2)]);
            }
            fputcsv($handle, ['TOTAL LIABILITIES', '', number_format((float) $cur['total_liabilities'], 2)]);
            fputcsv($handle, []);

            fputcsv($handle, ['--- EQUITY ---']);
            foreach ($cur['equity'] as $e) {
                fputcsv($handle, [$e['code'], $e['name'], number_format((float) $e['balance'], 2)]);
            }
            fputcsv($handle, ['Current Year Operating Surplus', '', number_format((float) $cur['current_year_surplus'], 2)]);
            fputcsv($handle, ['TOTAL EQUITY', '', number_format((float) $cur['total_equity'], 2)]);
            fputcsv($handle, ['TOTAL LIABILITIES & EQUITY', '', number_format((float) $cur['total_liab_and_equity'], 2)]);

            fclose($handle);
        }, 200, $headers);
    }
}
