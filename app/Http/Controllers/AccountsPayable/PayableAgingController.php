<?php

declare(strict_types=1);

namespace App\Http\Controllers\AccountsPayable;

use App\Http\Controllers\Controller;
use App\Models\Vendor;
use App\Services\Accounting\PayableAgingService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class PayableAgingController extends Controller
{
    public function __construct(
        private readonly PayableAgingService $agingService,
    ) {}

    public function index(Request $request): View
    {
        $asOfDate = $request->query('as_of_date', date('Y-m-d'));
        $vendorId = $request->query('vendor_id') ? (int) $request->query('vendor_id') : null;

        $report = $this->agingService->getPayableAgingReport($asOfDate, $vendorId);
        $vendorsList = Vendor::orderBy('name')->get();

        return view('accounts-payable.payable-aging', [
            'asOfDate'          => $report['as_of_date'],
            'vendors'           => $report['vendors'],
            'totalCurrent'      => $report['total_current'],
            'total1To30'        => $report['total_1_30'],
            'total31To60'       => $report['total_31_60'],
            'total61To90'       => $report['total_61_90'],
            'total90Plus'       => $report['total_90_plus'],
            'grandTotalPayable' => $report['grand_total'],
            'totalVendors'      => $report['total_vendors'],
            'allVendors'        => $vendorsList,
            'selectedVendorId'  => $vendorId,
        ]);
    }

    public function export(Request $request): StreamedResponse
    {
        $asOfDate = $request->query('as_of_date', date('Y-m-d'));
        return $this->agingService->exportAgingCsv($asOfDate);
    }
}
