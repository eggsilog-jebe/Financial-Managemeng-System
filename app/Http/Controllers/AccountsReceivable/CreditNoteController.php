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

    /**
     * Display a listing of Credit Notes and summary KPIs.
     */
    public function index(Request $request): View
    {
        $status = $request->query('status');
        $search = $request->query('search');

        $query = CreditNote::with(['invoice.patientAccount', 'patientAccount', 'approvedBy'])
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

        // Aggregation totals
        $totalCreditValue = CreditNote::whereIn('status', ['APPROVED', 'POSTED', 'APPLIED'])->sum('amount');
        $totalPendingApproval = CreditNote::where('status', 'DRAFT')->sum('amount');

        // Fetch open invoices with remaining copay balance for the modal
        $openInvoices = Invoice::with(['patientAccount', 'statutoryDiscounts', 'creditNotes'])
            ->whereIn('status', ['UNPAID', 'PARTIAL'])
            ->where('patient_payable', '>', 0)
            ->latest('invoice_date')
            ->get();

        return view('accounts-receivable.credit-notes', [
            'creditNotes'          => $creditNotes,
            'totalCreditValue'     => $totalCreditValue,
            'totalPendingApproval' => $totalPendingApproval,
            'openInvoices'         => $openInvoices,
            'status'               => $status,
            'search'               => $search,
        ]);
    }

    /**
     * Store a newly created Credit Note.
     */
    public function store(StoreCreditNoteRequest $request): RedirectResponse
    {
        try {
            $dto = CreditNoteData::fromRequest($request);
            $creditNote = $this->creditNoteService->createCreditNote($dto, auth()->id());

            $message = $creditNote->status === 'POSTED'
                ? "Credit Note adjustment [{$creditNote->credit_note_number}] approved and posted to General Ledger."
                : "Credit Note adjustment [{$creditNote->credit_note_number}] submitted for management approval.";

            return redirect()->back()->with('success', $message);
        } catch (DomainException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * Authorize and post a draft Credit Note to the General Ledger.
     */
    public function approve(int|string $id): RedirectResponse
    {
        try {
            $userId = auth()->id() ?? 1;
            $creditNote = $this->creditNoteService->approveCreditNote((int) $id, $userId);

            return redirect()->back()->with(
                'success',
                "Credit Note [{$creditNote->credit_note_number}] approved and posted to General Ledger (₱" . number_format((float) $creditNote->amount, 2) . ")."
            );
        } catch (DomainException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * Dedicated action to post a draft Credit Note to General Ledger.
     */
    public function postCreditNote(int|string $id): RedirectResponse
    {
        return $this->approve($id);
    }

    /**
     * Void a Credit Note adjustment and reverse ledger postings.
     */
    public function void(Request $request, int|string $id): RedirectResponse
    {
        $request->validate([
            'void_reason' => ['required', 'string', 'max:255'],
        ]);

        try {
            $userId = auth()->id() ?? 1;
            $reason = (string) $request->input('void_reason');
            $creditNote = $this->creditNoteService->voidCreditNote((int) $id, $reason, $userId);

            return redirect()->back()->with('success', "Credit Note [{$creditNote->credit_note_number}] has been voided.");
        } catch (DomainException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }
}
