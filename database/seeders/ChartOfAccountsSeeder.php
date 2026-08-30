<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Account;
use Illuminate\Database\Seeder;

final class ChartOfAccountsSeeder extends Seeder
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
                'code'           => '1030',
                'name'           => 'Digital Payment Gateway Clearing',
                'category'       => 'ASSET',
                'normal_balance' => 'DEBIT',
                'department'     => 'CASHIER',
                'is_active'      => true,
            ],
            [
                'code'           => '1110',
                'name'           => 'Accounts Receivable - Patient Copay',
                'category'       => 'ASSET',
                'normal_balance' => 'DEBIT',
                'department'     => 'BILLING',
                'is_active'      => true,
            ],
            [
                'code'           => '1120',
                'name'           => 'Accounts Receivable - PhilHealth Claims',
                'category'       => 'ASSET',
                'normal_balance' => 'DEBIT',
                'department'     => 'BILLING',
                'is_active'      => true,
            ],
            [
                'code'           => '1130',
                'name'           => 'Accounts Receivable - HMO Claims',
                'category'       => 'ASSET',
                'normal_balance' => 'DEBIT',
                'department'     => 'BILLING',
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
                'department'     => 'ACCOUNTING',
                'is_active'      => true,
            ],
            [
                'code'           => '2020',
                'name'           => 'Accrued Doctor Professional Fees',
                'category'       => 'LIABILITY',
                'normal_balance' => 'CREDIT',
                'department'     => 'ACCOUNTING',
                'is_active'      => true,
            ],
            [
                'code'           => '2030',
                'name'           => 'Withholding Tax Payable - Expanded (BIR 2307 / 1601-EQ)',
                'category'       => 'LIABILITY',
                'normal_balance' => 'CREDIT',
                'department'     => 'TAX',
                'is_active'      => true,
            ],
            [
                'code'           => '2110',
                'name'           => 'Withholding Tax Payable - Expanded (Alternative Ref)',
                'category'       => 'LIABILITY',
                'normal_balance' => 'CREDIT',
                'department'     => 'TAX',
                'is_active'      => true,
            ],
            [
                'code'           => '2120',
                'name'           => 'Withholding Tax Payable - Compensation (BIR 1601-C)',
                'category'       => 'LIABILITY',
                'normal_balance' => 'CREDIT',
                'department'     => 'TAX',
                'is_active'      => true,
            ],
            [
                'code'           => '2130',
                'name'           => 'SSS Premiums Payable',
                'category'       => 'LIABILITY',
                'normal_balance' => 'CREDIT',
                'department'     => 'PAYROLL',
                'is_active'      => true,
            ],
            [
                'code'           => '2140',
                'name'           => 'PhilHealth Premiums Payable',
                'category'       => 'LIABILITY',
                'normal_balance' => 'CREDIT',
                'department'     => 'PAYROLL',
                'is_active'      => true,
            ],
            [
                'code'           => '2150',
                'name'           => 'HDMF (Pag-IBIG) Premiums Payable',
                'category'       => 'LIABILITY',
                'normal_balance' => 'CREDIT',
                'department'     => 'PAYROLL',
                'is_active'      => true,
            ],
            [
                'code'           => '2210',
                'name'           => 'Patient Deposit Advances',
                'category'       => 'LIABILITY',
                'normal_balance' => 'CREDIT',
                'department'     => 'CASHIER',
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
                'department'     => 'EXECUTIVE',
                'is_active'      => true,
            ],
            [
                'code'           => '3020',
                'name'           => "Owner's Capital / Shareholder Equity",
                'category'       => 'EQUITY',
                'normal_balance' => 'CREDIT',
                'department'     => 'EXECUTIVE',
                'is_active'      => true,
            ],

            // ==========================================
            // REVENUE (4000s)
            // ==========================================
            [
                'code'           => '4010',
                'name'           => 'Inpatient Hospital Care Revenue',
                'category'       => 'REVENUE',
                'normal_balance' => 'CREDIT',
                'department'     => 'CLINICAL',
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
                'department'     => 'LABORATORY',
                'is_active'      => true,
            ],
            [
                'code'           => '4040',
                'name'           => 'Radiology & Imaging Revenue',
                'category'       => 'REVENUE',
                'normal_balance' => 'CREDIT',
                'department'     => 'RADIOLOGY',
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
                'name'           => 'Operating Room Fees',
                'category'       => 'REVENUE',
                'normal_balance' => 'CREDIT',
                'department'     => 'SURGERY',
                'is_active'      => true,
            ],

            // ==========================================
            // CONTRA-REVENUE / EXPENSES (4900s - 6000s)
            // ==========================================
            [
                'code'           => '4910',
                'name'           => 'Statutory Discounts Allowed (Senior/PWD)',
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
                'department'     => 'SOCIAL_SERVICES',
                'is_active'      => true,
            ],
            [
                'code'           => '5010',
                'name'           => 'Cost of Medicines & Medical Supplies Sold',
                'category'       => 'EXPENSE',
                'normal_balance' => 'DEBIT',
                'department'     => 'PHARMACY',
                'is_active'      => true,
            ],
            [
                'code'           => '5020',
                'name'           => 'Medical & Hospital Operating Supplies Expense',
                'category'       => 'EXPENSE',
                'normal_balance' => 'DEBIT',
                'department'     => 'PROCUREMENT',
                'is_active'      => true,
            ],
            [
                'code'           => '6010',
                'name'           => 'Salaries, Wages & Employee Benefits',
                'category'       => 'EXPENSE',
                'normal_balance' => 'DEBIT',
                'department'     => 'HR',
                'is_active'      => true,
            ],
            [
                'code'           => '6020',
                'name'           => 'Employer Statutory Contribution Expense (SSS/PhilHealth/HDMF)',
                'category'       => 'EXPENSE',
                'normal_balance' => 'DEBIT',
                'department'     => 'HR',
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
        ];

        foreach ($accounts as $a) {
            Account::updateOrCreate(['code' => $a['code']], $a);
        }
    }
}
