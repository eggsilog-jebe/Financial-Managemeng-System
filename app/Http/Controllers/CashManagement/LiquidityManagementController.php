<?php

declare(strict_types=1);

namespace App\Http\Controllers\CashManagement;

use App\Http\Controllers\Controller;
use App\Services\Accounting\LiquidityService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class LiquidityManagementController extends Controller
{
    public function __construct(
        private readonly LiquidityService $liquidityService
    ) {}

    public function index(Request $request): View
    {
        $data = $this->liquidityService->getLiquidityMetrics();

        $viewName = view()->exists('accounting.cash-management.liquidity.index')
            ? 'accounting.cash-management.liquidity.index'
            : 'cash.liquidity-management';

        return view($viewName, array_merge($data, ['totalCash' => $data['total_cash']]));
    }

    public function export(Request $request): StreamedResponse
    {
        $data = $this->liquidityService->getLiquidityMetrics();
        $filename = "treasury-liquidity-report-" . date('Ymd-His') . ".csv";

        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma'              => 'no-cache',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Expires'             => '0',
        ];

        return response()->stream(function () use ($data): void {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, ['EXECUTIVE TREASURY & LIQUIDITY MANAGEMENT REPORT']);
            fputcsv($handle, ['Generated At', date('Y-m-d H:i:s')]);
            fputcsv($handle, ['Total Liquid Cash Pool', number_format((float) $data['total_cash'], 2)]);
            fputcsv($handle, ['Estimated Daily Operating Burn', number_format((float) $data['daily_burn_rate'], 2)]);
            fputcsv($handle, ['Days Cash on Hand (DCOH)', $data['days_cash_on_hand'] . ' Days']);
            fputcsv($handle, ['Liquidity Rating', $data['liquidity_status']['rating'] . ' - ' . $data['liquidity_status']['desc']]);
            fputcsv($handle, ['Accounts Below Safety Floor', $data['below_minimum_count']]);
            fputcsv($handle, []);

            fputcsv($handle, ['Bank Name', 'Account Name', 'Account Number', 'GL Code', 'Current Balance (PHP)', 'Minimum Floor (PHP)', 'Concentration %', 'Safety Status', 'Account Status']);
            foreach ($data['concentration'] as $c) {
                fputcsv($handle, [
                    $c['bank_name'],
                    $c['name'],
                    $c['account_number'],
                    $c['gl_code'],
                    number_format((float) $c['balance'], 2),
                    number_format((float) $c['minimum_balance'], 2),
                    $c['percentage'] . '%',
                    $c['is_below_min'] ? 'BELOW MINIMUM' : 'OPTIMAL',
                    $c['status'],
                ]);
            }

            fclose($handle);
        }, 200, $headers);
    }
}
