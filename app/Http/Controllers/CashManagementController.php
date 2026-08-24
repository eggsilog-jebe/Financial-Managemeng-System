<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\BankAccount;
use App\Models\FundTransfer;
use App\Models\BankReconciliation;
use Illuminate\Contracts\View\View;

final class CashManagementController extends Controller
{
    public function bankAccounts(): View
    {
        $bankAccounts = BankAccount::orderBy('name')->get();
        return view('cash.bank-accounts', compact('bankAccounts'));
    }

    public function fundTransfers(): View
    {
        $transfers = FundTransfer::latest('transfer_date')->get();
        $totalTransferVolume = $transfers->sum('amount');
        return view('cash.fund-transfers', compact('transfers', 'totalTransferVolume'));
    }

    public function bankReconciliation(): View
    {
        $reconciliations = BankReconciliation::with('bankAccount')->latest('statement_date')->get();
        $totalBookBalance = BankAccount::where('status', 'Active')->sum('balance');
        return view('cash.bank-reconciliation', compact('reconciliations', 'totalBookBalance'));
    }

    public function cashFlowForecasting(): View
    {
        $totalCash = BankAccount::where('status', 'Active')->sum('balance');
        return view('cash.cash-flow-forecasting', compact('totalCash'));
    }

    public function liquidityManagement(): View
    {
        $totalCash = BankAccount::where('status', 'Active')->sum('balance');
        return view('cash.liquidity-management', compact('totalCash'));
    }
}
