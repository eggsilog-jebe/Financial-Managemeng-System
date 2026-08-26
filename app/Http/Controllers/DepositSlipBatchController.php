<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\BankAccount;
use App\Models\BankDeposit;
use App\Models\CashierShift;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

final class DepositSlipBatchController extends Controller
{
    public function index(): View
    {
        $deposits = BankDeposit::with(['bankAccount', 'cashierShift.cashier'])
            ->latest('deposit_date')
            ->get();
        $slips = $deposits;
        $totalDeposits = $deposits->sum('total_deposited');

        // Closed shifts ready for bank deposit / handover
        $closedShifts = CashierShift::with(['cashier', 'payments'])
            ->where('status', 'CLOSED')
            ->latest('closed_at')
            ->get();

        $bankAccounts = BankAccount::where('status', 'Active')->get();

        $viewName = view()->exists('accounting.collection.deposit-slips.index')
            ? 'accounting.collection.deposit-slips.index'
            : 'collection.deposit-slips';

        return view($viewName, compact(
            'deposits',
            'slips',
            'totalDeposits',
            'closedShifts',
            'bankAccounts'
        ));
    }
}
