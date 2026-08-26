<?php

declare(strict_types=1);

namespace App\Http\Controllers\FinancialReporting;

use App\Http\Controllers\Controller;
use App\Services\Accounting\Reporting\ProfitAndLossService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class ProfitAndLossController extends Controller
{
    public function __construct(
        private readonly ProfitAndLossService $pnlService
    ) {}

    public function index(Request $request): View
    {
        $dateFrom = $request->input('date_from', date('Y-01-01'));
        $dateTo = $request->input('date_to', date('Y-m-d'));
        $department = $request->input('department');

        $data = $this->pnlService->getProfitAndLossData($dateFrom, $dateTo, $department);

        $viewName = view()->exists('accounting.reports.pnl.index')
            ? 'accounting.reports.pnl.index'
            : 'financial-reporting.profit-loss';

        return view($viewName, [
            'dateFrom'       => $data['date_from'],
            'dateTo'         => $data['date_to'],
            'department'     => $data['department'],
            'revenues'       => collect($data['revenues']),
            'expenses'       => collect($data['expenses']),
            'grossRevenue'   => (float) $data['gross_revenue'],
            'salesDiscounts' => (float) $data['sales_discounts'],
            'totalRevenue'   => (float) $data['total_revenue'],
            'totalExpense'   => (float) $data['total_expense'],
            'netIncome'      => (float) $data['net_income'],
            'profitMargin'   => $data['profit_margin'],
        ]);
    }

    public function export(Request $request): StreamedResponse
    {
        $dateFrom = $request->input('date_from', date('Y-01-01'));
        $dateTo = $request->input('date_to', date('Y-m-d'));
        $data = $this->pnlService->getProfitAndLossData($dateFrom, $dateTo);
        $filename = "profit-and-loss-{$dateFrom}-to-{$dateTo}.csv";

        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma'              => 'no-cache',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Expires'             => '0',
        ];

        return response()->stream(function () use ($dateFrom, $dateTo, $data): void {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, ['HOSPITAL STATEMENT OF PROFIT & LOSS / COMPREHENSIVE INCOME']);
            fputcsv($handle, ['Period Covered', "{$dateFrom} to {$dateTo}"]);
            fputcsv($handle, ['Gross Clinical Revenues (PHP)', number_format((float) $data['gross_revenue'], 2)]);
            fputcsv($handle, ['Sales Discounts & Allowances (PHP)', number_format((float) $data['sales_discounts'], 2)]);
            fputcsv($handle, ['Net Operating Revenues (PHP)', number_format((float) $data['net_revenue'], 2)]);
            fputcsv($handle, ['Total Operating Expenses (PHP)', number_format((float) $data['total_expenses'], 2)]);
            fputcsv($handle, ['Net Operating Surplus / (Loss) (PHP)', number_format((float) $data['net_income'], 2)]);
            fputcsv($handle, ['Operating Margin (%)', $data['profit_margin'] . '%']);
            fputcsv($handle, []);

            fputcsv($handle, ['CODE', 'ACCOUNT DESCRIPTION', 'DEPARTMENT', 'AMOUNT (PHP)']);

            fputcsv($handle, ['--- REVENUES ---']);
            foreach ($data['revenues'] as $r) {
                fputcsv($handle, [$r['code'], $r['name'], $r['department'], number_format((float) $r['balance'], 2)]);
            }
            fputcsv($handle, ['NET OPERATING REVENUES', '', '', number_format((float) $data['net_revenue'], 2)]);
            fputcsv($handle, []);

            fputcsv($handle, ['--- OPERATING EXPENSES ---']);
            foreach ($data['expenses'] as $e) {
                fputcsv($handle, [$e['code'], $e['name'], $e['department'], number_format((float) $e['balance'], 2)]);
            }
            fputcsv($handle, ['TOTAL OPERATING EXPENSES', '', '', number_format((float) $data['total_expenses'], 2)]);
            fputcsv($handle, ['NET OPERATING SURPLUS / (DEFICIT)', '', '', number_format((float) $data['net_income'], 2)]);

            fclose($handle);
        }, 200, $headers);
    }
}
