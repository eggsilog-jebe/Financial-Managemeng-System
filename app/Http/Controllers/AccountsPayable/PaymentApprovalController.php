<?php

declare(strict_types=1);

namespace App\Http\Controllers\AccountsPayable;

use App\DTOs\Accounting\DisbursementReleaseData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Accounting\ReleaseDisbursementRequest;
use App\Models\BankAccount;
use App\Models\DisbursementVoucher;
use App\Services\Accounting\DisbursementExecutionService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

final class PaymentApprovalController extends Controller
{
    public function __construct(
        private readonly DisbursementExecutionService $disbursementService,
    ) {}

    public function index(Request $request): View
    {
        $status = $request->query('status');
        $search = $request->query('search');

        $query = DisbursementVoucher::with(['purchaseBill.vendor', 'bankAccount', 'checkRegister', 'approver'])
            ->latest('voucher_date');

        if ($status) {
            $query->where('status', $status);
        }

        if ($search) {
            $query->where(function ($q) use ($search): void {
                $q->where('voucher_number', 'LIKE', "%{$search}%")
                  ->orWhere('payee_name', 'LIKE', "%{$search}%")
                  ->orWhere('check_or_eft_ref', 'LIKE', "%{$search}%")
                  ->orWhereHas('purchaseBill', fn ($bq) => $bq->where('bill_number', 'LIKE', "%{$search}%"));
            });
        }

        $vouchers = $query->paginate(15)->withQueryString();

        $totalPrepared = DisbursementVoucher::where('status', 'DRAFT')->sum('net_disbursed_amount');
        $totalApproved = DisbursementVoucher::where('status', 'APPROVED')->sum('net_disbursed_amount');
        $totalReleased = DisbursementVoucher::where('status', 'RELEASED')->sum('net_disbursed_amount');
        $bankAccounts = BankAccount::where('status', 'Active')->get();

        return view('accounts-payable.ap-payment-approvals', compact(
            'vouchers',
            'totalPrepared',
            'totalApproved',
            'totalReleased',
            'bankAccounts',
            'status',
            'search',
        ));
    }

    public function approve(int|string $id): RedirectResponse
    {
        $voucher = $this->disbursementService->approveDisbursementVoucher((int) $id, auth()->id() ?? 1);

        return redirect()->back()->with('success', "Disbursement Voucher [{$voucher->voucher_number}] approved. Authorized for payment release.");
    }

    public function release(ReleaseDisbursementRequest $request, int|string $id): RedirectResponse
    {
        $dto = DisbursementReleaseData::fromArray($request->validated());
        $voucher = $this->disbursementService->releaseDisbursement((int) $id, $dto, auth()->id() ?? 1);

        $refInfo = $voucher->payment_method === 'CHECK'
            ? "Check #{$voucher->check_or_eft_ref}"
            : "EFT Ref: {$voucher->check_or_eft_ref}";

        return redirect()->back()->with('success', "Payment released successfully for Voucher [{$voucher->voucher_number}] ({$refInfo}). General Ledger updated.");
    }
}
