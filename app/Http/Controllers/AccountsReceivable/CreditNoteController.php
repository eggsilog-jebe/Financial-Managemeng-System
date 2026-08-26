<?php

declare(strict_types=1);

namespace App\Http\Controllers\AccountsReceivable;

use App\DTOs\Accounting\CreditNoteData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Accounting\StoreCreditNoteRequest;
use App\Models\CreditNote;
use App\Models\Invoice;
use App\Services\Accounting\CreditNoteService;
use DomainException;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

final class CreditNoteController extends Controller
{
    public function __construct(
        private readonly CreditNoteService $creditNoteService,
    ) {}

    public function index(Request $request): View
    {
        $status = $request->query('status');
        $search = $request->query('search');

        $query = CreditNote::with(['invoice.patientAccount', 'patientAccount', 'approver'])
            ->latest('issue_date');

        if ($status) {
            $query->where('status', $status);
        }

        if ($search) {
            $query->where(function ($q) use ($search): void {
                $q->where('credit_note_number', 'LIKE', "%{$search}%")
                  ->orWhere('reason', 'LIKE', "%{$search}%")
                  ->orWhereHas('invoice', fn ($iq) => $iq->where('invoice_number', 'LIKE', "%{$search}%"))
                  ->orWhereHas('patientAccount', fn ($pq) => $pq->where('full_name', 'LIKE', "%{$search}%"));
            });
        }

        $creditNotes = $query->paginate(15)->withQueryString();
        $totalCreditValue = CreditNote::whereIn('status', ['APPROVED', 'POSTED', 'APPLIED'])->sum('amount');
        $totalPendingApproval = CreditNote::where('status', 'DRAFT')->sum('amount');

        $openInvoices = Invoice::with('patientAccount')
            ->whereIn('status', ['UNPAID', 'PARTIAL'])
            ->where('patient_payable', '>', 0)
            ->latest('invoice_date')
            ->get();

        return view('accounts-receivable.credit-notes', compact(
            'creditNotes',
            'totalCreditValue',
            'totalPendingApproval',
            'openInvoices',
            'status',
            'search',
        ));
    }

    public function store(StoreCreditNoteRequest $request): RedirectResponse
    {
        try {
            $dto = CreditNoteData::fromArray($request->validated());
            $cn = $this->creditNoteService->createCreditNote($dto);

            return redirect()->back()->with('success', "Credit Note adjustment [{$cn->credit_note_number}] submitted for management approval.");
        } catch (DomainException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function approve(int|string $id): RedirectResponse
    {
        try {
            $userId = auth()->id() ?? 1;
            $cn = $this->creditNoteService->approveCreditNote((int) $id, $userId);

            return redirect()->back()->with('success', "Credit Note [{$cn->credit_note_number}] approved and posted to General Ledger ($" . number_format((float) $cn->amount, 2) . ").");
        } catch (DomainException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }
}
