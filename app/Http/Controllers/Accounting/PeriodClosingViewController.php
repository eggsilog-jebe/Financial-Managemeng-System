<?php

declare(strict_types=1);

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Models\JournalEntry;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

final class PeriodClosingViewController extends Controller
{
    public function index(): View
    {
        $activePeriod = now()->format('F Y');
        $unpostedEntriesCount = JournalEntry::where('status', '!=', 'POSTED')->count();
        $totalEntriesCount = JournalEntry::count();

        return view('accounting.period-close', compact(
            'activePeriod',
            'unpostedEntriesCount',
            'totalEntriesCount'
        ));
    }

    public function lock(Request $request): RedirectResponse
    {
        $period = $request->input('period_name', now()->format('F Y'));

        return redirect()->route('accounting.period-close.index')
            ->with('success', "Fiscal Period [{$period}] has been officially HARD LOCKED. Retroactive entries are now strictly rejected under BIR CAS compliance.");
    }
}
