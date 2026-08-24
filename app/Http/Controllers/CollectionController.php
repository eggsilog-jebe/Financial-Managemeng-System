<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\OfficialReceipt;
use App\Models\CashierShift;
use App\Models\BankDeposit;
use App\Models\BankAccount;
use Illuminate\Contracts\View\View;

final class CollectionController extends Controller
{
    public function paymentReceipts(): View
    {
        $payments = Payment::with(['patientAccount', 'invoice', 'officialReceipt'])->latest('payment_date')->get();

        $totalCollected = $payments->sum('amount');
        $cashCollected = $payments->where('payment_method', 'CASH')->sum('amount');
        $digitalCollected = $payments->where('payment_method', '!=', 'CASH')->sum('amount');

        return view('collection.payment-receipts', compact(
            'payments',
            'totalCollected',
            'cashCollected',
            'digitalCollected'
        ));
    }

    public function cashierDesk(): View
    {
        $shifts = CashierShift::with(['cashier', 'payments'])->latest('opened_at')->get();
        $terminals = $shifts;
        $activeShift = CashierShift::where('status', 'OPEN')->first();
        
        $todayPayments = Payment::whereDate('payment_date', today())->get();
        $todayTotal = $todayPayments->sum('amount');
        $cashReceipts = $todayPayments->where('payment_method', 'CASH')->sum('amount');

        return view('collection.cashier-desk', compact(
            'shifts',
            'terminals',
            'activeShift',
            'todayTotal',
            'cashReceipts'
        ));
    }

    public function depositSlips(): View
    {
        $deposits = BankDeposit::with(['bankAccount', 'cashierShift.cashier'])->latest('deposit_date')->get();
        $slips = $deposits;
        $totalDeposits = $deposits->sum('total_deposited');

        return view('collection.deposit-slips', compact('deposits', 'slips', 'totalDeposits'));
    }

    public function bankDeposits(): View
    {
        $deposits = BankDeposit::with(['bankAccount', 'cashierShift.cashier'])->latest('deposit_date')->get();
        $totalDeposits = $deposits->sum('total_deposited');
        $bankAccounts = BankAccount::where('status', 'Active')->get();

        return view('collection.bank-deposits', compact('deposits', 'totalDeposits', 'bankAccounts'));
    }

    public function paymentGatewayLogs(): View
    {
        $logs = Payment::with(['patientAccount', 'invoice', 'officialReceipt'])
            ->where('payment_method', '!=', 'CASH')
            ->latest('payment_date')
            ->get();
            
        $gateways = $logs;
        $totalOnline = $logs->sum('amount');

        return view('collection.payment-gateway-logs', compact('logs', 'gateways', 'totalOnline'));
    }
}
