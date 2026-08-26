<?php

declare(strict_types=1);

namespace App\Http\Controllers\AccountsReceivable;

use App\DTOs\Accounting\PatientInvoiceCreateData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Accounting\StorePatientInvoiceRequest;
use App\Models\Invoice;
use App\Models\PatientAccount;
use App\Services\Accounting\InvoiceBillingService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

final class PatientInvoiceController extends Controller
{
    public function __construct(
        private readonly InvoiceBillingService $billingService,
    ) {}

    public function index(Request $request): View
    {
        $status = $request->query('status');
        $search = $request->query('search');

        $query = Invoice::with(['patientAccount', 'items', 'hmoClaims', 'philhealthClaim', 'statutoryDiscounts', 'creditNotes'])
            ->latest('invoice_date');

        if ($status) {
            $query->where('status', $status);
        }

        if ($search) {
            $query->where(function ($q) use ($search): void {
                $q->where('invoice_number', 'LIKE', "%{$search}%")
                  ->orWhereHas('patientAccount', fn ($pq) => $pq->where('full_name', 'LIKE', "%{$search}%")->orWhere('patient_id_number', 'LIKE', "%{$search}%"));
            });
        }

        $invoices = $query->paginate(15)->withQueryString();

        $totalBilled = Invoice::sum('total_amount');
        $totalPending = Invoice::whereIn('status', ['UNPAID', 'PARTIAL'])->sum('patient_payable');
        $totalPaid = Invoice::where('status', 'PAID')->orWhere('status', 'SETTLED')->sum('total_amount');
        $patients = PatientAccount::where('status', 'Active')->orderBy('full_name')->get();

        return view('accounts-receivable.invoicing-billing', compact(
            'invoices',
            'totalBilled',
            'totalPending',
            'totalPaid',
            'patients',
            'status',
            'search',
        ));
    }

    public function store(StorePatientInvoiceRequest $request): RedirectResponse
    {
        $dto = PatientInvoiceCreateData::fromArray($request->validated());
        $invoice = $this->billingService->createPatientInvoice($dto);

        return redirect()->back()->with('success', "Patient Invoice [{$invoice->invoice_number}] generated successfully with statutory discounts and general ledger recognition.");
    }

    public function print(int|string $id): View
    {
        $invoice = Invoice::with(['patientAccount', 'items', 'philhealthClaim', 'hmoClaims', 'statutoryDiscounts', 'creditNotes', 'payments'])
            ->findOrFail((int) $id);

        return view('accounts-receivable.invoice-print', compact('invoice'));
    }
}
