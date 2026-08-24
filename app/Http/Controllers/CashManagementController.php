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
        return view('cash.fund-transfers', compact('transfers'));
    }

    public function bankReconciliation(): View
    {
        $reconciliations = BankReconciliation::with('bankAccount')->latest('statement_date')->get();
        return view('cash.bank-reconciliation', compact('reconciliations'));
    }

    public function cashFlowForecasting(): View
    {
        return view('cash.cash-flow-forecasting');
    }

    public function liquidityManagement(): View
    {
        return view('cash.liquidity-management');
    }
}
