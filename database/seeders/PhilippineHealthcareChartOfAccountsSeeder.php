<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Account;
use Illuminate\Database\Seeder;

final class PhilippineHealthcareChartOfAccountsSeeder extends Seeder
{
    public function run(): void
    {
        $accounts = [
            // ==========================================
            // ASSETS (1000s)
            // ==========================================
            [
                'code'           => '1010',
                'name'           => 'Petty Cash Fund',
                'category'       => 'ASSET',
                'normal_balance' => 'DEBIT',
                'department'     => 'FINANCE',
                'is_active'      => true,
            ],
            [
                'code'           => '1011',
                'name'           => 'Cashier Undeposited Collections',
                'category'       => 'ASSET',
                'normal_balance' => 'DEBIT',
                'department'     => 'CASHIER',
                'is_active'      => true,
            ],
            [
                'code'           => '1020',
                'name'           => 'Cash in Bank - Operating Account',
                'category'       => 'ASSET',
                'normal_balance' => 'DEBIT',
                'department'     => 'TREASURY',
                'is_active'      => true,
            ],
            [
                'code'           => '1021',
                'name'           => 'Cash in Bank - Payroll Account',
                'category'       => 'ASSET',
                'normal_balance' => 'DEBIT',
                'department'     => 'PAYROLL',
                'is_active'      => true,
            ],
            [
                'code'           => '1110',
                'name'           => 'Accounts Receivable - Inpatient',
                'category'       => 'ASSET',
                'normal_balance' => 'DEBIT',
                'department'     => 'BILLING',
                'is_active'      => true,
            ],
            [
                'code'           => '1120',
                'name'           => 'Accounts Receivable - Outpatient',
                'category'       => 'ASSET',
                'normal_balance' => 'DEBIT',
                'department'     => 'BILLING',
                'is_active'      => true,
            ],
            [
                'code'           => '1130',
                'name'           => 'Accounts Receivable - PhilHealth Claims',
                'category'       => 'ASSET',
                'normal_balance' => 'DEBIT',
                'department'     => 'CREDIT_COLLECTION',
                'is_active'      => true,
            ],
            [
                'code'           => '1140',
                'name'           => 'Accounts Receivable - HMO / Private Insurance',
                'category'       => 'ASSET',
                'normal_balance' => 'DEBIT',
                'department'     => 'CREDIT_COLLECTION',
                'is_active'      => true,
            ],
            [
                'code'           => '1210',
                'name'           => 'Pharmacy & Medical Supplies Inventory',
                'category'       => 'ASSET',
                'normal_balance' => 'DEBIT',
                'department'     => 'PHARMACY',
                'is_active'      => true,
            ],

            // ==========================================
            // LIABILITIES (2000s)
            // ==========================================
            [
                'code'           => '2010',
                'name'           => 'Accounts Payable - Trade / Vendors',
                'category'       => 'LIABILITY',
                'normal_balance' => 'CREDIT',
                'department'     => 'DISBURSEMENT',
                'is_active'      => true,
            ],
            [
                'code'           => '2020',
                'name'           => 'Accrued Doctor Professional Fees',
                'category'       => 'LIABILITY',
                'normal_balance' => 'CREDIT',
                'department'     => 'MEDICAL_AFFAIRS',
                'is_active'      => true,
            ],
            [
                'code'           => '2110',
                'name'           => 'Withholding Tax Payable - Expanded / EWT (BIR 2307 / 1601-EQ)',
                'category'       => 'LIABILITY',
                'normal_balance' => 'CREDIT',
                'department'     => 'TAX_COMPLIANCE',
                'is_active'      => true,
            ],
            [
                'code'           => '2120',
                'name'           => 'Withholding Tax Payable - Compensation (BIR 1601-C)',
                'category'       => 'LIABILITY',
                'normal_balance' => 'CREDIT',
                'department'     => 'TAX_COMPLIANCE',
                'is_active'      => true,
            ],
            [
                'code'           => '2130',
                'name'           => 'SSS Premiums Payable',
                'category'       => 'LIABILITY',
                'normal_balance' => 'CREDIT',
                'department'     => 'HR_COMPLIANCE',
                'is_active'      => true,
            ],
            [
                'code'           => '2140',
                'name'           => 'PhilHealth Premiums Payable',
                'category'       => 'LIABILITY',
                'normal_balance' => 'CREDIT',
                'department'     => 'HR_COMPLIANCE',
                'is_active'      => true,
            ],
            [
                'code'           => '2150',
                'name'           => 'HDMF (Pag-IBIG) Premiums Payable',
                'category'       => 'LIABILITY',
                'normal_balance' => 'CREDIT',
                'department'     => 'HR_COMPLIANCE',
                'is_active'      => true,
            ],
            [
                'code'           => '2210',
                'name'           => 'Patient Deposit Advances',
                'category'       => 'LIABILITY',
                'normal_balance' => 'CREDIT',
                'department'     => 'ADMISSION',
                'is_active'      => true,
            ],

            // ==========================================
            // EQUITY (3000s)
            // ==========================================
            [
                'code'           => '3010',
                'name'           => 'Hospital Retained Earnings',
                'category'       => 'EQUITY',
                'normal_balance' => 'CREDIT',
                'department'     => 'CORPORATE',
                'is_active'      => true,
            ],
            [
                'code'           => '3020',
                'name'           => "Owner's Capital / Shareholder Equity",
                'category'       => 'EQUITY',
                'normal_balance' => 'CREDIT',
                'department'     => 'CORPORATE',
                'is_active'      => true,
            ],

            // ==========================================
            // REVENUES (4000s)
            // ==========================================
            [
                'code'           => '4010',
                'name'           => 'Inpatient Hospital Care Revenue',
                'category'       => 'REVENUE',
                'normal_balance' => 'CREDIT',
                'department'     => 'NURSING_WARDS',
                'is_active'      => true,
            ],
            [
                'code'           => '4020',
                'name'           => 'Outpatient Consultation Revenue',
                'category'       => 'REVENUE',
                'normal_balance' => 'CREDIT',
                'department'     => 'OPD',
                'is_active'      => true,
            ],
            [
                'code'           => '4030',
                'name'           => 'Laboratory & Diagnostic Services Revenue',
                'category'       => 'REVENUE',
                'normal_balance' => 'CREDIT',
                'department'     => 'LIS',
                'is_active'      => true,
            ],
            [
                'code'           => '4040',
                'name'           => 'Radiology & Imaging Revenue',
                'category'       => 'REVENUE',
                'normal_balance' => 'CREDIT',
                'department'     => 'RIS',
                'is_active'      => true,
            ],
            [
                'code'           => '4050',
                'name'           => 'Pharmacy Sales Revenue',
                'category'       => 'REVENUE',
                'normal_balance' => 'CREDIT',
                'department'     => 'PHARMACY',
                'is_active'      => true,
            ],
            [
                'code'           => '4060',
                'name'           => 'Operating Room & Surgical Suite Fees',
                'category'       => 'REVENUE',
                'normal_balance' => 'CREDIT',
                'department'     => 'SURGERY',
                'is_active'      => true,
            ],

            // ==========================================
            // CONTRA-REVENUES & DEDUCTIONS (4900s)
            // ==========================================
            [
                'code'           => '4910',
                'name'           => 'Senior Citizen RA 9994 Discounts Allowed',
                'category'       => 'EXPENSE',
                'normal_balance' => 'DEBIT',
                'department'     => 'BILLING',
                'is_active'      => true,
            ],
            [
                'code'           => '4920',
                'name'           => 'PWD RA 10754 Discounts Allowed',
                'category'       => 'EXPENSE',
                'normal_balance' => 'DEBIT',
                'department'     => 'BILLING',
                'is_active'      => true,
            ],
            [
                'code'           => '4930',
                'name'           => 'Charity / Indigent Care Allowances',
                'category'       => 'EXPENSE',
                'normal_balance' => 'DEBIT',
                'department'     => 'MEDICAL_SOCIAL_SERVICES',
                'is_active'      => true,
            ],

            // ==========================================
            // DIRECT COSTS & OPERATING EXPENSES (5000s & 6000s)
            // ==========================================
            [
                'code'           => '5010',
                'name'           => 'Cost of Medicines & Medical Supplies Sold',
                'category'       => 'EXPENSE',
                'normal_balance' => 'DEBIT',
                'department'     => 'PHARMACY',
                'is_active'      => true,
            ],
            [
                'code'           => '6010',
                'name'           => 'Salaries, Wages & Employee Benefits',
                'category'       => 'EXPENSE',
                'normal_balance' => 'DEBIT',
                'department'     => 'ADMINISTRATION',
                'is_active'      => true,
            ],
            [
                'code'           => '6020',
                'name'           => 'SSS / PhilHealth / HDMF Employer Contribution Expense',
                'category'       => 'EXPENSE',
                'normal_balance' => 'DEBIT',
                'department'     => 'ADMINISTRATION',
                'is_active'      => true,
            ],
            [
                'code'           => '6030',
                'name'           => 'Hospital Utilities (Power, Water, Telecom)',
                'category'       => 'EXPENSE',
                'normal_balance' => 'DEBIT',
                'department'     => 'FACILITIES',
                'is_active'      => true,
            ],
            [
                'code'           => '6040',
                'name'           => 'Facility Maintenance & Fleet Fuel',
                'category'       => 'EXPENSE',
                'normal_balance' => 'DEBIT',
                'department'     => 'FACILITIES',
                'is_active'      => true,
            ],
            [
                'code'           => '6050',
                'name'           => 'Bank Charges & Transaction Fees',
                'category'       => 'EXPENSE',
                'normal_balance' => 'DEBIT',
                'department'     => 'TREASURY',
                'is_active'      => true,
            ],
        ];

        foreach ($accounts as $account) {
            Account::updateOrCreate(
                ['code' => $account['code']],
                $account
            );
        }
    }
}
