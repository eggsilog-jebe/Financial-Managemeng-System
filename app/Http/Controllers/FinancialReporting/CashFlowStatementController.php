<?php

declare(strict_types=1);

namespace App\Http\Controllers\FinancialReporting;

use App\Http\Controllers\Controller;
use App\Services\Accounting\Reporting\CashFlowService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class CashFlowStatementController extends Controller
{
    public function __construct(
        private readonly CashFlowService $cashFlowService
    ) {}

    public function index(Request $request): View
    {
        $dateFrom = $request->input('date_from', date('Y-01-01'));
        $dateTo = $request->input('date_to', date('Y-m-d'));

        $data = $this->cashFlowService->getCashFlowData($dateFrom, $dateTo);

        $viewName = view()->exists('accounting.reports.cash-flow.index')
            ? 'accounting.reports.cash-flow.index'
            : 'financial-reporting.cash-flow-statement';

        return view($viewName, $data);
    }

    public function export(Request $request): StreamedResponse
    {
        $dateFrom = $request->input('date_from', date('Y-01-01'));
        $dateTo = $request->input('date_to', date('Y-m-d'));
        $data = $this->cashFlowService->getCashFlowData($dateFrom, $dateTo);
        $filename = "cash-flow-statement-{$dateFrom}-to-{$dateTo}.csv";

        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma'              => 'no-cache',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Expires'             => '0',
        ];

        return response()->stream(function () use ($dateFrom, $dateTo, $data): void {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, ['HOSPITAL STATEMENT OF CASH FLOWS (PAS 7)']);
            fputcsv($handle, ['Period Covered', "{$dateFrom} to {$dateTo}"]);
            fputcsv($handle, []);

            fputcsv($handle, ['CASH FLOWS FROM OPERATING ACTIVITIES']);
            fputcsv($handle, ['Patient & HMO Cash Collections', number_format((float) $data['operating_receipts'], 2)]);
            fputcsv($handle, ['Cash Paid to Medical Suppliers & Inventory', '-' . number_format((float) $data['supplier_disbursements'], 2)]);
            fputcsv($handle, ['Cash Paid to Hospital Personnel & Staff', '-' . number_format((float) $data['payroll_disbursements'], 2)]);
            fputcsv($handle, ['Direct Clinical Operating Disbursements', '-' . number_format((float) $data['direct_opex_cash'], 2)]);
            fputcsv($handle, ['Net Cash from Operating Activities', number_format((float) $data['net_operating_cash'], 2)]);
            fputcsv($handle, []);

            fputcsv($handle, ['CASH FLOWS FROM INVESTING ACTIVITIES']);
            fputcsv($handle, ['Capital Expenditures & Diagnostic Equipment', '-' . number_format((float) $data['capex_outflows'], 2)]);
            fputcsv($handle, ['Net Cash used in Investing Activities', number_format((float) $data['net_investing_cash'], 2)]);
            fputcsv($handle, []);

            fputcsv($handle, ['CASH FLOWS FROM FINANCING ACTIVITIES']);
            fputcsv($handle, ['Bank Facility & Institutional Reserves', number_format((float) $data['net_financing_cash'], 2)]);
            fputcsv($handle, ['Net Cash from Financing Activities', number_format((float) $data['net_financing_cash'], 2)]);
            fputcsv($handle, []);

            fputcsv($handle, ['NET INCREASE / (DECREASE) IN CASH', number_format((float) $data['net_cash_flow'], 2)]);
            fputcsv($handle, ['Cash and Cash Equivalents at Beginning', number_format((float) $data['opening_cash'], 2)]);
            fputcsv($handle, ['Cash and Cash Equivalents at End of Period', number_format((float) $data['closing_cash'], 2)]);

            fclose($handle);
        }, 200, $headers);
    }
}
