<?php

declare(strict_types=1);

namespace App\Http\Controllers\FinancialReporting;

use App\Http\Controllers\Controller;
use App\Services\Accounting\Reporting\StatementOfChangesInEquityService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class StatementOfChangesInEquityController extends Controller
{
    public function __construct(
        private readonly StatementOfChangesInEquityService $equityService
    ) {}

    public function index(Request $request): View
    {
        $dateFrom = $request->input('date_from', date('Y-01-01'));
        $dateTo = $request->input('date_to', date('Y-m-d'));

        $data = $this->equityService->getChangesInEquityData($dateFrom, $dateTo);

        return view('accounting.reports.equity.index', $data);
    }

    public function export(Request $request): StreamedResponse
    {
        $dateFrom = $request->input('date_from', date('Y-01-01'));
        $dateTo = $request->input('date_to', date('Y-m-d'));
        $data = $this->equityService->getChangesInEquityData($dateFrom, $dateTo);
        $filename = "statement-of-changes-in-equity-{$dateFrom}-to-{$dateTo}.csv";

        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma'              => 'no-cache',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Expires'             => '0',
        ];

        return response()->stream(function () use ($dateFrom, $dateTo, $data): void {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, ['HOSPITAL STATEMENT OF CHANGES IN EQUITY (PFRS / IAS 1)']);
            fputcsv($handle, ['Period Covered', "{$dateFrom} to {$dateTo}"]);
            fputcsv($handle, []);

            fputcsv($handle, ['ACCOUNT CODE', 'EQUITY COMPONENT', 'OPENING BALANCE', 'CAPITAL ADDITIONS', 'DISTRIBUTIONS', 'CLOSING BALANCE']);

            foreach ($data['accounts'] as $acc) {
                fputcsv($handle, [
                    $acc['code'],
                    $acc['name'],
                    number_format((float) $acc['opening_balance'], 2),
                    number_format((float) $acc['additions'], 2),
                    number_format((float) $acc['deductions'], 2),
                    number_format((float) $acc['closing_balance'], 2),
                ]);
            }

            fputcsv($handle, []);
            fputcsv($handle, ['Opening Total Equity (PHP)', number_format((float) $data['opening_equity'], 2)]);
            fputcsv($handle, ['Total Capital Additions (PHP)', number_format((float) $data['capital_additions'], 2)]);
            fputcsv($handle, ['Total Distributions / Drawdowns (PHP)', '-' . number_format((float) $data['distributions'], 2)]);
            fputcsv($handle, ['Current Period Net Operating Surplus (PHP)', number_format((float) $data['current_period_surplus'], 2)]);
            fputcsv($handle, ['Total Ending Equity (PHP)', number_format((float) $data['total_closing_equity'], 2)]);

            fclose($handle);
        }, 200, $headers);
    }
}
