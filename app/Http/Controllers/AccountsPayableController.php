<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Vendor;
use App\Models\PurchaseBill;
use App\Models\Invoice;
use Illuminate\Contracts\View\View;

final class AccountsPayableController extends Controller
{
    public function vendors(): View
    {
        $vendors            = Vendor::with('purchaseBills')->orderBy('name')->get();
        $totalActiveVendors = $vendors->where('status', 'Active')->count();
        $totalApLiability   = PurchaseBill::whereIn('status', ['UNPAID', 'PARTIAL'])->sum('total_amount');
        // ewt_withheld column not yet in schema — placeholder until migration adds it
        $totalEwt           = 0;

        return view('accounts-payable.vendor-management', compact(
            'vendors',
            'totalActiveVendors',
            'totalApLiability',
            'totalEwt',
        ));
    }

    public function invoices(): View
    {
        $invoices       = Invoice::with('patientAccount')->latest('invoice_date')->get();
        $totalBilled    = $invoices->sum('total_amount');
        $totalPending   = $invoices->whereIn('status', ['UNPAID', 'PARTIAL'])->sum('patient_payable');

        return view('accounts-payable.invoices-vouchers', compact(
            'invoices',
            'totalBilled',
            'totalPending',
        ));
    }

    public function purchaseBills(): View
    {
        $bills        = PurchaseBill::with('vendor')->latest('bill_date')->get();
        $totalUnpaid  = $bills->whereIn('status', ['UNPAID'])->sum('total_amount');
        $totalPaid    = $bills->where('status', 'PAID')->sum('paid_amount');
        $pendingCount = $bills->where('status', 'PENDING_APPROVAL')->count();

        return view('accounts-payable.purchase-bills', compact(
            'bills',
            'totalUnpaid',
            'totalPaid',
            'pendingCount',
        ));
    }

    public function payableAging(): View
    {
        $bills = PurchaseBill::with('vendor')
            ->whereIn('status', ['UNPAID', 'PARTIAL'])
            ->latest('due_date')
            ->get();

        $now = now();

        $current   = $bills->filter(fn ($b) => $b->due_date >= $now)->sum('total_amount');
        $days30    = $bills->filter(fn ($b) => $b->due_date < $now && $b->due_date >= $now->copy()->subDays(30))->sum('total_amount');
        $days60    = $bills->filter(fn ($b) => $b->due_date < $now->copy()->subDays(30) && $b->due_date >= $now->copy()->subDays(60))->sum('total_amount');
        $days90Plus = $bills->filter(fn ($b) => $b->due_date < $now->copy()->subDays(60))->sum('total_amount');

        return view('accounts-payable.payable-aging', compact(
            'bills',
            'current',
            'days30',
            'days60',
            'days90Plus',
        ));
    }

    public function apApprovals(): View
    {
        $bills = PurchaseBill::with('vendor')
            ->where('status', 'PENDING_APPROVAL')
            ->latest('bill_date')
            ->get();

        return view('accounts-payable.ap-payment-approvals', compact('bills'));
    }
}
