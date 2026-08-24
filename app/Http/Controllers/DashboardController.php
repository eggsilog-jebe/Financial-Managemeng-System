<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Invoice;
use App\Models\PurchaseBill;
use App\Models\BankAccount;
use App\Models\JournalEntry;
use Illuminate\Contracts\View\View;

final class DashboardController extends Controller
{
    public function __invoke(): View
    {
        // General Ledger: sum of all asset account balances as proxy for total ledger
        $accounts = Account::with('journalEntryLines')->get();

        $totalLedgerBalance = $accounts
            ->where('category', 'ASSET')
            ->sum(fn ($acc) => (float) $acc->current_balance);

        // Accounts Receivable: sum of outstanding patient invoices
        $totalAR         = Invoice::whereIn('status', ['UNPAID', 'PARTIAL'])->sum('patient_payable');
        $activeInvoices  = Invoice::whereIn('status', ['UNPAID', 'PARTIAL'])->count();

        // Accounts Payable: sum of outstanding purchase bills
        $totalAP         = PurchaseBill::whereIn('status', ['UNPAID', 'PARTIAL'])->sum('total_amount');
        $pendingVendors  = PurchaseBill::whereIn('status', ['UNPAID', 'PARTIAL'])->count();

        // Cash Management: total liquid across all active bank accounts
        $totalCash       = BankAccount::where('status', 'Active')->sum('balance');
        $bankAccountCount = BankAccount::where('status', 'Active')->count();

        return view('welcome', compact(
            'totalLedgerBalance',
            'totalAR',
            'activeInvoices',
            'totalAP',
            'pendingVendors',
            'totalCash',
            'bankAccountCount',
        ));
    }
}
