<?php

declare(strict_types=1);

namespace App\Http\Controllers\Disbursement;

use App\DTOs\Accounting\PaymentRequestData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Accounting\StoreDisbursementRequest;
use App\Models\BankAccount;
use App\Models\DisbursementVoucher;
use App\Models\PayrollRun;
use App\Models\PurchaseBill;
use App\Services\Accounting\DisbursementVoucherService;
use DomainException;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

final class PaymentRequestController extends Controller
{
    public function __construct(
        private readonly DisbursementVoucherService $voucherService,
    ) {}

    public function index(Request $request): View
    {
        $status = $request->query('status');
        $search = $request->query('search');

        $query = DisbursementVoucher::with(['bankAccount', 'purchaseBill', 'payrollRun', 'preparer', 'auditor'])
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

        $totalRequests = DisbursementVoucher::count();
        $pendingApproval = DisbursementVoucher::whereIn('status', ['PREPARED', 'AUDITED'])->sum('net_disbursed_amount');
        $totalReleased = DisbursementVoucher::where('status', 'RELEASED')->sum('net_disbursed_amount');
        $approvedAmount = DisbursementVoucher::where('status', 'APPROVED')->sum('net_disbursed_amount');

        $bankAccounts = BankAccount::where('status', 'Active')->orderBy('bank_name')->get();
        $openBills = PurchaseBill::whereIn('status', ['UNPAID', 'PARTIAL', 'APPROVED'])->latest('bill_date')->get();
        $openPayrolls = PayrollRun::where('status', 'APPROVED')->latest('payout_date')->get();

        return view('disbursement.payment-requests', compact(
            'vouchers',
            'totalRequests',
            'pendingApproval',
            'totalReleased',
            'approvedAmount',
            'bankAccounts',
            'openBills',
            'openPayrolls',
            'status',
            'search',
        ));
    }

    public function store(StoreDisbursementRequest $request): RedirectResponse
    {
        try {
            $dto = PaymentRequestData::fromArray($request->validated(), auth()->id());
            $voucher = $this->voucherService->createPaymentRequest($dto);

            return redirect()->back()->with('success', "Payment Request Voucher [{$voucher->voucher_number}] submitted successfully.");
        } catch (DomainException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function audit(int|string $id): RedirectResponse
    {
        try {
            $voucher = $this->voucherService->auditPaymentRequest((int) $id, auth()->id() ?? 1);

            return redirect()->back()->with('success', "Disbursement Voucher [{$voucher->voucher_number}] audited and cleared for executive approval.");
        } catch (DomainException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function void(Request $request, int|string $id): RedirectResponse
    {
        try {
            $reason = (string) $request->input('reason', 'Cancelled by Management');
            $voucher = $this->voucherService->voidPaymentRequest((int) $id, auth()->id() ?? 1, $reason);

            return redirect()->back()->with('success', "Disbursement Voucher [{$voucher->voucher_number}] has been voided.");
        } catch (DomainException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }
}
