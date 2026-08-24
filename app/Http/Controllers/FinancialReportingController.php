<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Invoice;
use App\Models\PurchaseBill;
use App\Models\BankAccount;
use App\Models\JournalEntry;
use App\Models\PaymentReceipt;
use App\Models\PaymentRequest;
use Illuminate\Contracts\View\View;

final class FinancialReportingController extends Controller
{
    public function balanceSheet(): View
    {
        $accounts = Account::with('journalEntryLines')->orderBy('code')->get();

        $assets      = $accounts->where('category', 'ASSET');
        $liabilities = $accounts->where('category', 'LIABILITY');
        $equity      = $accounts->where('category', 'EQUITY');

        $totalAssets      = $assets->sum(fn ($a) => (float) $a->current_balance);
        $totalLiabilities = $liabilities->sum(fn ($a) => (float) $a->current_balance);
        $totalEquity      = $equity->sum(fn ($a) => (float) $a->current_balance);

        $currentRatio = $totalLiabilities > 0
            ? round($totalAssets / $totalLiabilities, 2)
            : 0.0;

        return view('financial-reporting.balance-sheet', compact(
            'accounts',
            'assets',
            'liabilities',
            'equity',
            'totalAssets',
            'totalLiabilities',
            'totalEquity',
            'currentRatio',
        ));
    }

    public function profitLoss(): View
    {
        $accounts = Account::with('journalEntryLines')->orderBy('code')->get();

        $revenues  = $accounts->where('category', 'REVENUE');
        $expenses  = $accounts->where('category', 'EXPENSE');

        $totalRevenue  = $revenues->sum(fn ($a) => (float) $a->current_balance);
        $totalExpense  = $expenses->sum(fn ($a) => (float) $a->current_balance);
        $netIncome     = bcsub((string) $totalRevenue, (string) $totalExpense, 4);

        return view('financial-reporting.profit-loss', compact(
            'revenues',
            'expenses',
            'totalRevenue',
            'totalExpense',
            'netIncome',
        ));
    }

    public function cashFlowStatement(): View
    {
        $accounts      = Account::all();
        $operatingCash = PaymentReceipt::sum('amount_paid');
        $supplierCash  = PurchaseBill::where('status', 'PAID')->sum('paid_amount');
        $payrollCash   = PaymentRequest::where('status', 'DISBURSED')->sum('amount');
        $operatingNet  = (float) $operatingCash - (float) $supplierCash - (float) $payrollCash;
        
        $investingNet  = 0.0;
        $financingNet  = 0.0;
        $netCashFlow   = $operatingNet + $investingNet + $financingNet;
        $bankBalance   = BankAccount::where('status', 'Active')->sum('balance');

        return view('financial-reporting.cash-flow-statement', compact(
            'operatingCash',
            'supplierCash',
            'payrollCash',
            'operatingNet',
            'investingNet',
            'financingNet',
            'netCashFlow',
            'bankBalance',
        ));
    }

    public function kpiDashboard(): View
    {
        // Days Sales Outstanding (DSO): AR / avg daily revenue
        $totalAR       = Invoice::whereIn('status', ['UNPAID', 'PARTIAL'])->sum('patient_payable');
        $accounts      = Account::with('journalEntryLines')->get();
        $totalRevenue  = $accounts->where('category', 'REVENUE')->sum(fn ($a) => (float) $a->current_balance);
        $totalExpense  = $accounts->where('category', 'EXPENSE')->sum(fn ($a) => (float) $a->current_balance);

        $avgDailyRevenue = $totalRevenue > 0 ? $totalRevenue / 365 : 1;
        $dso             = $totalAR > 0 ? round($totalAR / $avgDailyRevenue, 1) : 0.0;

        $operatingProfitMargin = $totalRevenue > 0
            ? round((($totalRevenue - $totalExpense) / $totalRevenue) * 100, 1)
            : 0.0;

        $totalAssets      = $accounts->where('category', 'ASSET')->sum(fn ($a) => (float) $a->current_balance);
        $totalLiabilities = $accounts->where('category', 'LIABILITY')->sum(fn ($a) => (float) $a->current_balance);

        $currentRatio = $totalLiabilities > 0
            ? round($totalAssets / $totalLiabilities, 2)
            : 0.0;

        return view('financial-reporting.financial-kpi-dashboard', compact(
            'dso',
            'operatingProfitMargin',
            'totalRevenue',
            'totalExpense',
            'totalAssets',
            'totalLiabilities',
            'currentRatio',
        ));
    }

    public function executiveReports(): View
    {
        $accounts  = Account::with('journalEntryLines')->get();
        $totalAR   = Invoice::whereIn('status', ['UNPAID', 'PARTIAL'])->sum('patient_payable');
        $totalAP   = PurchaseBill::whereIn('status', ['UNPAID', 'PARTIAL'])->sum('total_amount');
        $cashPool  = BankAccount::where('status', 'Active')->sum('balance');

        $reports = [];

        $totalRevenue = $accounts->where('category', 'REVENUE')->sum(fn ($a) => (float) $a->current_balance);
        $totalExpense = $accounts->where('category', 'EXPENSE')->sum(fn ($a) => (float) $a->current_balance);
        $netIncome    = bcsub((string) $totalRevenue, (string) $totalExpense, 4);

        return view('financial-reporting.executive-reports', compact(
            'reports',
            'totalAR',
            'totalAP',
            'cashPool',
            'totalRevenue',
            'totalExpense',
            'netIncome',
        ));
    }
}
