<?php

declare(strict_types=1);

namespace App\Http\Controllers\CashManagement;

use App\Http\Controllers\Controller;
use App\Services\Accounting\CashFlowForecastService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class CashFlowForecastController extends Controller
{
    public function __construct(
        private readonly CashFlowForecastService $forecastService
    ) {}

    public function index(Request $request): View
    {
        $horizonDays = (int) $request->input('horizon', 30);
        $data = $this->forecastService->getForecastData($horizonDays);

        $viewName = view()->exists('accounting.cash-management.forecasting.index')
            ? 'accounting.cash-management.forecasting.index'
            : 'cash.cash-flow-forecasting';

        return view($viewName, array_merge($data, ['totalCash' => $data['available_cash']]));
    }

    public function export(Request $request): StreamedResponse
    {
        $horizonDays = (int) $request->input('horizon', 30);
        $data = $this->forecastService->getForecastData($horizonDays);
        $filename = "cash-flow-forecast-{$horizonDays}d-" . date('Ymd-His') . ".csv";

        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma'              => 'no-cache',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Expires'             => '0',
        ];

        return response()->stream(function () use ($data): void {
            $handle = fopen('php://output', 'w');

            // Header summary
            fputcsv($handle, ['HOSPITAL CASH FLOW FORECAST REPORT (' . $data['horizon_days'] . ' DAYS)']);
            fputcsv($handle, ['Generated At', date('Y-m-d H:i:s')]);
            fputcsv($handle, ['Current Liquid Cash', number_format((float) $data['available_cash'], 2)]);
            fputcsv($handle, ['Projected Inflows (30-Day)', number_format((float) $data['total_projected_inflows'], 2)]);
            fputcsv($handle, ['Committed Outflows (30-Day)', number_format((float) $data['total_committed_outflows'], 2)]);
            fputcsv($handle, ['Net Operating Position', number_format((float) $data['net_operating_position'], 2)]);
            fputcsv($handle, ['Projected Ending Cash', number_format((float) $data['projected_ending_cash'], 2)]);
            fputcsv($handle, []);

            // Events Table
            fputcsv($handle, ['Type', 'Category', 'Reference #', 'Counterparty / Entity', 'Expected Due Date', 'Amount (PHP)', 'Status']);
            foreach ($data['events'] as $evt) {
                fputcsv($handle, [
                    $evt['type'],
                    $evt['category'],
                    $evt['reference'],
                    $evt['counterparty'],
                    $evt['due_date'],
                    number_format((float) $evt['amount'], 2),
                    $evt['status'],
                ]);
            }

            fclose($handle);
        }, 200, $headers);
    }
}
