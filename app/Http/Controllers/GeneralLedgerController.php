<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\JournalEntry;
use Illuminate\Contracts\View\View;

final class GeneralLedgerController extends Controller
{
    public function chartOfAccounts(): View
    {
        $accounts = Account::with('journalEntryLines')->orderBy('code')->get();
        return view('general-ledger.chart-of-accounts', compact('accounts'));
    }

    public function journalEntries(): View
    {
        $entries = JournalEntry::with('lines.account')->latest('entry_date')->get();

        $monthStart = now()->startOfMonth();
        $monthlyEntries = JournalEntry::with('lines')
            ->where('entry_date', '>=', $monthStart)
            ->get();

        $monthlyDebitTotal  = $monthlyEntries->sum(fn ($je) => $je->lines->sum('debit'));
        $monthlyCreditTotal = $monthlyEntries->sum(fn ($je) => $je->lines->sum('credit'));
        $postedCount        = JournalEntry::where('status', 'POSTED')->count();
        $draftCount         = JournalEntry::where('status', 'DRAFT')->count();

        return view('general-ledger.journal-entries', compact(
            'entries',
            'monthlyDebitTotal',
            'monthlyCreditTotal',
            'postedCount',
            'draftCount',
        ));
    }

    public function ledgerBooks(): View
    {
        $accounts = Account::with('journalEntryLines.journalEntry')->orderBy('code')->get();

        $ytdDebitTotal  = $accounts->sum(fn ($a) => $a->journalEntryLines->sum('debit'));
        $ytdCreditTotal = $accounts->sum(fn ($a) => $a->journalEntryLines->sum('credit'));

        return view('general-ledger.ledger-books', compact('accounts', 'ytdDebitTotal', 'ytdCreditTotal'));
    }

    public function trialBalance(): View
    {
        $accounts = Account::with('journalEntryLines')->orderBy('code')->get();

        $totalDebitBalance  = $accounts
            ->filter(fn ($a) => strtoupper($a->normal_balance) === 'DEBIT')
            ->sum(fn ($a) => (float) $a->current_balance);

        $totalCreditBalance = $accounts
            ->filter(fn ($a) => strtoupper($a->normal_balance) === 'CREDIT')
            ->sum(fn ($a) => (float) $a->current_balance);

        return view('general-ledger.trial-balance', compact(
            'accounts',
            'totalDebitBalance',
            'totalCreditBalance',
        ));
    }

    public function periodEndClosing(): View
    {
        $activePeriod = now()->format('F Y');
        $unpostedEntriesCount = JournalEntry::where('status', '!=', 'POSTED')->count();
        $totalEntriesCount = JournalEntry::count();

        return view('general-ledger.period-end-closing', compact(
            'activePeriod',
            'unpostedEntriesCount',
            'totalEntriesCount',
        ));
    }
}
