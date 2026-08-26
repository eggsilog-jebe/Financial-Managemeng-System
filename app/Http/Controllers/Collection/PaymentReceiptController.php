<?php

declare(strict_types=1);

namespace App\Http\Controllers\Collection;

use App\Http\Controllers\Controller;
use App\Http\Requests\Collection\VoidPaymentReceiptRequest;
use App\Models\Payment;
use App\Services\Accounting\CashierPaymentService;
use DomainException;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

final class PaymentReceiptController extends Controller
{
    public function __construct(
        private readonly CashierPaymentService $cashierPaymentService
    ) {}

    public function index(Request $request): View
    {
        $query = Payment::with(['patientAccount', 'invoice', 'officialReceipt', 'cashierShift'])
            ->latest('payment_date');

        if ($request->filled('method')) {
            $query->where('payment_method', $request->input('method'));
        }

        if ($request->filled('date_from')) {
            $query->whereDate('payment_date', '>=', $request->input('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->whereDate('payment_date', '<=', $request->input('date_to'));
        }

        if ($request->filled('search')) {
            $search = '%' . $request->input('search') . '%';
            $query->where(function ($q) use ($search) {
                $q->where('payment_reference', 'like', $search)
                  ->orWhereHas('officialReceipt', function ($sub) use ($search) {
                      $sub->where('or_number', 'like', $search)
                          ->orWhere('payor_name', 'like', $search);
                  })
                  ->orWhereHas('patientAccount', function ($sub) use ($search) {
                      $sub->where('full_name', 'like', $search)
                          ->orWhere('patient_id_number', 'like', $search);
                  });
            });
        }

        $payments = $query->paginate(20)->withQueryString();

        $allPayments = Payment::all();
        $totalCollected = $allPayments->sum('amount');
        $cashCollected = $allPayments->where('payment_method', 'CASH')->sum('amount');
        $digitalCollected = $allPayments->where('payment_method', '!=', 'CASH')->sum('amount');

        $viewName = view()->exists('accounting.collection.receipts.index')
            ? 'accounting.collection.receipts.index'
            : 'collection.payment-receipts';

        return view($viewName, compact(
            'payments',
            'totalCollected',
            'cashCollected',
            'digitalCollected'
        ));
    }

    public function print(int $id): View
    {
        $payment = Payment::with(['patientAccount', 'invoice.items', 'officialReceipt', 'cashierShift.cashier'])
            ->findOrFail($id);

        $receipt = $payment->officialReceipt;
        $invoice = $payment->invoice;

        return view('accounting.print.official-receipt', compact('payment', 'receipt', 'invoice'));
    }

    public function voidReceipt(VoidPaymentReceiptRequest $request, int $id): RedirectResponse
    {
        $validated = $request->validated();
        $authorizedUserId = auth()->id() ?? 1;

        try {
            $payment = $this->cashierPaymentService->voidPayment($id, $validated['reason'], $authorizedUserId);

            return redirect()->back()->with('success', "Payment [{$payment->payment_reference}] has been voided and reversing journal entry posted.");
        } catch (DomainException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }
}
