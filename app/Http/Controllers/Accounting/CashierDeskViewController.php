<?php

declare(strict_types=1);

namespace App\Http\Controllers\Accounting;

use App\DTOs\PaymentReceiptData;
use App\Http\Controllers\Controller;
use App\Models\CashierShift;
use App\Models\Invoice;
use App\Models\Payment;
use App\Services\Accounting\CollectionService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

final class CashierDeskViewController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->query('q');

        // Open unpaid / partial invoices
        $invoicesQuery = Invoice::with(['patientAccount', 'invoiceItems'])
            ->whereIn('status', ['UNPAID', 'PARTIAL'])
            ->latest('id');

        if ($search) {
            $invoicesQuery->where(function ($q) use ($search) {
                $q->where('invoice_number', 'LIKE', "%{$search}%")
                  ->orWhereHas('patientAccount', function ($pq) use ($search) {
                      $pq->where('full_name', 'LIKE', "%{$search}%")
                         ->orWhere('patient_id_number', 'LIKE', "%{$search}%");
                  });
            });
        }

        $openInvoices = $invoicesQuery->paginate(10)->withQueryString();

        // Recent official payment receipts
        $recentPayments = Payment::with(['patientAccount', 'invoice', 'officialReceipt'])
            ->latest('id')
            ->limit(10)
            ->get();

        // Active shift
        $activeShift = CashierShift::where('status', 'OPEN')->first();

        return view('accounting.cashier.index', [
            'openInvoices'   => $openInvoices,
            'recentPayments' => $recentPayments,
            'activeShift'    => $activeShift,
            'search'         => $search,
        ]);
    }

    public function processPayment(Request $request, CollectionService $collectionService): RedirectResponse
    {
        $validated = $request->validate([
            'invoice_id'         => ['required', 'integer', 'exists:invoices,id'],
            'amount'             => ['required', 'numeric', 'min:0.01'],
            'payment_method'     => ['required', 'string', 'in:CASH,CREDIT_CARD,DEBIT_CARD,QR_PH,GCASH,MAYA,CHECK,ONLINE_BANK'],
            'transaction_ref'    => ['nullable', 'string', 'max:100'],
            'notes'              => ['nullable', 'string', 'max:255'],
        ]);

        $invoice = Invoice::with('patientAccount')->findOrFail($validated['invoice_id']);
        $shift = CashierShift::where('status', 'OPEN')->first();

        $dto = new PaymentReceiptData(
            patientAccountId: $invoice->patient_account_id,
            invoiceId: $invoice->id,
            cashierShiftId: $shift?->id,
            amount: (string) $validated['amount'],
            paymentMethod: $validated['payment_method'],
            transactionChannelRef: $validated['transaction_ref'] ?? null,
            payorName: $invoice->patientAccount->full_name,
            payorTin: null,
            paymentDate: date('Y-m-d'),
            paymentType: 'PATIENT_COPAY',
        );

        $payment = $collectionService->processCollection($dto);

        return redirect()->route('accounting.cashier.index')
            ->with('success', "Payment successfully posted! Official Receipt #{$payment->officialReceipt?->or_number} generated.");
    }
}
