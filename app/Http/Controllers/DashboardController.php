<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Invoice;
use App\Models\PurchaseBill;
use App\Models\BankAccount;
use App\Models\JournalEntry;
use App\Services\Accounting\AccountingCacheService;
use Illuminate\Contracts\View\View;

final class DashboardController extends Controller
{
    public function __invoke(AccountingCacheService $cacheService): View
    {
        $metrics = $cacheService->rememberWelcomeMetrics(function (): array {
            // General Ledger: sum of all asset account balances computed directly at the database level
            $totalLedgerBalance = (float) \Illuminate\Support\Facades\DB::table('accounts')
                ->join('journal_entry_lines', 'accounts.id', '=', 'journal_entry_lines.account_id')
                ->where('accounts.category', 'ASSET')
                ->selectRaw("COALESCE(SUM(CASE WHEN accounts.normal_balance = 'DEBIT' THEN (journal_entry_lines.debit - journal_entry_lines.credit) ELSE (journal_entry_lines.credit - journal_entry_lines.debit) END), 0) as balance")
                ->value('balance');

            // Accounts Receivable: single-trip sum and count of outstanding patient invoices
            $arStats = Invoice::query()
                ->whereIn('status', ['UNPAID', 'PARTIAL'])
                ->selectRaw('COALESCE(SUM(patient_payable), 0) as total, COUNT(*) as count')
                ->first();
            $totalAR        = (float) ($arStats->total ?? 0);
            $activeInvoices = (int) ($arStats->count ?? 0);

            // Accounts Payable: single-trip sum and count of outstanding purchase bills
            $apStats = PurchaseBill::query()
                ->whereIn('status', ['UNPAID', 'PARTIAL'])
                ->selectRaw('COALESCE(SUM(total_amount), 0) as total, COUNT(*) as count')
                ->first();
            $totalAP        = (float) ($apStats->total ?? 0);
            $pendingVendors = (int) ($apStats->count ?? 0);

            // Cash Management: single-trip sum and count of active bank accounts
            $bankStats = BankAccount::query()
                ->where('status', 'Active')
                ->selectRaw('COALESCE(SUM(balance), 0) as total, COUNT(*) as count')
                ->first();
            $totalCash        = (float) ($bankStats->total ?? 0);
            $bankAccountCount = (int) ($bankStats->count ?? 0);

            return compact(
                'totalLedgerBalance',
                'totalAR',
                'activeInvoices',
                'totalAP',
                'pendingVendors',
                'totalCash',
                'bankAccountCount',
            );
        }, 120);

        return view('welcome', $metrics);
    }
}
