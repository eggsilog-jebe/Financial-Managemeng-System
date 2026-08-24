<?php

declare(strict_types=1);

namespace App\Http\Controllers\Accounting;

use App\DTOs\Accounting\JournalEntryData;
use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\JournalEntry;
use App\Services\Accounting\JournalEntryService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

final class GeneralLedgerBrowserController extends Controller
{
    public function index(Request $request): View
    {
        $status = $request->query('status');
        $dateFrom = $request->query('date_from');
        $dateTo = $request->query('date_to');
        $search = $request->query('q');

        $query = JournalEntry::with(['lines.account', 'user'])
            ->latest('id');

        if ($status) {
            $query->where('status', $status);
        }

        if ($dateFrom) {
            $query->whereDate('entry_date', '>=', $dateFrom);
        }

        if ($dateTo) {
            $query->whereDate('entry_date', '<=', $dateTo);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('reference_number', 'LIKE', "%{$search}%")
                  ->orWhere('description', 'LIKE', "%{$search}%");
            });
        }

        $entries = $query->paginate(15)->withQueryString();
        $accounts = Account::active()->orderBy('code')->get();

        return view('accounting.general-ledger.index', [
            'entries'  => $entries,
            'accounts' => $accounts,
            'status'   => $status,
            'dateFrom' => $dateFrom,
            'dateTo'   => $dateTo,
            'search'   => $search,
        ]);
    }

    public function reverse(Request $request, int $id, JournalEntryService $service): RedirectResponse
    {
        $request->validate([
            'reason' => ['required', 'string', 'max:255'],
        ]);

        $entry = JournalEntry::findOrFail($id);
        $reversal = $service->reverseEntry($entry, auth()->id() ?? 1, $request->input('reason'));

        return redirect()->route('accounting.general-ledger.index')
            ->with('success', "Journal Entry [{$entry->reference_number}] successfully reversed via Reversal Entry [{$reversal->reference_number}].");
    }
}
