<?php

declare(strict_types=1);

namespace App\Http\Controllers\AccountsPayable;

use App\DTOs\Accounting\DisbursementVoucherData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Accounting\PrepareDisbursementVoucherRequest;
use App\Models\BankAccount;
use App\Models\DisbursementVoucher;
use App\Models\PurchaseBill;
use App\Services\Accounting\DisbursementExecutionService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

final class VendorInvoiceController extends Controller
{
    public function __construct(
        private readonly DisbursementExecutionService $disbursementService,
    ) {}

    public function index(Request $request): View
    {
        $status = $request->query('status');
        $search = $request->query('search');

        $query = PurchaseBill::with(['vendor', 'items', 'threeWayMatch', 'birCertificate', 'disbursementVouchers.bankAccount'])
            ->latest('bill_date');

        if ($status) {
            $query->where('status', $status);
        }

        if ($search) {
            $query->where(function ($q) use ($search): void {
                $q->where('bill_number', 'LIKE', "%{$search}%")
                  ->orWhereHas('vendor', fn ($vq) => $vq->where('name', 'LIKE', "%{$search}%"))
                  ->orWhereHas('threeWayMatch', fn ($tq) => $tq->where('vendor_invoice_number', 'LIKE', "%{$search}%"));
            });
        }

        $invoices = $query->paginate(15)->withQueryString();

        $totalBilled = PurchaseBill::sum('total_amount');
        $totalPending = PurchaseBill::whereIn('status', ['UNPAID', 'PARTIAL', 'OVERDUE', 'APPROVED'])->get()->sum(fn ($b) => $b->balance_due);
        $totalVouchers = DisbursementVoucher::count();
        $bankAccounts = BankAccount::where('status', 'Active')->get();

        return view('accounts-payable.invoices-vouchers', compact(
            'invoices',
            'totalBilled',
            'totalPending',
            'totalVouchers',
            'bankAccounts',
            'status',
            'search',
        ));
    }

    public function prepareVoucher(PrepareDisbursementVoucherRequest $request): RedirectResponse
    {
        $dto = DisbursementVoucherData::fromArray($request->validated());
        $voucher = $this->disbursementService->prepareDisbursementVoucher($dto, auth()->id() ?? 1);

        return redirect()->back()->with('success', "Disbursement Voucher [{$voucher->voucher_number}] prepared for ₱" . number_format((float) $voucher->net_disbursed_amount, 2) . ". Pending Finance Approval.");
    }
}
