<?php

declare(strict_types=1);

namespace App\Services\Accounting\Reporting;

use App\Models\BankAccount;
use App\Models\DisbursementVoucher;
use App\Models\Payment;
use App\Models\PurchaseBill;
use Illuminate\Support\Facades\DB;

final class CashFlowService
{
    /**
     * Compute PAS 7-Compliant Statement of Cash Flows for a given date range.
     */
    public function getCashFlowData(?string $dateFrom = null, ?string $dateTo = null): array
    {
        $from = $dateFrom ?: date('Y-01-01');
        $to = $dateTo ?: date('Y-m-d');

        // 1. Operating Activities:
        // Collections received from patients and HMOs
        $patientCollections = Payment::whereBetween('payment_date', [$from, $to])
            ->sum('amount');
        $operatingReceipts = (string) $patientCollections;

        // Cash disbursements to suppliers (AP)
        $supplierDisbursements = PurchaseBill::whereBetween('bill_date', [$from, $to])
            ->sum('paid_amount');
        $supplierCash = (string) $supplierDisbursements;

        // Cash disbursements for payroll & personnel
        $payrollDisbursements = DisbursementVoucher::whereBetween('voucher_date', [$from, $to])
            ->whereNotNull('payroll_run_id')
            ->whereIn('status', ['APPROVED', 'RELEASED', 'CLEARED'])
            ->sum('net_disbursed_amount');
        $payrollCash = (string) $payrollDisbursements;

        // Direct Clinical / Operating expenses paid
        $directOpEx = DisbursementVoucher::whereBetween('voucher_date', [$from, $to])
            ->whereNull('payroll_run_id')
            ->whereNull('purchase_bill_id')
            ->whereIn('status', ['APPROVED', 'RELEASED', 'CLEARED'])
            ->sum('net_disbursed_amount');
        $directOpExCash = (string) $directOpEx;

        $totalOperatingOutflows = bcadd(bcadd($supplierCash, $payrollCash, 4), $directOpExCash, 4);
        $netOperatingCash = bcsub($operatingReceipts, $totalOperatingOutflows, 4);

        // 2. Investing Activities (CapEx, Medical Equipment, Facility Improvements)
        $investingLines = DB::table('journal_entry_lines')
            ->join('journal_entries', 'journal_entry_lines.journal_entry_id', '=', 'journal_entries.id')
            ->join('accounts', 'journal_entry_lines.account_id', '=', 'accounts.id')
            ->where('journal_entries.status', 'POSTED')
            ->whereBetween('journal_entries.entry_date', [$from, $to])
            ->where('accounts.category', 'ASSET')
            ->where(function ($q) {
                $q->where('accounts.name', 'like', '%Equipment%')
                  ->orWhere('accounts.name', 'like', '%Property%')
                  ->orWhere('accounts.name', 'like', '%Building%')
                  ->orWhere('accounts.code', 'like', '15%');
            })
            ->sum(DB::raw('journal_entry_lines.debit - journal_entry_lines.credit'));

        $capexOutflow = (string) $investingLines;
        $netInvestingCash = bcsub('0.0000', $capexOutflow, 4);

        // 3. Financing Activities (Equity contributions, Debt financing)
        $financingLines = DB::table('journal_entry_lines')
            ->join('journal_entries', 'journal_entry_lines.journal_entry_id', '=', 'journal_entries.id')
            ->join('accounts', 'journal_entry_lines.account_id', '=', 'accounts.id')
            ->where('journal_entries.status', 'POSTED')
            ->whereBetween('journal_entries.entry_date', [$from, $to])
            ->whereIn('accounts.category', ['LIABILITY', 'EQUITY'])
            ->where(function ($q) {
                $q->where('accounts.name', 'like', '%Loan%')
                  ->orWhere('accounts.name', 'like', '%Capital%')
                  ->orWhere('accounts.name', 'like', '%Reserve%')
                  ->orWhere('accounts.code', 'like', '25%')
                  ->orWhere('accounts.code', 'like', '30%');
            })
            ->sum(DB::raw('journal_entry_lines.credit - journal_entry_lines.debit'));

        $netFinancingCash = (string) $financingLines;

        // 4. Net Increase / Decrease in Cash
        $netCashFlow = bcadd(bcadd($netOperatingCash, $netInvestingCash, 4), $netFinancingCash, 4);

        // 5. Cash and Cash Equivalents at End of Period
        $bankAccounts = BankAccount::where('status', 'Active')->where('is_active', true)->get();
        $closingCash = '0.0000';
        foreach ($bankAccounts as $acc) {
            $closingCash = bcadd($closingCash, (string) $acc->balance, 4);
        }

        $openingCash = bcsub($closingCash, $netCashFlow, 4);

        return [
            'date_from'              => $from,
            'date_to'                => $to,
            'operating_receipts'     => $operatingReceipts,
            'operatingCash'          => (float) $operatingReceipts,
            'supplier_disbursements' => $supplierCash,
            'supplierCash'           => (float) $supplierCash,
            'payroll_disbursements'  => $payrollCash,
            'payrollCash'            => (float) $payrollCash,
            'direct_opex_cash'       => $directOpExCash,
            'net_operating_cash'     => $netOperatingCash,
            'operatingNet'           => (float) $netOperatingCash,
            'capex_outflows'         => $capexOutflow,
            'net_investing_cash'     => $netInvestingCash,
            'investingNet'           => (float) $netInvestingCash,
            'net_financing_cash'     => $netFinancingCash,
            'financingNet'           => (float) $netFinancingCash,
            'net_cash_flow'          => $netCashFlow,
            'netCashFlow'            => (float) $netCashFlow,
            'opening_cash'           => $openingCash,
            'closing_cash'           => $closingCash,
            'bankBalance'            => (float) $closingCash,
        ];
    }
}
