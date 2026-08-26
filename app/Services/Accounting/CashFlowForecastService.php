<?php

declare(strict_types=1);

namespace App\Services\Accounting;

use App\Models\BankAccount;
use App\Models\HmoClaim;
use App\Models\Invoice;
use App\Models\PayrollRun;
use App\Models\PurchaseBill;

final class CashFlowForecastService
{
    /**
     * Compute real-time 30-day Cash Flow Forecast metrics & upcoming schedules.
     */
    public function getForecastData(int $horizonDays = 30): array
    {
        $cutoffDate = now()->addDays($horizonDays)->toDateString();

        // 1. Available Liquid Cash (Active Bank Accounts)
        $bankAccounts = BankAccount::where('status', 'Active')->where('is_active', true)->get();
        $availableCash = '0.0000';
        foreach ($bankAccounts as $acc) {
            $availableCash = bcadd($availableCash, (string) $acc->balance, 4);
        }

        // 2. Projected Inflows
        // Unpaid Patient Copays
        $patientInvoices = Invoice::with('patientAccount')
            ->where('patient_payable', '>', 0)
            ->where('status', '!=', 'SETTLED')
            ->whereDate('invoice_date', '<=', $cutoffDate)
            ->get();

        $patientInflow = '0.0000';
        foreach ($patientInvoices as $inv) {
            $patientInflow = bcadd($patientInflow, (string) $inv->patient_payable, 4);
        }

        // Open HMO Claims
        $hmoClaims = HmoClaim::with(['invoice.patientAccount'])
            ->whereIn('status', ['SUBMITTED', 'APPROVED', 'PENDING_BILLING'])
            ->whereDate('created_at', '<=', $cutoffDate)
            ->get();

        $hmoInflow = '0.0000';
        foreach ($hmoClaims as $claim) {
            $claimAmt = (string) ($claim->approved_limit > 0 ? $claim->approved_limit : $claim->claimed_amount);
            $hmoInflow = bcadd($hmoInflow, $claimAmt, 4);
        }

        $totalProjectedInflows = bcadd($patientInflow, $hmoInflow, 4);

        // 3. Committed Outflows
        // Unpaid Purchase Bills (AP)
        $purchaseBills = PurchaseBill::with('vendor')
            ->whereIn('status', ['UNPAID', 'PARTIAL', 'APPROVED', 'OVERDUE'])
            ->whereDate('due_date', '<=', $cutoffDate)
            ->get();

        $apOutflow = '0.0000';
        foreach ($purchaseBills as $pb) {
            $dueAmt = bcsub((string) $pb->total_amount, (string) $pb->paid_amount, 4);
            $apOutflow = bcadd($apOutflow, $dueAmt, 4);
        }

        // Scheduled Payroll Runs
        $payrollRuns = PayrollRun::whereIn('status', ['DRAFT', 'SUBMITTED', 'AUDITED', 'APPROVED'])
            ->whereDate('payout_date', '<=', $cutoffDate)
            ->get();

        $payrollOutflow = '0.0000';
        foreach ($payrollRuns as $pr) {
            $payrollOutflow = bcadd($payrollOutflow, (string) ($pr->total_net_pay ?? 0), 4);
        }

        $totalCommittedOutflows = bcadd($apOutflow, $payrollOutflow, 4);

        // 4. Net Cash Position
        $netOperatingPosition = bcsub($totalProjectedInflows, $totalCommittedOutflows, 4);
        $projectedEndingCash = bcadd($availableCash, $netOperatingPosition, 4);

        // 5. Build Chronological Cash Events Schedule
        $events = [];

        foreach ($patientInvoices as $pi) {
            $events[] = [
                'type'        => 'INFLOW',
                'category'    => 'Patient Copay',
                'reference'   => $pi->invoice_number,
                'counterparty'=> $pi->patientAccount?->full_name ?? 'Hospital Patient',
                'due_date'    => $pi->invoice_date ? $pi->invoice_date->format('Y-m-d') : date('Y-m-d'),
                'amount'      => (string) $pi->patient_payable,
                'status'      => $pi->status,
            ];
        }

        foreach ($hmoClaims as $hc) {
            $events[] = [
                'type'        => 'INFLOW',
                'category'    => 'HMO Claim Reimbursement',
                'reference'   => $hc->loa_number ?: ('HMO-CLAIM-' . $hc->id),
                'counterparty'=> $hc->hmo_provider ?? 'HMO Insurance Carrier',
                'due_date'    => $hc->created_at ? $hc->created_at->format('Y-m-d') : date('Y-m-d'),
                'amount'      => (string) ($hc->approved_limit > 0 ? $hc->approved_limit : $hc->claimed_amount),
                'status'      => $hc->status,
            ];
        }

        foreach ($purchaseBills as $pb) {
            $events[] = [
                'type'        => 'OUTFLOW',
                'category'    => 'Vendor AP Purchase Bill',
                'reference'   => $pb->bill_number,
                'counterparty'=> $pb->vendor?->name ?? 'Medical Supply Vendor',
                'due_date'    => $pb->due_date ? $pb->due_date->format('Y-m-d') : date('Y-m-d'),
                'amount'      => bcsub((string) $pb->total_amount, (string) $pb->paid_amount, 4),
                'status'      => $pb->status,
            ];
        }

        foreach ($payrollRuns as $pr) {
            $events[] = [
                'type'        => 'OUTFLOW',
                'category'    => 'Hospital Staff Payroll',
                'reference'   => $pr->payroll_run_number,
                'counterparty'=> 'Hospital Medical Staff & Nurses',
                'due_date'    => $pr->payout_date ? $pr->payout_date->format('Y-m-d') : ($pr->cutoff_end ? $pr->cutoff_end->format('Y-m-d') : date('Y-m-d')),
                'amount'      => (string) ($pr->total_net_pay ?? 0),
                'status'      => $pr->status,
            ];
        }

        // Sort events chronologically by due date
        usort($events, fn ($a, $b) => strcmp($a['due_date'], $b['due_date']));

        return [
            'horizon_days'             => $horizonDays,
            'available_cash'           => $availableCash,
            'patient_inflows'          => $patientInflow,
            'hmo_inflows'              => $hmoInflow,
            'total_projected_inflows'  => $totalProjectedInflows,
            'ap_outflows'              => $apOutflow,
            'payroll_outflows'         => $payrollOutflow,
            'total_committed_outflows' => $totalCommittedOutflows,
            'net_operating_position'   => $netOperatingPosition,
            'projected_ending_cash'    => $projectedEndingCash,
            'events'                   => $events,
            'bank_accounts'            => $bankAccounts,
        ];
    }
}
