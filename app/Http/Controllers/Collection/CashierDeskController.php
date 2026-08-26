<?php

declare(strict_types=1);

namespace App\Http\Controllers\Collection;

use App\DTOs\Accounting\PosCollectionData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Collection\CollectPaymentRequest;
use App\Models\CashierShift;
use App\Models\Invoice;
use App\Models\Payment;
use App\Services\Accounting\CashierPaymentService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

final class CashierDeskController extends Controller
{
    public function __construct(
        private readonly CashierPaymentService $cashierPaymentService
    ) {}

    public function index(Request $request): View
    {
        $search = $request->query('q');
        $admissionType = $request->query('admission_type');
        $hideZero = $request->boolean('hide_zero', true);

        $shifts = CashierShift::with(['cashier', 'payments'])
            ->latest('opened_at')
            ->get();
        $terminals = $shifts;

        // Current active shift for logged in cashier (or any active shift fallback)
        $userId = auth()->id();
        $activeShift = CashierShift::with('cashier')
            ->where('status', 'OPEN')
            ->where(function ($q) use ($userId) {
                if ($userId) {
                    $q->where('cashier_id', $userId);
                }
            })
            ->latest('opened_at')
            ->first() ?? CashierShift::where('status', 'OPEN')->latest('opened_at')->first();

        // Invoices with pending patient payable
        $invoicesQuery = Invoice::with(['patientAccount', 'invoiceItems'])
            ->latest('invoice_date');

        if ($hideZero) {
            $invoicesQuery->where('patient_payable', '>', 0);
        }

        if ($search) {
            $invoicesQuery->where(function ($q) use ($search) {
                $q->where('invoice_number', 'LIKE', "%{$search}%")
                  ->orWhereHas('patientAccount', function ($pq) use ($search) {
                      $pq->where('full_name', 'LIKE', "%{$search}%")
                         ->orWhere('patient_id_number', 'LIKE', "%{$search}%");
                  });
            });
        }

        if ($admissionType && in_array($admissionType, ['Inpatient', 'Outpatient'])) {
            $invoicesQuery->whereHas('patientAccount', function ($pq) use ($admissionType) {
                $pq->where('admission_type', $admissionType);
            });
        }

        $pendingInvoices = $invoicesQuery->paginate(15)->withQueryString();

        $todayPayments = Payment::with(['invoice', 'patientAccount', 'officialReceipt'])
            ->whereDate('payment_date', today())
            ->latest('id')
            ->get();

        $todayTotal = $todayPayments->sum('amount');
        $cashReceipts = $todayPayments->where('payment_method', 'CASH')->sum('amount');
        $digitalReceipts = $todayPayments->where('payment_method', '!=', 'CASH')->sum('amount');

        $viewName = view()->exists('accounting.collection.cashier.index')
            ? 'accounting.collection.cashier.index'
            : 'collection.cashier-desk';

        return view($viewName, compact(
            'shifts',
            'terminals',
            'activeShift',
            'pendingInvoices',
            'todayPayments',
            'todayTotal',
            'cashReceipts',
            'digitalReceipts',
            'search',
            'admissionType',
            'hideZero'
        ));
    }

    public function collect(CollectPaymentRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $dto = new PosCollectionData(
            invoiceId: (int) $validated['invoice_id'],
            patientAccountId: null,
            cashierShiftId: ! empty($validated['cashier_shift_id']) ? (int) $validated['cashier_shift_id'] : null,
            paymentMethod: (string) $validated['payment_method'],
            amount: (string) $validated['amount'],
            tenderedAmount: ! empty($validated['tendered_amount']) ? (string) $validated['tendered_amount'] : null,
            gatewayProvider: $validated['gateway_provider'] ?? null,
            gatewayTransactionId: $validated['gateway_transaction_id'] ?? null,
            payorName: $validated['payor_name'] ?? null,
            payorTin: $validated['payor_tin'] ?? null,
            notes: $validated['notes'] ?? null,
        );

        $payment = $this->cashierPaymentService->collectPayment($dto);
        $orNo = $payment->officialReceipt?->or_number ?? $payment->payment_reference;
        $redirectRoute = str_contains(request()->path(), 'accounting/cashier')
            ? route('accounting.cashier')
            : route('collection.cashier-desk');

        return redirect()->to($redirectRoute)
            ->with('success', "Payment of ₱" . number_format((float) $payment->amount, 2) . " processed successfully! Official Receipt: {$orNo}");
    }
}
