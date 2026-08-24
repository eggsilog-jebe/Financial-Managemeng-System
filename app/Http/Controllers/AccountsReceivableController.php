<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\PatientAccount;
use App\Models\Invoice;
use Illuminate\Contracts\View\View;

final class AccountsReceivableController extends Controller
{
    public function patientAccounts(): View
    {
        $accounts              = PatientAccount::latest()->get();
        $totalReceivable       = $accounts->sum('current_balance');
        $hmoGuarantees         = $accounts->where('hmo_provider', '!=', null)->sum('current_balance');

        return view('accounts-receivable.patient-accounts', compact(
            'accounts',
            'totalReceivable',
            'hmoGuarantees',
        ));
    }

    public function invoicingBilling(): View
    {
        $invoices     = Invoice::with('patientAccount')->latest('invoice_date')->get();
        $totalBilled  = $invoices->sum('total_amount');
        $totalPending = $invoices->whereIn('status', ['UNPAID', 'PARTIAL'])->sum('patient_payable');
        $totalPaid    = $invoices->where('status', 'PAID')->sum('total_amount');

        return view('accounts-receivable.invoicing-billing', compact(
            'invoices',
            'totalBilled',
            'totalPending',
            'totalPaid',
        ));
    }

    public function receivableAging(): View
    {
        $invoices = Invoice::with('patientAccount')
            ->whereIn('status', ['UNPAID', 'PARTIAL'])
            ->latest('invoice_date')
            ->get();

        $now = now();

        $current    = $invoices->filter(fn ($i) => $i->invoice_date >= $now->copy()->subDays(30))->sum('patient_payable');
        $days30     = $invoices->filter(fn ($i) => $i->invoice_date < $now->copy()->subDays(30) && $i->invoice_date >= $now->copy()->subDays(60))->sum('patient_payable');
        $days60     = $invoices->filter(fn ($i) => $i->invoice_date < $now->copy()->subDays(60) && $i->invoice_date >= $now->copy()->subDays(90))->sum('patient_payable');
        $days90Plus = $invoices->filter(fn ($i) => $i->invoice_date < $now->copy()->subDays(90))->sum('patient_payable');

        return view('accounts-receivable.receivable-aging', compact(
            'invoices',
            'current',
            'days30',
            'days60',
            'days90Plus',
        ));
    }

    public function creditNotes(): View
    {
        // Credit notes are invoices in CREDIT status or with a credit adjustment
        $creditNotes = Invoice::with('patientAccount')
            ->where('status', 'CREDIT')
            ->latest('invoice_date')
            ->get();

        $totalCreditValue = $creditNotes->sum('patient_payable');

        return view('accounts-receivable.credit-notes', compact('creditNotes', 'totalCreditValue'));
    }

    public function customerStatements(): View
    {
        $accounts = PatientAccount::with('invoices')->latest()->get();

        return view('accounts-receivable.customer-statements', compact('accounts'));
    }
}
