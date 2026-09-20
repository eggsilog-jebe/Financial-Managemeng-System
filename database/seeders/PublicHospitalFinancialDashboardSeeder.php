<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Account;
use App\Models\BankAccount;
use App\Models\BankDeposit;
use App\Models\BudgetAllocation;
use App\Models\CashierShift;
use App\Models\Invoice;
use App\Models\PatientAccount;
use App\Models\Payment;
use App\Models\PhilhealthClaim;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

final class PublicHospitalFinancialDashboardSeeder extends Seeder
{
    public function run(): void
    {
        $cashier = User::where('role', 'Cashier')->first() ?? User::first();
        $glCashInBank = Account::where('code', '1020')->first();

        // 1. Seed Land Bank of the Philippines (AGDB Depository Account)
        $lbpAccount = BankAccount::firstOrCreate(
            ['account_number' => 'LBP-DEPO-1020-01'],
            [
                'name'            => 'Land Bank of the Philippines - AGDB Depository',
                'bank_name'       => 'Land Bank of the Philippines (LBP)',
                'account_number'  => 'LBP-DEPO-1020-01',
                'gl_code'         => '1020',
                'gl_account_id'   => $glCashInBank?->id,
                'purpose'         => 'National Government Depository & Daily Intact Collections',
                'currency'        => 'PHP',
                'opening_balance' => 12500000.0000,
                'balance'         => 12535000.0000,
                'minimum_balance' => 100000.0000,
                'status'          => 'Active',
                'is_active'       => true,
            ]
        );

        // 2. Departmental Budget Allocations (FY 2026) with realistic burn-rates
        $budgetItems = [
            [
                'department'        => 'Department of Surgery',
                'department_code'   => 'SURG-01',
                'category'          => 'Clinical Operations',
                'department_head'   => 'Chief of Surgery',
                'fiscal_year'       => '2026',
                'allocated_amount'  => '15000000.0000',
                'spent_amount'      => '7250000.0000',
                'remaining_balance' => '7750000.0000',
                'status'            => 'Approved',
                'notes'             => 'Major surgical suites, anesthesia gases, and operating theater equipment maintenance.',
            ],
            [
                'department'        => 'Pharmacy & Medical Supplies',
                'department_code'   => 'PHARM-01',
                'category'          => 'Pharmaceuticals & Inventory',
                'department_head'   => 'Chief Pharmacist',
                'fiscal_year'       => '2026',
                'allocated_amount'  => '25000000.0000',
                'spent_amount'      => '22100000.0000', // 88.4% Burn Rate -> Critical Alert
                'remaining_balance' => '2900000.0000',
                'status'            => 'Approved',
                'notes'             => 'Essential drug formulary and IV fluids replenishment. Warning: 88.4% exhausted.',
            ],
            [
                'department'        => 'Emergency Medicine & Trauma',
                'department_code'   => 'EMERG-01',
                'category'          => 'Clinical Operations',
                'department_head'   => 'Chair of Emergency Medicine',
                'fiscal_year'       => '2026',
                'allocated_amount'  => '12000000.0000',
                'spent_amount'      => '6400000.0000',
                'remaining_balance' => '5600000.0000',
                'status'            => 'Approved',
                'notes'             => '24/7 Level III Trauma Center operations and rapid triage consumables.',
            ],
            [
                'department'        => 'Laboratory & Diagnostic Imaging',
                'department_code'   => 'LAB-01',
                'category'          => 'Diagnostic Services',
                'department_head'   => 'Head Pathologist',
                'fiscal_year'       => '2026',
                'allocated_amount'  => '10000000.0000',
                'spent_amount'      => '4800000.0000',
                'remaining_balance' => '5200000.0000',
                'status'            => 'Approved',
                'notes'             => 'CT scan contrast, molecular pathology reagents, and hematology analyzers.',
            ],
            [
                'department'        => 'Intensive Care Unit (ICU)',
                'department_code'   => 'ICU-01',
                'category'          => 'Critical Care',
                'department_head'   => 'Critical Care Director',
                'fiscal_year'       => '2026',
                'allocated_amount'  => '8000000.0000',
                'spent_amount'      => '6950000.0000', // 86.9% Burn Rate -> Critical Alert
                'remaining_balance' => '1050000.0000',
                'status'            => 'Approved',
                'notes'             => 'Continuous hemodynamic monitoring and advanced life support ventilators.',
            ],
            [
                'department'        => 'Pediatrics & Neonatal Care',
                'department_code'   => 'PED-01',
                'category'          => 'Clinical Operations',
                'department_head'   => 'Chair of Pediatrics',
                'fiscal_year'       => '2026',
                'allocated_amount'  => '6000000.0000',
                'spent_amount'      => '1800000.0000',
                'remaining_balance' => '4200000.0000',
                'status'            => 'Approved',
                'notes'             => 'NICU incubators, neonatal phototherapy, and pediatric immunization programs.',
            ],
            [
                'department'        => 'Outpatient Department (OPD)',
                'department_code'   => 'OPD-01',
                'category'          => 'Ambulatory Care',
                'department_head'   => 'OPD Medical Officer',
                'fiscal_year'       => '2026',
                'allocated_amount'  => '4000000.0000',
                'spent_amount'      => '1400000.0000',
                'remaining_balance' => '2600000.0000',
                'status'            => 'Approved',
                'notes'             => 'Consultation clinics, telemedicine infrastructure, and preventive health screenings.',
            ],
        ];

        foreach ($budgetItems as $item) {
            BudgetAllocation::updateOrCreate(
                [
                    'department'  => $item['department'],
                    'fiscal_year' => $item['fiscal_year'],
                ],
                $item
            );
        }

        // 3. Ensure Cashier Shift exists for Daily Collection & Deposit Tracking
        $shift = CashierShift::updateOrCreate(
            ['shift_code' => 'SHIFT-20260918-01'],
            [
                'cashier_id'                => $cashier?->id ?? 1,
                'terminal_name'             => 'POS-MAIN-01',
                'opened_at'                 => Carbon::now()->startOfDay()->addHours(8),
                'opening_cash_float'        => '5000.0000',
                'expected_cash'             => '43500.0000',
                'actual_cash_counted'       => '43500.0000',
                'cash_variance'             => '0.0000',
                'total_digital_collections' => '10000.0000',
                'total_collections'         => '38500.0000',
                'status'                    => 'OPEN',
                'notes'                     => 'Main Lobby Cashier Desk - Shift A (COA Intact Deposit Monitoring Active)',
            ]
        );

        // 4. Ensure Cashier Payments exist
        $patient1 = PatientAccount::first();
        if ($patient1) {
            Payment::firstOrCreate(
                ['payment_reference' => 'PAY-20260918-0001'],
                [
                    'invoice_id'             => null,
                    'patient_account_id'     => $patient1->id,
                    'cashier_shift_id'       => $shift->id,
                    'payment_date'           => Carbon::now()->toDateString(),
                    'amount'                 => '16000.0000',
                    'payment_method'         => 'CASH',
                    'transaction_channel_ref'=> 'OR-2026-00912',
                    'payment_type'           => 'PATIENT_COPAY',
                ]
            );

            Payment::firstOrCreate(
                ['payment_reference' => 'PAY-20260918-0002'],
                [
                    'invoice_id'             => null,
                    'patient_account_id'     => $patient1->id,
                    'cashier_shift_id'       => $shift->id,
                    'payment_date'           => Carbon::now()->toDateString(),
                    'amount'                 => '12500.0000',
                    'payment_method'         => 'CASH',
                    'transaction_channel_ref'=> 'OR-2026-00913',
                    'payment_type'           => 'PATIENT_COPAY',
                ]
            );

            Payment::firstOrCreate(
                ['payment_reference' => 'PAY-20260918-0003'],
                [
                    'invoice_id'             => null,
                    'patient_account_id'     => $patient1->id,
                    'cashier_shift_id'       => $shift->id,
                    'payment_date'           => Carbon::now()->toDateString(),
                    'amount'                 => '10000.0000',
                    'payment_method'         => 'GCASH',
                    'transaction_channel_ref'=> 'GCASH-TXN-8849201',
                    'payment_type'           => 'PATIENT_COPAY',
                ]
            );
        }

        // 5. Bank Deposit to LBP (COA Circular 2021-014 Intact Daily Remittance)
        BankDeposit::firstOrCreate(
            ['deposit_reference' => 'DEP-20260918-0001'],
            [
                'bank_account_id'       => $lbpAccount->id,
                'cashier_shift_id'      => $shift->id,
                'deposit_date'          => Carbon::now()->toDateString(),
                'cash_amount'           => '25000.0000',
                'check_amount'          => '0.0000',
                'total_deposited'       => '25000.0000',
                'bank_reference_number' => 'LBP-DEP-REF-992147',
                'validated_by_teller'   => 'R. Mendoza (LBP QC Government Center Branch)',
                'status'                => 'DEPOSITED',
            ]
        );

        // 6. Additional PhilHealth Claims for UHC Case Rates Representation
        $patient2 = PatientAccount::skip(1)->first();
        if ($patient2) {
            $inv2 = Invoice::firstOrCreate(
                ['invoice_number' => 'INV-20260918-DENGUE'],
                [
                    'patient_account_id' => $patient2->id,
                    'invoice_date'       => Carbon::now()->toDateString(),
                    'due_date'           => Carbon::now()->addDays(30)->toDateString(),
                    'total_amount'       => '28500.0000',
                    'insurance_covered'  => '16000.0000',
                    'discount_amount'    => '0.0000',
                    'vat_amount'         => '0.0000',
                    'patient_payable'    => '12500.0000',
                    'paid_amount'        => '0.0000',
                    'status'             => 'PARTIAL',
                ]
            );

            PhilhealthClaim::firstOrCreate(
                ['claim_series_number' => 'PHIC-20260918-0002'],
                [
                    'invoice_id'                 => $inv2->id,
                    'membership_type'            => 'INDIGENT',
                    'primary_icd_code'           => 'A91',
                    'primary_case_rate_code'     => 'DENGUE-SEV-01',
                    'primary_case_rate_amount'   => '16000.0000',
                    'secondary_case_rate_amount' => '0.0000',
                    'total_case_rate_amount'     => '16000.0000',
                    'hospital_fee_share'         => '11200.0000',
                    'professional_fee_share'     => '4800.0000',
                    'claim_status'               => 'TRANSMITTED',
                    'transmitted_at'             => Carbon::now()->toDateString(),
                ]
            );
        }

        $patient3 = PatientAccount::skip(2)->first();
        if ($patient3) {
            $inv3 = Invoice::firstOrCreate(
                ['invoice_number' => 'INV-20260918-APPEND'],
                [
                    'patient_account_id' => $patient3->id,
                    'invoice_date'       => Carbon::now()->toDateString(),
                    'due_date'           => Carbon::now()->addDays(30)->toDateString(),
                    'total_amount'       => '42000.0000',
                    'insurance_covered'  => '24000.0000',
                    'discount_amount'    => '0.0000',
                    'vat_amount'         => '0.0000',
                    'patient_payable'    => '18000.0000',
                    'paid_amount'        => '0.0000',
                    'status'             => 'UNPAID',
                ]
            );

            PhilhealthClaim::firstOrCreate(
                ['claim_series_number' => 'PHIC-20260918-0003'],
                [
                    'invoice_id'                 => $inv3->id,
                    'membership_type'            => 'FORMAL_ECONOMY',
                    'primary_icd_code'           => 'K35.8',
                    'primary_case_rate_code'     => 'APPEND-ACUTE-01',
                    'primary_case_rate_amount'   => '24000.0000',
                    'secondary_case_rate_amount' => '0.0000',
                    'total_case_rate_amount'     => '24000.0000',
                    'hospital_fee_share'         => '16800.0000',
                    'professional_fee_share'     => '7200.0000',
                    'claim_status'               => 'APPROVED',
                    'transmitted_at'             => Carbon::now()->subDays(5)->toDateString(),
                ]
            );
        }

        $patient4 = PatientAccount::skip(3)->first();
        if ($patient4) {
            $inv4 = Invoice::firstOrCreate(
                ['invoice_number' => 'INV-20260918-CS'],
                [
                    'patient_account_id' => $patient4->id,
                    'invoice_date'       => Carbon::now()->toDateString(),
                    'due_date'           => Carbon::now()->addDays(30)->toDateString(),
                    'total_amount'       => '36000.0000',
                    'insurance_covered'  => '19000.0000',
                    'discount_amount'    => '0.0000',
                    'vat_amount'         => '0.0000',
                    'patient_payable'    => '17000.0000',
                    'paid_amount'        => '0.0000',
                    'status'             => 'UNPAID',
                ]
            );

            PhilhealthClaim::firstOrCreate(
                ['claim_series_number' => 'PHIC-20260918-0004'],
                [
                    'invoice_id'                 => $inv4->id,
                    'membership_type'            => 'SPONSORED',
                    'primary_icd_code'           => 'O82.0',
                    'primary_case_rate_code'     => 'CS-DELIVERY-01',
                    'primary_case_rate_amount'   => '19000.0000',
                    'secondary_case_rate_amount' => '0.0000',
                    'total_case_rate_amount'     => '19000.0000',
                    'hospital_fee_share'         => '13300.0000',
                    'professional_fee_share'     => '5700.0000',
                    'claim_status'               => 'IN_PROCESS',
                    'transmitted_at'             => Carbon::now()->subDays(2)->toDateString(),
                ]
            );
        }
    }
}
