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
use Symfony\Component\HttpFoundation\StreamedResponse;

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

    public function reject(int|string $id): RedirectResponse
    {
        $voucher = DisbursementVoucher::findOrFail((int) $id);
        $voucher->update(['status' => 'CANCELLED']);

        return redirect()->back()->with('success', "Disbursement Voucher [{$voucher->voucher_number}] has been rejected / cancelled.");
    }

    public function bulkApprove(Request $request): RedirectResponse
    {
        $voucherIds = $request->input('voucher_ids', []);
        if (empty($voucherIds)) {
            return redirect()->back()->with('error', 'No disbursement vouchers were selected for authorization.');
        }

        $approvedCount = 0;
        $userId = auth()->id() ?? 1;

        foreach ($voucherIds as $id) {
            $v = DisbursementVoucher::find((int) $id);
            if ($v && ($v->status === 'DRAFT' || $v->status === 'PENDING_APPROVAL')) {
                $this->disbursementService->approveDisbursementVoucher((int) $id, $userId);
                $approvedCount++;
            }
        }

        return redirect()->back()->with('success', "Successfully authorized {$approvedCount} disbursement voucher(s) for payment release.");
    }

    public function exportBankBatch(Request $request): StreamedResponse
    {
        $status = $request->query('status', 'APPROVED');
        $vouchers = DisbursementVoucher::with(['purchaseBill.vendor', 'bankAccount'])
            ->whereIn('status', $status === 'ALL' ? ['DRAFT', 'APPROVED', 'RELEASED'] : (array) $status)
            ->latest('voucher_date')
            ->get();

        $filename = 'Bank_EFT_Disbursement_Batch_' . date('Ymd_His') . '.csv';

        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma'              => 'no-cache',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Expires'             => '0',
        ];

        $callback = function () use ($vouchers): void {
            $handle = fopen('php://output', 'w');
            fputs($handle, "\xEF\xBB\xBF"); // UTF-8 BOM

            fputcsv($handle, ['St. Jude Metropolitan Medical Center']);
            fputcsv($handle, ['Supplier Payment EFT / Bank Batch Register']);
            fputcsv($handle, ['Generated: ' . date('Y-m-d H:i:s')]);
            fputcsv($handle, []);

            fputcsv($handle, [
                'Voucher Number',
                'Payee Name',
                'Payee TIN',
                'Destination Bank',
                'Bank Account Number',
                'Account Name',
                'Payment Method',
                'Gross Amount (PHP)',
                'EWT Withheld (PHP)',
                'Net Disbursed Amount (PHP)',
                'Status',
                'Source Bank Account',
                'Bill Reference',
            ]);

            foreach ($vouchers as $v) {
                $vendor = $v->purchaseBill?->vendor;
                fputcsv($handle, [
                    $v->voucher_number,
                    $v->payee_name,
                    $vendor?->tin ?? '—',
                    $vendor?->bank_name ?? '—',
                    $vendor?->bank_account_number ?? '—',
                    $vendor?->bank_account_name ?? $v->payee_name,
                    $v->payment_method,
                    number_format((float) $v->gross_amount, 2, '.', ''),
                    number_format((float) $v->withheld_tax_amount, 2, '.', ''),
                    number_format((float) $v->net_disbursed_amount, 2, '.', ''),
                    $v->status,
                    $v->bankAccount?->name ?? 'Operating Account',
                    $v->purchaseBill?->bill_number ?? 'Manual Request',
                ]);
            }

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
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
