<?php

declare(strict_types=1);

namespace App\Http\Controllers\GeneralLedger;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Services\Accounting\AccountingCacheService;
use App\Services\Accounting\LedgerBookService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class LedgerBookController extends Controller
{
    public function __construct(
        private readonly LedgerBookService $ledgerBookService,
        private readonly AccountingCacheService $cacheService,
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

        // Metric summaries across all active GL accounts (cached via Redis)
        $totals = $this->cacheService->rememberLedgerTotals(function (): array {
            $agg = DB::table('journal_entry_lines')
                ->join('journal_entries', 'journal_entry_lines.journal_entry_id', '=', 'journal_entries.id')
                ->where('journal_entries.status', 'POSTED')
                ->selectRaw('COALESCE(SUM(journal_entry_lines.debit), 0) as total_debit, COALESCE(SUM(journal_entry_lines.credit), 0) as total_credit')
                ->first();

            return [
                'ytdDebitTotal'  => (float) ($agg?->total_debit ?? 0.0),
                'ytdCreditTotal' => (float) ($agg?->total_credit ?? 0.0),
            ];
        }, 300);

        $ytdDebitTotal  = $totals['ytdDebitTotal'];
        $ytdCreditTotal = $totals['ytdCreditTotal'];

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
