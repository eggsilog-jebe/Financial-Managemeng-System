<?php

declare(strict_types=1);

namespace App\Http\Controllers\FinancialReporting;

use App\Http\Controllers\Controller;
use App\Services\Accounting\Reporting\FinancialKpiService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class FinancialKpiDashboardController extends Controller
{
    public function __construct(
        private readonly FinancialKpiService $kpiService
    ) {}

    public function index(Request $request): View
    {
        $metrics = $this->kpiService->getKpiMetrics();

        $viewName = view()->exists('accounting.reports.kpi.index')
            ? 'accounting.reports.kpi.index'
            : 'financial-reporting.financial-kpi-dashboard';

        return view($viewName, $metrics);
    }

    public function export(Request $request): StreamedResponse
    {
        $metrics = $this->kpiService->getKpiMetrics();
        $filename = "financial-kpi-summary-" . date('Ymd-His') . ".csv";

        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma'              => 'no-cache',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Expires'             => '0',
        ];

        return response()->stream(function () use ($metrics): void {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, ['EXECUTIVE HEALTHCARE FINANCIAL KPI DECK']);
            fputcsv($handle, ['Generated At', date('Y-m-d H:i:s')]);
            fputcsv($handle, []);

            fputcsv($handle, ['KEY METRIC', 'CURRENT VALUE', 'BENCHMARK / TARGET', 'STATUS']);
            fputcsv($handle, ['Operating Profit Margin', $metrics['operating_margin'] . '%', '> 15.0%', $metrics['operating_margin'] >= 15 ? 'OPTIMAL' : 'MONITOR']);
            fputcsv($handle, ['Days Sales Outstanding (DSO)', $metrics['dso'] . ' Days', '< 45 Days', $metrics['dso'] <= 45 ? 'HEALTHY' : 'ELEVATED']);
            fputcsv($handle, ['Days Payable Outstanding (DPO)', $metrics['dpo'] . ' Days', '30 - 60 Days', 'CONTROLLED']);
            fputcsv($handle, ['Current Ratio', $metrics['current_ratio'] . 'x', '> 1.50x', $metrics['current_ratio'] >= 1.5 ? 'HEALTHY' : 'CAUTION']);
            fputcsv($handle, ['Quick Ratio (Acid Test)', $metrics['quick_ratio'] . 'x', '> 1.00x', $metrics['quick_ratio'] >= 1.0 ? 'HEALTHY' : 'CAUTION']);
            fputcsv($handle, ['Days Cash on Hand (DCOH)', $metrics['days_cash_on_hand'] . ' Days', '> 30 Days', $metrics['days_cash_on_hand'] >= 30 ? 'HEALTHY' : 'LOW']);
            fputcsv($handle, []);

            fputcsv($handle, ['MONTHLY 12-MONTH TRAJECTORY']);
            fputcsv($handle, ['Month', 'Revenue (PHP)', 'Expense (PHP)', 'Operating Surplus (PHP)']);
            foreach ($metrics['trajectory'] as $t) {
                fputcsv($handle, [
                    $t['label'],
                    number_format((float) $t['revenue'], 2),
                    number_format((float) $t['expense'], 2),
                    number_format((float) $t['surplus'], 2),
                ]);
            }

            fclose($handle);
        }, 200, $headers);
    }
}
