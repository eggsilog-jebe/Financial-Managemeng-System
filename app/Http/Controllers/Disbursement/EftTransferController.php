<?php

declare(strict_types=1);

namespace App\Http\Controllers\Disbursement;

use App\Http\Controllers\Controller;
use App\Models\DisbursementVoucher;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
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

        return view('disbursement.eft-transfers', compact(
            'transfers',
            'totalTransfers',
            'totalAmount',
            'pesonetAmount',
            'instapayAmount',
            'channel',
            'search',
        ));
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
