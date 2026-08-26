<?php

declare(strict_types=1);

namespace App\Http\Controllers\AccountsPayable;

use App\DTOs\Accounting\PurchaseBillCreateData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Accounting\StorePurchaseBillRequest;
use App\Models\DoctorProfile;
use App\Models\PurchaseBill;
use App\Models\Vendor;
use App\Services\Accounting\AccountsPayableService;
use App\Services\Accounting\ThreeWayMatchingService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

final class PurchaseBillController extends Controller
{
    public function __construct(
        private readonly AccountsPayableService $apService,
        private readonly ThreeWayMatchingService $matchingService,
    ) {}

    public function index(Request $request): View
    {
        $status = $request->query('status');
        $matchStatus = $request->query('match_status');
        $search = $request->query('search');

        $query = PurchaseBill::with(['vendor', 'items', 'threeWayMatch', 'birCertificate'])
            ->latest('bill_date');

        if ($status) {
            $query->where('status', $status);
        }

        if ($matchStatus) {
            $query->whereHas('threeWayMatch', fn ($tq) => $tq->where('match_status', $matchStatus));
        }

        if ($search) {
            $query->where(function ($q) use ($search): void {
                $q->where('bill_number', 'LIKE', "%{$search}%")
                  ->orWhereHas('vendor', fn ($vq) => $vq->where('name', 'LIKE', "%{$search}%"))
                  ->orWhereHas('threeWayMatch', fn ($tq) => $tq->where('vendor_invoice_number', 'LIKE', "%{$search}%")
                      ->orWhere('po_number', 'LIKE', "%{$search}%")
                      ->orWhere('grn_number', 'LIKE', "%{$search}%"));
            });
        }

        $bills = $query->paginate(15)->withQueryString();

        $totalUnpaid = PurchaseBill::whereIn('status', ['UNPAID', 'PARTIAL', 'OVERDUE'])->sum('total_amount');
        $totalPaid = PurchaseBill::where('status', 'PAID')->sum('paid_amount');
        $pendingCount = PurchaseBill::where('status', 'UNPAID')->orWhereNull('status')->count();
        $vendors = Vendor::where('status', 'Active')->orderBy('name')->get();
        $doctors = DoctorProfile::where('status', 'Active')->orderBy('full_name')->get();

        return view('accounts-payable.purchase-bills', compact(
            'bills',
            'totalUnpaid',
            'totalPaid',
            'pendingCount',
            'vendors',
            'doctors',
            'status',
            'matchStatus',
            'search',
        ));
    }

    public function store(StorePurchaseBillRequest $request): RedirectResponse
    {
        $dto = PurchaseBillCreateData::fromArray($request->validated());
        $bill = $this->apService->ingestVendorBillAndPostAP($dto);

        return redirect()->back()->with('success', "Purchase Bill [{$bill->bill_number}] ingested successfully with 3-Way Match evaluation and BIR 2307 certificate generation.");
    }

    public function approve(int|string $id): RedirectResponse
    {
        $bill = $this->matchingService->approveMatch((int) $id, auth()->id() ?? 1);

        return redirect()->back()->with('success', "Purchase Bill [{$bill->bill_number}] 3-Way Match approved and authorized for disbursement.");
    }
}
