<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\OfficialReceipt;
use App\Models\PaymentReceipt;
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
        $receipts         = PaymentReceipt::whereDate('receipt_date', today())->latest()->get();
        $todayTotal       = $receipts->sum('amount_paid');
        $cashReceipts     = $receipts->where('payment_method', 'Cash')->sum('amount_paid');

        return view('collection.cashier-desk', compact(
            'receipts',
            'todayTotal',
            'cashReceipts',
        ));
    }

    public function depositSlips(): View
    {
        $deposits = PaymentReceipt::latest('receipt_date')->get();
        $totalDeposits = $deposits->sum('amount_paid');

        return view('collection.deposit-slips', compact('deposits', 'totalDeposits'));
    }

    public function bankDeposits(): View
    {
        $deposits     = PaymentReceipt::latest('receipt_date')->get();
        $totalDeposits = $deposits->sum('amount_paid');

        return view('collection.bank-deposits', compact('deposits', 'totalDeposits'));
    }

    public function paymentGatewayLogs(): View
    {
        $logs         = PaymentReceipt::where('payment_method', '!=', 'Cash')->latest('receipt_date')->get();
        $totalOnline  = $logs->sum('amount_paid');

        return view('collection.payment-gateway-logs', compact('logs', 'totalOnline'));
    }
}
