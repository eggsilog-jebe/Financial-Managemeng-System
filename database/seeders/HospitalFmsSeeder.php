<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Account;
use App\Models\JournalEntry;
use App\Models\JournalEntryLine;
use App\Models\BankAccount;
use App\Models\FundTransfer;
use App\Models\BankReconciliation;
use App\Models\TaxRule;
use App\Models\TaxCertificate;
use App\Models\TaxReturn;
use App\Models\Vendor;
use App\Models\PurchaseBill;
use App\Models\PatientAccount;
use App\Models\Invoice;
use App\Models\BudgetAllocation;
use App\Models\PaymentRequest;
use App\Models\PaymentReceipt;

final class HospitalFmsSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Seed General Ledger Chart of Accounts
        if (Account::count() === 0) {
            $accCash = Account::create([
                'code' => '1010',
                'name' => 'Cash on Hand - Main Vault',
                'category' => 'ASSET',
                'normal_balance' => 'DEBIT',
                'department' => 'Treasury / Cashier',
                'is_active' => true,
            ]);

            $accBank = Account::create([
                'code' => '1020',
                'name' => 'Operating Bank Account - Metrobank',
                'category' => 'ASSET',
                'normal_balance' => 'DEBIT',
                'department' => 'Hospital Treasury',
                'is_active' => true,
            ]);

            $accAR = Account::create([
                'code' => '1050',
                'name' => 'Accounts Receivable - Patients & HMOs',
                'category' => 'ASSET',
                'normal_balance' => 'DEBIT',
                'department' => 'Patient Billing / AR',
                'is_active' => true,
            ]);

            $accAP = Account::create([
                'code' => '2010',
                'name' => 'Accounts Payable - Medical Suppliers',
                'category' => 'LIABILITY',
                'normal_balance' => 'CREDIT',
                'department' => 'Accounts Payable',
                'is_active' => true,
            ]);

            $accEquity = Account::create([
                'code' => '3010',
                'name' => 'Founding Capital Reserve',
                'category' => 'EQUITY',
                'normal_balance' => 'CREDIT',
                'department' => 'Executive Office',
                'is_active' => true,
            ]);

            $accRev = Account::create([
                'code' => '4010',
                'name' => 'Inpatient & Emergency Care Revenue',
                'category' => 'REVENUE',
                'normal_balance' => 'CREDIT',
                'department' => 'Medical Services',
                'is_active' => true,
            ]);

            $accExp = Account::create([
                'code' => '5010',
                'name' => 'Medical & Surgical Supplies Expense',
                'category' => 'EXPENSE',
                'normal_balance' => 'DEBIT',
                'department' => 'Pharmacy & Supplies',
                'is_active' => true,
            ]);

            // Seed Journal Entries & Balanced Lines
            if (JournalEntry::count() === 0) {
                $je1 = JournalEntry::create([
                    'reference_number' => 'JE-2026-0045',
                    'entry_date' => '2026-08-10',
                    'description' => 'Outpatient Lab Consultation & Testing Revenue Settlement',
                    'type' => 'GENERAL',
                    'status' => 'POSTED',
                    'posted_at' => now(),
                ]);

                JournalEntryLine::create([
                    'journal_entry_id' => $je1->id,
                    'account_id' => $accCash->id,
                    'debit' => 350000.0000,
                    'credit' => 0.0000,
                    'memo' => 'Cash Settlement',
                ]);

                JournalEntryLine::create([
                    'journal_entry_id' => $je1->id,
                    'account_id' => $accRev->id,
                    'debit' => 0.0000,
                    'credit' => 350000.0000,
                    'memo' => 'Outpatient Services Revenue',
                ]);

                $je2 = JournalEntry::create([
                    'reference_number' => 'JE-2026-0044',
                    'entry_date' => '2026-08-09',
                    'description' => 'Surgical Supplies Stock Purchase',
                    'type' => 'GENERAL',
                    'status' => 'POSTED',
                    'posted_at' => now(),
                ]);

                JournalEntryLine::create([
                    'journal_entry_id' => $je2->id,
                    'account_id' => $accExp->id,
                    'debit' => 85400.0000,
                    'credit' => 0.0000,
                    'memo' => 'Surgical Equipment Expense',
                ]);

                JournalEntryLine::create([
                    'journal_entry_id' => $je2->id,
                    'account_id' => $accAP->id,
                    'debit' => 0.0000,
                    'credit' => 85400.0000,
                    'memo' => 'Trade Payable Vendor Bill',
                ]);
            }
        }

        // 2. Seed Cash Management
        if (BankAccount::count() === 0) {
            $bank1 = BankAccount::create([
                'name' => 'Metrobank Operating Account',
                'bank_name' => 'Metrobank - Main Branch',
                'account_number' => '1020-8841-99',
                'gl_code' => '1010-01-METRO',
                'purpose' => 'Primary Operations & Payroll Payouts',
                'currency' => 'PHP',
                'balance' => 4850000.0000,
                'status' => 'Active',
            ]);

            BankAccount::create([
                'name' => 'BDO Collections Account',
                'bank_name' => 'BDO Unibank - Medical City Branch',
                'account_number' => '0091-2384-12',
                'gl_code' => '1010-02-BDO',
                'purpose' => 'Collections & HMO Deposits',
                'currency' => 'PHP',
                'balance' => 2140000.0000,
                'status' => 'Active',
            ]);

            if (FundTransfer::count() === 0) {
                FundTransfer::create([
                    'reference_number' => 'TRF-2026-088',
                    'source_account' => 'BDO Collections',
                    'source_number' => '#0091-2384-12',
                    'destination_account' => 'Metrobank Operating',
                    'destination_number' => '#1020-8841-99',
                    'amount' => 500000.0000,
                    'transfer_method' => 'PESONet Interbank Transfer',
                    'transfer_date' => '2026-08-07',
                    'status' => 'Completed & Posted',
                ]);
            }

            if (BankReconciliation::count() === 0) {
                BankReconciliation::create([
                    'bank_account_id' => $bank1->id,
                    'statement_date' => '2026-07-31',
                    'statement_balance' => 4850000.0000,
                    'book_balance' => 4850000.0000,
                    'variance' => 0.0000,
                    'status' => 'Reconciled',
                ]);
            }
        }

        // 3. Seed Tax Management
        if (TaxRule::count() === 0) {
            TaxRule::create([
                'tax_code' => 'TAX-EWT-DOC10',
                'name' => 'EWT - Professional Fees (Medical Consultants)',
                'atc_code' => 'WI010',
                'category' => 'Expanded Withholding Tax',
                'cat_type' => 'ewt',
                'rate' => 10.00,
                'scope' => 'Visiting Doctors & Medical Consultants (< ₱3M Gross)',
                'status' => 'Active',
            ]);

            TaxRule::create([
                'tax_code' => 'TAX-EWT-SUP01',
                'name' => 'EWT - Medical & Hospital Equipment Suppliers',
                'atc_code' => 'WC158',
                'category' => 'Expanded Withholding Tax',
                'cat_type' => 'ewt',
                'rate' => 1.00,
                'scope' => 'Purchase of Medical Goods & Supplies',
                'status' => 'Active',
            ]);
        }

        if (TaxCertificate::count() === 0) {
            TaxCertificate::create([
                'cert_number' => 'C2307-2026-881',
                'payee_name' => 'Dr. Roberto Gomez',
                'payee_role' => 'Visiting Cardiology Consultant',
                'payee_type' => 'doctor',
                'tin' => '102-391-441-000',
                'atc_code' => 'WI010 (10%)',
                'gross_income' => 120000.0000,
                'tax_withheld' => 12000.0000,
                'form_type' => '2307',
            ]);
        }

        if (TaxReturn::count() === 0) {
            TaxReturn::create([
                'return_number' => 'RET-2307-2026-Q2',
                'form_type' => '1601EQ',
                'period_covered' => 'Q2 FY 2026',
                'tax_due' => 145000.0000,
                'status' => 'FILED',
                'filing_date' => '2026-07-25',
            ]);
        }

        // 4. Seed Accounts Payable & Receivable
        if (Vendor::count() === 0) {
            $v1 = Vendor::create([
                'code' => 'VEND-001',
                'name' => 'PharmaSupply Philippines Inc.',
                'tin' => '100-291-884-000',
                'contact_person' => 'Juan Dela Cruz',
                'email' => 'sales@pharmasupply.ph',
                'phone' => '09171234567',
                'status' => 'Active',
            ]);

            if (PurchaseBill::count() === 0) {
                PurchaseBill::create([
                    'bill_number' => 'PB-2026-9901',
                    'vendor_id' => $v1->id,
                    'bill_date' => '2026-08-01',
                    'due_date' => '2026-08-31',
                    'total_amount' => 125000.0000,
                    'paid_amount' => 0.0000,
                    'status' => 'UNPAID',
                ]);
            }
        }

        if (PatientAccount::count() === 0) {
            $p1 = PatientAccount::create([
                'patient_id_number' => 'PAT-2026-0012',
                'full_name' => 'Maria Santos',
                'admission_type' => 'Inpatient',
                'hmo_provider' => 'Maxicare Health',
                'total_billed' => 85000.0000,
                'current_balance' => 15000.0000,
                'status' => 'Active',
            ]);

            if (Invoice::count() === 0) {
                Invoice::create([
                    'invoice_number' => 'INV-2026-4401',
                    'patient_account_id' => $p1->id,
                    'invoice_date' => '2026-08-05',
                    'total_amount' => 85000.0000,
                    'insurance_covered' => 70000.0000,
                    'patient_payable' => 15000.0000,
                    'status' => 'UNPAID',
                ]);
            }
        }

        // 5. Seed Budgets & Collections
        if (BudgetAllocation::count() === 0) {
            BudgetAllocation::create([
                'department' => 'Medical & Emergency Services',
                'fiscal_year' => 'FY 2026',
                'allocated_amount' => 12000000.0000,
                'spent_amount' => 4500000.0000,
                'remaining_balance' => 7500000.0000,
                'status' => 'Approved',
            ]);
        }

        if (PaymentRequest::count() === 0) {
            PaymentRequest::create([
                'request_number' => 'REQ-2026-102',
                'department' => 'Pharmacy',
                'payee_name' => 'PharmaSupply Philippines Inc.',
                'amount' => 125000.0000,
                'purpose' => 'Medical Consumables Requisition',
                'status' => 'APPROVED',
            ]);
        }

        if (PaymentReceipt::count() === 0) {
            PaymentReceipt::create([
                'receipt_number' => 'OR-2026-8801',
                'payer_name' => 'Maria Santos',
                'amount_paid' => 15000.0000,
                'payment_method' => 'Cash',
                'receipt_date' => '2026-08-10',
                'cashier_name' => 'John Cashier',
            ]);
        }
    }
}
