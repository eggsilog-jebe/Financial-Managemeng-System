<?php

declare(strict_types=1);

namespace App\Http\Controllers\GeneralLedger;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Services\Accounting\LedgerBookService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class LedgerBookController extends Controller
{
    public function __construct(
        private readonly LedgerBookService $ledgerBookService,
    ) {}

    public function index(Request $request): View
    {
        $accounts = Account::orderBy('code')->get();

        $selectedAccountId = $request->query('account_id')
            ? (int) $request->query('account_id')
            : ($accounts->first()?->id ?? 1);

        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');
        $fiscalYear = $request->query('fiscal_year');

        $statement = $accounts->isNotEmpty()
            ? $this->ledgerBookService->getAccountLedgerStatement($selectedAccountId, $startDate, $endDate, $fiscalYear)
            : null;

        // Metric summaries across all active GL accounts
        $allAccounts = Account::with(['journalEntryLines.journalEntry' => function ($q): void {
            $q->where('status', 'POSTED');
        }])->get();

        $ytdDebitTotal  = $allAccounts->sum(fn (Account $a): float => (float) $a->journalEntryLines->sum('debit'));
        $ytdCreditTotal = $allAccounts->sum(fn (Account $a): float => (float) $a->journalEntryLines->sum('credit'));

        return view('general-ledger.ledger-books', [
            'accounts'          => $accounts,
            'selectedAccountId' => $selectedAccountId,
            'statement'         => $statement,
            'startDate'         => $startDate,
            'endDate'           => $endDate,
            'fiscalYear'        => $fiscalYear,
            'ytdDebitTotal'     => $ytdDebitTotal,
            'ytdCreditTotal'    => $ytdCreditTotal,
        ]);
    }

    public function export(Request $request): StreamedResponse
    {
        $accounts = Account::orderBy('code')->get();
        $accountId = (int) ($request->query('account_id') ?? $accounts->first()?->id ?? 1);
        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');
        $fiscalYear = $request->query('fiscal_year');

        return $this->ledgerBookService->exportAccountCsv(
            accountId: $accountId,
            startDate: $startDate,
            endDate: $endDate,
            fiscalYear: $fiscalYear,
        );
    }
}
