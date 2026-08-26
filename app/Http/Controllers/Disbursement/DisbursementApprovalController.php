<?php

declare(strict_types=1);

namespace App\Http\Controllers\Disbursement;

use App\DTOs\Accounting\DisbursementReleaseData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Accounting\ReleaseDisbursementRequest;
use App\Models\DisbursementVoucher;
use App\Services\Accounting\DisbursementExecutionService;
use DomainException;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

final class DisbursementApprovalController extends Controller
{
    public function __construct(
        private readonly DisbursementExecutionService $disbursementService,
    ) {}

    public function index(Request $request): View
    {
        $status = $request->query('status');
        $search = $request->query('search');

        $query = DisbursementVoucher::with(['bankAccount', 'purchaseBill.vendor', 'payrollRun', 'preparer', 'approver', 'auditor', 'checkRegister'])
            ->latest('voucher_date');

        if ($status) {
            $query->where('status', $status);
        }

        if ($search) {
            $query->where(function ($q) use ($search): void {
                $q->where('voucher_number', 'LIKE', "%{$search}%")
                  ->orWhere('payee_name', 'LIKE', "%{$search}%")
                  ->orWhere('description', 'LIKE', "%{$search}%")
                  ->orWhere('check_or_eft_ref', 'LIKE', "%{$search}%");
            });
        }

        $vouchers = $query->paginate(15)->withQueryString();

        $totalPrepared = DisbursementVoucher::whereIn('status', ['DRAFT', 'PREPARED'])->sum('net_disbursed_amount');
        $totalAudited = DisbursementVoucher::where('status', 'AUDITED')->sum('net_disbursed_amount');
        $totalApproved = DisbursementVoucher::where('status', 'APPROVED')->sum('net_disbursed_amount');
        $totalReleased = DisbursementVoucher::where('status', 'RELEASED')->sum('net_disbursed_amount');

        return view('disbursement.disbursement-approvals', compact(
            'vouchers',
            'totalPrepared',
            'totalAudited',
            'totalApproved',
            'totalReleased',
            'status',
            'search',
        ));
    }

    public function approve(int|string $id): RedirectResponse
    {
        try {
            $userId = auth()->id() ?? 1;
            $voucher = $this->disbursementService->approveDisbursementVoucher((int) $id, $userId);

            return redirect()->back()->with('success', "Disbursement Voucher [{$voucher->voucher_number}] approved by Finance Management.");
        } catch (DomainException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function release(ReleaseDisbursementRequest $request, int|string $id): RedirectResponse
    {
        try {
            $validated = $request->validated();
            $dto = new DisbursementReleaseData(
                checkNumber: $validated['check_number'] ?? null,
                checkDate: $validated['check_date'] ?? date('Y-m-d'),
                eftReference: $validated['eft_reference'] ?? null,
                notes: $validated['notes'] ?? null,
            );

            $userId = auth()->id() ?? 1;
            $voucher = $this->disbursementService->releaseDisbursement((int) $id, $dto, $userId);

            return redirect()->back()->with('success', "Disbursement Voucher [{$voucher->voucher_number}] successfully released and settled with General Ledger recognition.");
        } catch (DomainException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }
}
