<?php

declare(strict_types=1);

namespace App\Http\Controllers\GeneralLedger;

use App\DTOs\Accounting\JournalEntryData;
use App\DTOs\JournalLineData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Accounting\ReverseJournalEntryRequest;
use App\Http\Requests\Accounting\StoreJournalEntryRequest;
use App\Models\Account;
use App\Models\JournalEntry;
use App\Services\Accounting\JournalEntryService;
use DomainException;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class JournalEntryController extends Controller
{
    public function __construct(
        private readonly JournalEntryService $journalService,
    ) {}

    public function index(Request $request): View
    {
        $status = $request->query('status');
        $type = $request->query('type');
        $search = $request->query('q') ?? $request->query('search');
        $dateFrom = $request->query('date_from');
        $dateTo = $request->query('date_to');

        $query = JournalEntry::with(['lines.account', 'creator', 'reversedByEntry'])
            ->latest('entry_date')
            ->latest('id');

        if ($status) {
            $query->where('status', strtoupper($status));
        }

        if ($type) {
            $query->where('type', strtoupper($type));
        }

        if ($dateFrom) {
            $query->whereDate('entry_date', '>=', $dateFrom);
        }

        if ($dateTo) {
            $query->whereDate('entry_date', '<=', $dateTo);
        }

        if ($search) {
            $query->where(function ($q) use ($search): void {
                $q->where('reference_number', 'LIKE', "%{$search}%")
                  ->orWhere('description', 'LIKE', "%{$search}%");
            });
        }

        $entries = $query->paginate(20)->withQueryString();

        // Metric summaries
        $monthStart = now()->startOfMonth();
        $monthlyEntries = JournalEntry::with('lines')
            ->where('entry_date', '>=', $monthStart)
            ->where('status', 'POSTED')
            ->get();

        $monthlyDebitTotal  = $monthlyEntries->sum(fn (JournalEntry $je): float => (float) $je->lines->sum('debit'));
        $monthlyCreditTotal = $monthlyEntries->sum(fn (JournalEntry $je): float => (float) $je->lines->sum('credit'));
        $postedCount        = JournalEntry::where('status', 'POSTED')->count();
        $draftCount         = JournalEntry::where('status', 'DRAFT')->count();

        $accounts = Account::active()->orderBy('code')->get();

        return view('general-ledger.journal-entries', [
            'entries'            => $entries,
            'accounts'           => $accounts,
            'monthlyDebitTotal'  => $monthlyDebitTotal,
            'monthlyCreditTotal' => $monthlyCreditTotal,
            'postedCount'        => $postedCount,
            'draftCount'         => $draftCount,
            'selectedStatus'     => $status,
            'selectedType'       => $type,
            'search'             => $search,
            'dateFrom'           => $dateFrom,
            'dateTo'             => $dateTo,
        ]);
    }

    public function store(StoreJournalEntryRequest $request): Response
    {
        $validated = $request->validated();
        $autoPost = filter_var($validated['auto_post'] ?? true, FILTER_VALIDATE_BOOLEAN);

        $lines = array_map(
            fn(array $line): JournalLineData => new JournalLineData(
                accountId: (int) $line['account_id'],
                debit: (string) ($line['debit'] ?? '0.0000'),
                credit: (string) ($line['credit'] ?? '0.0000'),
                memo: $line['memo'] ?? null
            ),
            $validated['lines']
        );

        $dto = new JournalEntryData(
            entryDate: (string) $validated['entry_date'],
            description: (string) $validated['description'],
            lines: $lines,
            userId: (int) ($request->user()?->id ?? 1),
            referenceNumber: ! empty($validated['reference_number']) ? (string) $validated['reference_number'] : null,
            type: (string) ($validated['type'] ?? 'GENERAL'),
        );

        try {
            $entry = $this->journalService->createEntry($dto, autoPost: $autoPost);
        } catch (DomainException $e) {
            if ($request->wantsJson()) {
                return response()->json(['error' => $e->getMessage()], 422);
            }
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }

        $msg = $autoPost
            ? "Journal Entry [{$entry->reference_number}] balanced and posted to General Ledger."
            : "Draft Journal Entry [{$entry->reference_number}] successfully saved.";

        if ($request->wantsJson()) {
            return response()->json([
                'message' => $msg,
                'entry'   => $entry,
            ], 201);
        }

        return redirect()->route('gl.journal-entries')->with('success', $msg);
    }

    public function post(Request $request, int $id): Response
    {
        $user = $request->user();
        $role = $user?->role ?? 'StaffAccountant';

        if (! in_array($role, ['FinanceManager', 'CFO', 'FinanceDirector'], true) && $user !== null) {
            abort(403, "Segregation of Duties: Only Finance Managers and CFOs may authorize posting to the General Ledger.");
        }

        $entry = JournalEntry::findOrFail($id);

        try {
            $posted = $this->journalService->postEntry($entry, (int) ($user?->id ?? 1));
        } catch (DomainException $e) {
            if ($request->wantsJson()) {
                return response()->json(['error' => $e->getMessage()], 422);
            }
            return redirect()->back()->with('error', $e->getMessage());
        }

        $msg = "Journal Entry [{$posted->reference_number}] successfully posted to Ledger.";

        if ($request->wantsJson()) {
            return response()->json(['message' => $msg, 'entry' => $posted]);
        }

        return redirect()->route('gl.journal-entries')->with('success', $msg);
    }

    public function reverse(ReverseJournalEntryRequest $request, int $id): Response
    {
        $entry = JournalEntry::findOrFail($id);
        $reason = (string) $request->validated('reason');
        $userId = (int) ($request->user()?->id ?? 1);

        try {
            $reversal = $this->journalService->reverseEntry($entry, $userId, $reason);
        } catch (DomainException $e) {
            if ($request->wantsJson()) {
                return response()->json(['error' => $e->getMessage()], 422);
            }
            return redirect()->back()->with('error', $e->getMessage());
        }

        $msg = "Journal Entry [{$entry->reference_number}] reversed via Reversal Entry [{$reversal->reference_number}].";

        if ($request->wantsJson()) {
            return response()->json(['message' => $msg, 'reversal' => $reversal]);
        }

        return redirect()->route('gl.journal-entries')->with('success', $msg);
    }
}
