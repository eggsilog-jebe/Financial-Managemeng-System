<?php

declare(strict_types=1);

namespace App\Http\Controllers\AccountsReceivable;

use App\Http\Controllers\Controller;
use App\Services\Accounting\ReceivableAgingService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class ReceivableAgingController extends Controller
{
    public function __construct(
        private readonly ReceivableAgingService $agingService,
    ) {}

    public function index(Request $request): View
    {
        $asOfDate = $request->query('as_of_date', date('Y-m-d'));
        $payorType = $request->query('payor_type', 'ALL');
        $search = $request->query('search');
        $admissionType = $request->query('admission_type', 'ALL');

        $report = $this->agingService->getReceivableAgingReport($asOfDate, $payorType, $search, $admissionType);

        return view('accounts-receivable.receivable-aging', [
            'asOfDate'      => $report['as_of_date'],
            'debtors'       => $report['debtors'],
            'totalCurrent'  => $report['total_current'],
            'total31To60'   => $report['total_31_60'],
            'total61To90'   => $report['total_61_90'],
            'total91To120'  => $report['total_91_120'],
            'total120Plus'  => $report['total_120_plus'],
            'grandTotalAR'  => $report['grand_total'],
            'totalDebtors'  => $report['total_debtors'],
            'payorType'     => $payorType,
            'search'        => $search,
            'admissionType' => $admissionType,
        ]);
    }

    public function export(Request $request): StreamedResponse
    {
        $asOfDate = $request->query('as_of_date', date('Y-m-d'));
        return $this->agingService->exportAgingCsv($asOfDate);
    }
}
