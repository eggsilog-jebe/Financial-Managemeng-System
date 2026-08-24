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
        $accounts = Account::orderBy('code')->get();
        return view('general-ledger.chart-of-accounts', compact('accounts'));
    }

    public function journalEntries(): View
    {
        $entries = JournalEntry::with('lines.account')->latest('entry_date')->get();
        return view('general-ledger.journal-entries', compact('entries'));
    }

    public function ledgerBooks(): View
    {
        $accounts = Account::with('journalEntryLines.journalEntry')->orderBy('code')->get();
        return view('general-ledger.ledger-books', compact('accounts'));
    }

    public function trialBalance(): View
    {
        $accounts = Account::with('journalEntryLines')->orderBy('code')->get();
        return view('general-ledger.trial-balance', compact('accounts'));
    }

    public function periodEndClosing(): View
    {
        return view('general-ledger.period-end-closing');
    }
}
