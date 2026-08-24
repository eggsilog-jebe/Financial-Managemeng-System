<?php

declare(strict_types=1);

namespace App\Services\Accounting;

use App\DTOs\JournalEntryData;
use App\DTOs\JournalLineData;
use App\DTOs\PayrollRunIngestionData;
use App\Models\Account;
use App\Models\BankAccount;
use App\Models\DisbursementVoucher;
use App\Models\PayrollItem;
use App\Models\PayrollRun;
use Illuminate\Support\Facades\DB;

final class PayrollIntegrationService
{
    public function __construct(
        private readonly JournalEntryService $journalEntryService
    ) {}

    /**
     * Ingest hospital HRMS payroll register, calculate Philippine statutory contributions
     * (SSS, PhilHealth, Pag-IBIG & BIR 1601-C), and automatically post balanced Double-Entry payroll journal.
     */
    public function ingestAndDisbursePayroll(PayrollRunIngestionData $data): PayrollRun
    {
        return DB::transaction(function () use ($data): PayrollRun {
            $bank = BankAccount::findOrFail($data->disbursementBankAccountId);

            $totalGross = '0.0000';
            $totalSssEe = '0.0000';
            $totalSssEr = '0.0000';
            $totalPhicEe = '0.0000';
            $totalPhicEr = '0.0000';
            $totalHdmfEe = '0.0000';
            $totalHdmfEr = '0.0000';
            $totalTax1601c = '0.0000';
            $totalNetPay = '0.0000';

            $calculatedEmployees = [];

            foreach ($data->employees as $emp) {
                $gross = $emp->getGrossPay();
                $totalGross = bcadd($totalGross, $gross, 4);

                // Philippine Statutory Contributions Formula Matrix:
                // SSS (approx 4.5% EE, 9.5% ER capped at 30k base)
                $sssBase = bccomp($gross, '30000.0000', 4) > 0 ? '30000.0000' : $gross;
                $sssEe = bcmul($sssBase, '0.0450', 4);
                $sssEr = bcmul($sssBase, '0.0950', 4);

                // PhilHealth (5% total split 50/50 -> 2.5% EE, 2.5% ER)
                $phicEe = bcmul($gross, '0.0250', 4);
                $phicEr = bcmul($gross, '0.0250', 4);

                // Pag-IBIG / HDMF (Standard ₱200 EE, ₱200 ER)
                $hdmfEe = '200.0000';
                $hdmfEr = '200.0000';

                // BIR Form 1601-C TRAIN Law Withholding Tax on Compensation (taxable base net of mandatory statutory EE share)
                $statutoryEe = bcadd(bcadd($sssEe, $phicEe, 4), $hdmfEe, 4);
                $taxableIncome = bcsub($gross, $statutoryEe, 4);

                $wTax = '0.0000';
                if (bccomp($taxableIncome, '20833.0000', 4) > 0) { // Monthly ₱250k annual threshold
                    $excess = bcsub($taxableIncome, '20833.0000', 4);
                    $wTax = bcmul($excess, '0.1500', 4); // Standard 15% bracket
                }

                $totalEeDeductions = bcadd($statutoryEe, $wTax, 4);
                $netPay = bcsub($gross, $totalEeDeductions, 4);

                // Accumulate totals
                $totalSssEe = bcadd($totalSssEe, $sssEe, 4);
                $totalSssEr = bcadd($totalSssEr, $sssEr, 4);
                $totalPhicEe = bcadd($totalPhicEe, $phicEe, 4);
                $totalPhicEr = bcadd($totalPhicEr, $phicEr, 4);
                $totalHdmfEe = bcadd($totalHdmfEe, $hdmfEe, 4);
                $totalHdmfEr = bcadd($totalHdmfEr, $hdmfEr, 4);
                $totalTax1601c = bcadd($totalTax1601c, $wTax, 4);
                $totalNetPay = bcadd($totalNetPay, $netPay, 4);

                $calculatedEmployees[] = [
                    'data'     => $emp,
                    'gross'    => $gross,
                    'sssEe'    => $sssEe,
                    'sssEr'    => $sssEr,
                    'phicEe'   => $phicEe,
                    'phicEr'   => $phicEr,
                    'hdmfEe'   => $hdmfEe,
                    'hdmfEr'   => $hdmfEr,
                    'wTax'     => $wTax,
                    'netPay'   => $netPay,
                ];
            }

            $totalStatutoryDeductions = bcadd(
                bcadd(bcadd($totalSssEe, $totalPhicEe, 4), $totalHdmfEe, 4),
                $totalTax1601c,
                4
            );

            // 1. Create Payroll Run Record
            $runNumber = 'PAYROLL-' . date('Ymd', strtotime($data->payoutDate)) . '-' . strtoupper(bin2hex(random_bytes(2)));
            $payrollRun = PayrollRun::create([
                'payroll_run_number'          => $runNumber,
                'cutoff_start'                => $data->cutoffStart,
                'cutoff_end'                  => $data->cutoffEnd,
                'payout_date'                 => $data->payoutDate,
                'employee_count'              => count($data->employees),
                'total_gross_pay'             => $totalGross,
                'total_sss_employee'          => $totalSssEe,
                'total_sss_employer'          => $totalSssEr,
                'total_philhealth_employee'   => $totalPhicEe,
                'total_philhealth_employer'   => $totalPhicEr,
                'total_pagibig_employee'      => $totalHdmfEe,
                'total_pagibig_employer'      => $totalHdmfEr,
                'total_withholding_tax_1601c' => $totalTax1601c,
                'total_statutory_deductions'  => $totalStatutoryDeductions,
                'total_net_pay'               => $totalNetPay,
                'status'                      => 'DISBURSED',
            ]);

            // 2. Persist Individual Payroll Items
            foreach ($calculatedEmployees as $c) {
                PayrollItem::create([
                    'payroll_run_id'            => $payrollRun->id,
                    'employee_id_number'        => $c['data']->employeeIdNumber,
                    'employee_name'             => $c['data']->employeeName,
                    'department'                => $c['data']->department,
                    'tin'                       => $c['data']->tin,
                    'sss_number'                => $c['data']->sssNumber,
                    'philhealth_number'         => $c['data']->philhealthNumber,
                    'pagibig_number'            => $c['data']->pagibigNumber,
                    'bank_account_number'       => $c['data']->bankAccountNumber,
                    'basic_salary'              => $c['data']->basicSalary,
                    'overtime_pay'              => $c['data']->overtimePay,
                    'allowances'                => $c['data']->allowances,
                    'gross_pay'                 => $c['gross'],
                    'sss_employee_share'        => $c['sssEe'],
                    'sss_employer_share'        => $c['sssEr'],
                    'philhealth_employee_share' => $c['phicEe'],
                    'philhealth_employer_share' => $c['phicEr'],
                    'pagibig_employee_share'    => $c['hdmfEe'],
                    'pagibig_employer_share'    => $c['hdmfEr'],
                    'withholding_tax'           => $c['wTax'],
                    'net_pay'                   => $c['netPay'],
                ]);
            }

            // 3. Create Automated Disbursement Voucher for PESONet Electronic Bank Release
            $voucherNumber = 'DV-' . date('Ymd') . '-' . strtoupper(bin2hex(random_bytes(3)));
            $voucher = DisbursementVoucher::create([
                'voucher_number'       => $voucherNumber,
                'payroll_run_id'       => $payrollRun->id,
                'bank_account_id'      => $bank->id,
                'voucher_date'         => $data->payoutDate,
                'payee_name'           => 'Hospital Medical & Nursing Staff (PESONet Payroll Batch)',
                'gross_amount'         => $totalGross,
                'withheld_tax_amount'  => $totalStatutoryDeductions,
                'net_disbursed_amount' => $totalNetPay,
                'payment_method'       => 'PESONET_EFT',
                'check_or_eft_ref'     => 'PESONET-' . $payrollRun->payroll_run_number,
                'status'               => 'RELEASED',
                'approved_by'          => auth()->id(),
                'released_at'          => now(),
            ]);

            // Deduct Net Payroll from Bank Account
            $bank->decrement('balance', (float) $totalNetPay);

            // 4. Post Double-Entry General Ledger Payroll Journal
            $this->postPayrollDoubleEntry(
                $payrollRun,
                $data->payoutDate,
                $totalGross,
                $totalSssEe,
                $totalSssEr,
                $totalPhicEe,
                $totalPhicEr,
                $totalHdmfEe,
                $totalHdmfEr,
                $totalTax1601c,
                $totalNetPay
            );

            return $payrollRun->loadMissing(['items', 'disbursementVoucher']);
        });
    }

    private function postPayrollDoubleEntry(
        PayrollRun $run,
        string $payoutDate,
        string $grossSalaries,
        string $sssEe,
        string $sssEr,
        string $phicEe,
        string $phicEr,
        string $hdmfEe,
        string $hdmfEr,
        string $tax1601c,
        string $netPay
    ): void {
        // Accounts Definition
        $salariesExpenseAcc = Account::firstOrCreate(['code' => '5030'], ['name' => 'Salaries & Wages Expense - Hospital Staff', 'category' => 'EXPENSE', 'normal_balance' => 'DEBIT']);
        $employerStatAcc    = Account::firstOrCreate(['code' => '5031'], ['name' => 'Employer Statutory Contributions Expense', 'category' => 'EXPENSE', 'normal_balance' => 'DEBIT']);
        $taxPayable1601cAcc = Account::firstOrCreate(['code' => '2040'], ['name' => 'Withholding Tax Payable - Compensation (BIR 1601-C)', 'category' => 'LIABILITY', 'normal_balance' => 'CREDIT']);
        $sssPayableAcc      = Account::firstOrCreate(['code' => '2041'], ['name' => 'SSS Premiums & EC Payable', 'category' => 'LIABILITY', 'normal_balance' => 'CREDIT']);
        $phicPayableAcc     = Account::firstOrCreate(['code' => '2042'], ['name' => 'PhilHealth Premiums Payable', 'category' => 'LIABILITY', 'normal_balance' => 'CREDIT']);
        $hdmfPayableAcc     = Account::firstOrCreate(['code' => '2043'], ['name' => 'Pag-IBIG / HDMF Premiums Payable', 'category' => 'LIABILITY', 'normal_balance' => 'CREDIT']);
        $cashInBankAcc      = Account::firstOrCreate(['code' => '1020'], ['name' => 'Operating Bank Account - Metrobank', 'category' => 'ASSET', 'normal_balance' => 'DEBIT']);

        $totalEmployerExpense = bcadd(bcadd($sssEr, $phicEr, 4), $hdmfEr, 4);
        $totalSssCombined = bcadd($sssEe, $sssEr, 4);
        $totalPhicCombined = bcadd($phicEe, $phicEr, 4);
        $totalHdmfCombined = bcadd($hdmfEe, $hdmfEr, 4);

        $journalLines = [
            // DR: Gross Salaries Expense
            new JournalLineData(
                accountId: $salariesExpenseAcc->id,
                debit: $grossSalaries,
                credit: '0.0000',
                memo: "Gross salaries for payroll batch {$run->payroll_run_number}"
            ),
            // DR: Employer Statutory Expense
            new JournalLineData(
                accountId: $employerStatAcc->id,
                debit: $totalEmployerExpense,
                credit: '0.0000',
                memo: "Hospital employer share (SSS, PHIC, HDMF) for {$run->payroll_run_number}"
            ),
            // CR: BIR Form 1601-C Compensation Withholding Tax
            new JournalLineData(
                accountId: $taxPayable1601cAcc->id,
                debit: '0.0000',
                credit: $tax1601c,
                memo: "BIR Form 1601-C tax withheld from compensation on {$run->payroll_run_number}"
            ),
            // CR: Total SSS Payable (EE + ER)
            new JournalLineData(
                accountId: $sssPayableAcc->id,
                debit: '0.0000',
                credit: $totalSssCombined,
                memo: "SSS premiums payable (EE ₱{$sssEe} + ER ₱{$sssEr})"
            ),
            // CR: Total PhilHealth Payable (EE + ER)
            new JournalLineData(
                accountId: $phicPayableAcc->id,
                debit: '0.0000',
                credit: $totalPhicCombined,
                memo: "PhilHealth premiums payable (EE ₱{$phicEe} + ER ₱{$phicEr})"
            ),
            // CR: Total Pag-IBIG Payable (EE + ER)
            new JournalLineData(
                accountId: $hdmfPayableAcc->id,
                debit: '0.0000',
                credit: $totalHdmfCombined,
                memo: "Pag-IBIG premiums payable (EE ₱{$hdmfEe} + ER ₱{$hdmfEr})"
            ),
            // CR: Cash in Bank (Net Payroll Release)
            new JournalLineData(
                accountId: $cashInBankAcc->id,
                debit: '0.0000',
                credit: $netPay,
                memo: "Net payroll disbursement via PESONet batch on {$run->payroll_run_number}"
            ),
        ];

        $entryData = new JournalEntryData(
            referenceNumber: $run->payroll_run_number,
            entryDate: $payoutDate,
            description: "Payroll disbursement & Philippine statutory accrual for {$run->payroll_run_number}",
            type: 'GENERAL',
            postedBy: auth()->id(),
            lines: $journalLines
        );

        $this->journalEntryService->createAndPostEntry($entryData);
    }
}
