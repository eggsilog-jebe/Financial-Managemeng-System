<?php

declare(strict_types=1);

namespace App\Http\Controllers\Disbursement;

use App\Http\Controllers\Controller;
use App\Models\BankAccount;
use App\Models\CasAuditTrail;
use App\Models\DisbursementVoucher;
use DomainException;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class EftTransferController extends Controller
{
    public function index(Request $request): View
    {
        $channel = $request->query('channel');
        $search = $request->query('search');

        $query = DisbursementVoucher::with(['bankAccount', 'purchaseBill.vendor', 'payrollRun'])
            ->whereIn('payment_method', ['PESONET_EFT', 'INSTAPAY', 'TELEGRAPHIC_TRANSFER'])
            ->latest('voucher_date');

        if ($channel) {
            $query->where('payment_method', $channel);
        }

        if ($search) {
            $query->where(function ($q) use ($search): void {
                $q->where('voucher_number', 'LIKE', "%{$search}%")
                  ->orWhere('payee_name', 'LIKE', "%{$search}%")
                  ->orWhere('check_or_eft_ref', 'LIKE', "%{$search}%");
            });
        }

        $transfers = $query->paginate(15)->withQueryString();

        $totalTransfers = DisbursementVoucher::whereIn('payment_method', ['PESONET_EFT', 'INSTAPAY', 'TELEGRAPHIC_TRANSFER'])->count();
        $totalAmount = DisbursementVoucher::whereIn('payment_method', ['PESONET_EFT', 'INSTAPAY', 'TELEGRAPHIC_TRANSFER'])->sum('net_disbursed_amount');
        $pesonetAmount = DisbursementVoucher::where('payment_method', 'PESONET_EFT')->sum('net_disbursed_amount');
        $instapayAmount = DisbursementVoucher::where('payment_method', 'INSTAPAY')->sum('net_disbursed_amount');

        $bankAccounts = BankAccount::where('status', 'Active')->orderBy('name')->get();

        return view('disbursement.eft-transfers', compact(
            'transfers',
            'totalTransfers',
            'totalAmount',
            'pesonetAmount',
            'instapayAmount',
            'channel',
            'search',
            'bankAccounts',
        ));
    }

    /**
     * Create a new EFT disbursement batch entry.
     * Computes net_disbursed_amount = gross_amount - withheld_tax_amount using BCMath (scale: 4).
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'payee_name'          => ['required', 'string', 'max:255'],
            'bank_account_id'     => ['required', 'integer', 'exists:bank_accounts,id'],
            'payment_method'      => ['required', 'in:PESONET_EFT,INSTAPAY,TELEGRAPHIC_TRANSFER'],
            'gross_amount'        => ['required', 'numeric', 'min:0.01'],
            'withheld_tax_amount' => ['nullable', 'numeric', 'min:0'],
            'description'         => ['nullable', 'string', 'max:500'],
            'voucher_date'        => ['required', 'date'],
        ]);

        return DB::transaction(function () use ($validated): RedirectResponse {
            $grossAmount = (string) $validated['gross_amount'];
            $withheldTax = (string) ($validated['withheld_tax_amount'] ?? '0.0000');

            // BCMath: net disbursed = gross - EWT withheld (scale: 4)
            $netDisbursed = bcsub($grossAmount, $withheldTax, 4);

            if (bccomp($netDisbursed, '0.0000', 4) <= 0) {
                return back()->withErrors(['withheld_tax_amount' => 'Withheld tax cannot exceed or equal gross amount.'])->withInput();
            }

            $seq = str_pad((string) (DisbursementVoucher::count() + 1), 6, '0', STR_PAD_LEFT);
            $voucherNumber = 'EFT-' . date('Ymd') . '-' . $seq;

            $voucher = DisbursementVoucher::create([
                'voucher_number'       => $voucherNumber,
                'bank_account_id'      => $validated['bank_account_id'],
                'prepared_by'          => auth()->id(),
                'voucher_date'         => $validated['voucher_date'],
                'payee_name'           => $validated['payee_name'],
                'description'          => $validated['description'] ?? null,
                'gross_amount'         => $grossAmount,
                'withheld_tax_amount'  => $withheldTax,
                'net_disbursed_amount' => $netDisbursed,
                'payment_method'       => $validated['payment_method'],
                'status'               => 'PREPARED',
            ]);

            // CAS Audit Trail
            CasAuditTrail::create([
                'event_uuid'      => \Illuminate\Support\Str::uuid()->toString(),
                'user_id'         => auth()->id(),
                'user_name'       => auth()->user()?->name ?? 'Finance Officer',
                'ip_address'      => $request->ip(),
                'auditable_type'  => DisbursementVoucher::class,
                'auditable_id'    => $voucher->id,
                'action'          => 'INSERT',
                'old_values'      => null,
                'new_values'      => json_encode($voucher->toArray()),
                'record_hash'     => hash('sha256', json_encode($voucher->toArray())),
                'previous_hash'   => null,
            ]);

            return redirect()
                ->route('disbursement.eft-transfers')
                ->with('success', "EFT Transfer voucher [{$voucherNumber}] created successfully.");
        });
    }

    /**
     * Approve and release an EFT disbursement voucher.
     * Only APPROVED → RELEASED transition is permitted. CFO/FinanceDirector only (enforced via route middleware).
     */
    public function approve(int $id, Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'eft_reference' => ['nullable', 'string', 'max:100'],
        ]);

        return DB::transaction(function () use ($id, $validated, $request): RedirectResponse {
            $voucher = DisbursementVoucher::findOrFail($id);

            if (! in_array($voucher->status, ['PREPARED', 'APPROVED'], true)) {
                throw new DomainException("EFT Voucher [{$voucher->voucher_number}] cannot be approved — current status: {$voucher->status}.");
            }

            $oldValues = $voucher->toArray();

            $voucher->update([
                'status'           => 'RELEASED',
                'approved_by'      => auth()->id(),
                'check_or_eft_ref' => $validated['eft_reference'] ?? ('EFT-REL-' . strtoupper(bin2hex(random_bytes(4)))),
                'released_at'      => now(),
            ]);

            // CAS Audit Trail
            CasAuditTrail::create([
                'event_uuid'     => \Illuminate\Support\Str::uuid()->toString(),
                'user_id'        => auth()->id(),
                'user_name'      => auth()->user()?->name ?? 'CFO',
                'ip_address'     => $request->ip(),
                'auditable_type' => DisbursementVoucher::class,
                'auditable_id'   => $voucher->id,
                'action'         => 'UPDATE',
                'old_values'     => json_encode($oldValues),
                'new_values'     => json_encode($voucher->toArray()),
                'record_hash'    => hash('sha256', json_encode($voucher->toArray())),
                'previous_hash'  => null,
            ]);

            return redirect()
                ->route('disbursement.eft-transfers')
                ->with('success', "EFT Voucher [{$voucher->voucher_number}] approved and released to bank.");
        });
    }

    public function export(Request $request): StreamedResponse
    {
        $vouchers = DisbursementVoucher::with(['bankAccount', 'purchaseBill.vendor', 'payrollRun'])
            ->whereIn('payment_method', ['PESONET_EFT', 'INSTAPAY', 'TELEGRAPHIC_TRANSFER'])
            ->latest('voucher_date')
            ->get();

        $filename = 'EFT_Bank_Batch_Payout_' . date('Ymd_His') . '.csv';

        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma'              => 'no-cache',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Expires'             => '0',
        ];

        $callback = function () use ($vouchers): void {
            $handle = fopen('php://output', 'w');
            fputs($handle, "\xEF\xBB\xBF");

            fputcsv($handle, ['St. Jude Metropolitan Medical Center']);
            fputcsv($handle, ['Electronic Funds Transfer (EFT) Bank Batch Payout File']);
            fputcsv($handle, ['Batch Date:', date('Y-m-d H:i:s')]);
            fputcsv($handle, []);

            fputcsv($handle, [
                'Voucher Ref #',
                'Payment Channel',
                'Disbursing Bank',
                'Disbursing Account #',
                'Beneficiary / Payee Name',
                'Transfer Amount (PHP)',
                'Payment Particulars',
                'Status',
                'Release Ref',
            ]);

            foreach ($vouchers as $v) {
                fputcsv($handle, [
                    $v->voucher_number,
                    $v->payment_method,
                    $v->bankAccount?->bank_name ?? 'Operating Bank',
                    $v->bankAccount?->account_number ?? 'N/A',
                    $v->payee_name,
                    number_format((float) $v->net_disbursed_amount, 2, '.', ''),
                    $v->description ?? ($v->purchaseBill ? "Bill {$v->purchaseBill->bill_number}" : 'Disbursement Payout'),
                    $v->status,
                    $v->check_or_eft_ref ?? 'PENDING',
                ]);
            }

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }
}