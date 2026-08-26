<?php

declare(strict_types=1);

namespace Tests\Feature\Accounting;

use App\Models\Account;
use App\Models\BankAccount;
use App\Models\DisbursementVoucher;
use App\Models\FiscalPeriod;
use App\Models\Invoice;
use App\Models\JournalEntry;
use App\Models\JournalEntryLine;
use App\Models\PatientAccount;
use App\Models\Payment;
use App\Models\PurchaseBill;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class FinancialReportingModuleTest extends TestCase
{
    use RefreshDatabase;

    private User $accountant;
    private User $manager;
    private User $cfo;
    private User $auditor;
    private Account $assetCash;
    private Account $assetAr;
    private Account $liabAp;
    private Account $equityCap;
    private Account $revInpatient;
    private Account $expSalaries;

    protected function setUp(): void
    {
        parent::setUp();

        $this->accountant = User::factory()->create(['role' => 'StaffAccountant', 'name' => 'Report Staff', 'email' => 'reporting@hospital.local']);
        $this->manager = User::factory()->create(['role' => 'FinanceManager', 'name' => 'Finance Manager', 'email' => 'finance.mgr@hospital.local']);
        $this->cfo = User::factory()->create(['role' => 'CFO', 'name' => 'Chief Financial Officer', 'email' => 'cfo@hospital.local']);
        $this->auditor = User::factory()->create(['role' => 'Auditor', 'name' => 'Senior Auditor', 'email' => 'auditor@hospital.local']);

        FiscalPeriod::create([
            'period_code'   => '2026-M01',
            'fiscal_year'   => '2026',
            'period_number' => 1,
            'start_date'    => '2026-01-01',
            'end_date'      => '2026-12-31',
            'status'        => 'OPEN',
        ]);

        $this->assetCash = Account::create(['code' => '1010', 'name' => 'Cash on Hand & Bank', 'category' => 'ASSET', 'normal_balance' => 'DEBIT']);
        $this->assetAr = Account::create(['code' => '1120', 'name' => 'Accounts Receivable - Patients', 'category' => 'ASSET', 'normal_balance' => 'DEBIT']);
        $this->liabAp = Account::create(['code' => '2010', 'name' => 'Accounts Payable - Vendors', 'category' => 'LIABILITY', 'normal_balance' => 'CREDIT']);
        $this->equityCap = Account::create(['code' => '3010', 'name' => 'Hospital Capital Reserve', 'category' => 'EQUITY', 'normal_balance' => 'CREDIT']);
        $this->revInpatient = Account::create(['code' => '4010', 'name' => 'Inpatient Care Revenue', 'category' => 'REVENUE', 'normal_balance' => 'CREDIT']);
        $this->expSalaries = Account::create(['code' => '6010', 'name' => 'Medical Staff Salaries', 'category' => 'EXPENSE', 'normal_balance' => 'DEBIT']);

        // Create Bank Account
        BankAccount::create([
            'name'            => 'Operating Cash Pool',
            'bank_name'       => 'Metrobank Pasig',
            'account_number'  => 'MB-1010-001',
            'gl_code'         => '1010',
            'gl_account_id'   => $this->assetCash->id,
            'purpose'         => 'Hospital Operations',
            'currency'        => 'PHP',
            'opening_balance' => '1000000.0000',
            'balance'         => '1000000.0000',
            'minimum_balance' => '100000.0000',
            'status'          => 'Active',
            'is_active'       => true,
        ]);

        // Post Journal Entry 1: Capital Equity (DR Cash 1,000,000 / CR Equity 1,000,000)
        $je1 = JournalEntry::create([
            'reference_number' => 'JE-CAP-001',
            'entry_date'       => '2026-01-02',
            'description'      => 'Initial Capital Contribution',
            'status'           => 'POSTED',
            'type'             => 'GENERAL',
        ]);
        JournalEntryLine::create(['journal_entry_id' => $je1->id, 'account_id' => $this->assetCash->id, 'debit' => '1000000.0000', 'credit' => '0.0000', 'memo' => 'Debit Cash']);
        JournalEntryLine::create(['journal_entry_id' => $je1->id, 'account_id' => $this->equityCap->id, 'debit' => '0.0000', 'credit' => '1000000.0000', 'memo' => 'Credit Equity']);

        // Post Journal Entry 2: Inpatient Revenue (DR AR 300,000 / CR Revenue 300,000)
        $je2 = JournalEntry::create([
            'reference_number' => 'JE-REV-001',
            'entry_date'       => '2026-01-10',
            'description'      => 'Inpatient Billable Services',
            'status'           => 'POSTED',
            'type'             => 'GENERAL',
        ]);
        JournalEntryLine::create(['journal_entry_id' => $je2->id, 'account_id' => $this->assetAr->id, 'debit' => '300000.0000', 'credit' => '0.0000', 'memo' => 'Debit Patient AR']);
        JournalEntryLine::create(['journal_entry_id' => $je2->id, 'account_id' => $this->revInpatient->id, 'debit' => '0.0000', 'credit' => '300000.0000', 'memo' => 'Credit Revenue']);

        // Post Journal Entry 3: Staff Salaries (DR Expense 100,000 / CR Cash 100,000)
        $je3 = JournalEntry::create([
            'reference_number' => 'JE-EXP-001',
            'entry_date'       => '2026-01-15',
            'description'      => 'Staff Mid-Month Payroll',
            'status'           => 'POSTED',
            'type'             => 'GENERAL',
        ]);
        JournalEntryLine::create(['journal_entry_id' => $je3->id, 'account_id' => $this->expSalaries->id, 'debit' => '100000.0000', 'credit' => '0.0000', 'memo' => 'Debit Salaries Expense']);
        JournalEntryLine::create(['journal_entry_id' => $je3->id, 'account_id' => $this->assetCash->id, 'debit' => '0.0000', 'credit' => '100000.0000', 'memo' => 'Credit Cash']);
    }

    /** @test */
    public function test_balance_sheet_accounting_equation_and_export(): void
    {
        $this->actingAs($this->accountant);

        // 1. Fetch Balance Sheet View
        $response = $this->get('/financial-reporting/balance-sheet?as_of_date=2026-01-31');
        $response->assertStatus(200);
        $response->assertSee('1010');
        $response->assertSee('1120');
        $response->assertSee('3010');
        $response->assertSee('BALANCED');

        // Assets = Cash (900k) + AR (300k) = 1,200k
        // Equity = Base (1,000k) + Net Income (300k - 100k = 200k) = 1,200k
        // Assets === Liab + Equity (1,200k === 1,200k)

        // 2. Export Balance Sheet CSV
        $export = $this->get('/financial-reporting/balance-sheet/export?as_of_date=2026-01-31');
        $export->assertStatus(200);
        $this->assertStringContainsString('text/csv', (string) $export->headers->get('Content-Type'));
    }

    /** @test */
    public function test_profit_and_loss_statement_revenue_expense_net_income(): void
    {
        $this->actingAs($this->accountant);

        $response = $this->get('/financial-reporting/profit-loss?date_from=2026-01-01&date_to=2026-01-31');
        $response->assertStatus(200);
        $response->assertSee('Inpatient Care Revenue');
        $response->assertSee('Medical Staff Salaries');
        $response->assertSee('200,000.00'); // Net Surplus: 300,000 - 100,000 = 200,000

        $export = $this->get('/financial-reporting/profit-loss/export?date_from=2026-01-01&date_to=2026-01-31');
        $export->assertStatus(200);
        $this->assertStringContainsString('text/csv', (string) $export->headers->get('Content-Type'));
    }

    /** @test */
    public function test_pas7_cash_flow_statement_operating_investing_financing(): void
    {
        $this->actingAs($this->accountant);

        $patient = PatientAccount::create([
            'patient_id_number' => 'MRN-2026-CF01',
            'full_name'         => 'Maria Corazon',
            'admission_type'    => 'Inpatient',
            'current_balance'   => '150000.0000',
            'credit_limit'      => '200000.0000',
            'status'            => 'Active',
        ]);

        $invoice = Invoice::create([
            'invoice_number'    => 'INV-CF-001',
            'patient_account_id'=> $patient->id,
            'invoice_date'      => '2026-01-10',
            'total_amount'      => '150000.0000',
            'patient_payable'   => '150000.0000',
            'status'            => 'ISSUED',
        ]);

        // Add dummy payment and purchase bill
        Payment::create([
            'patient_account_id' => $patient->id,
            'invoice_id'         => $invoice->id,
            'payment_reference'  => 'PAY-CF-001',
            'payment_date'       => '2026-01-12',
            'amount'             => '150000.0000',
            'payment_method'     => 'CASH',
            'status'             => 'COMPLETED',
        ]);

        $vendor = Vendor::create([
            'name'           => 'Pharma Supply Corp',
            'vendor_code'    => 'VND-CF-01',
            'tax_id_number'  => '111-222-333-000',
            'classification' => 'Pharmaceuticals',
            'current_balance'=> '0.0000',
            'is_active'      => true,
        ]);

        PurchaseBill::create([
            'bill_number'  => 'PB-CF-001',
            'vendor_id'    => $vendor->id,
            'bill_date'    => '2026-01-14',
            'due_date'     => '2026-01-28',
            'total_amount' => '50000.0000',
            'paid_amount'  => '50000.0000',
            'status'       => 'PAID',
        ]);

        $response = $this->get('/financial-reporting/cash-flow-statement?date_from=2026-01-01&date_to=2026-01-31');
        $response->assertStatus(200);
        $response->assertSee('PAS 7 Statement of Cash Flows Breakdown');
        $response->assertSee('150,000.00'); // Inflow
        $response->assertSee('50,000.00');  // Outflow

        $export = $this->get('/financial-reporting/cash-flow-statement/export?date_from=2026-01-01&date_to=2026-01-31');
        $export->assertStatus(200);
        $this->assertStringContainsString('text/csv', (string) $export->headers->get('Content-Type'));
    }

    /** @test */
    public function test_financial_kpi_dashboard_dso_dpo_ratios(): void
    {
        $this->actingAs($this->cfo);

        $response = $this->get('/financial-reporting/financial-kpi-dashboard');
        $response->assertStatus(200);
        $response->assertSee('Operating Profit Margin');
        $response->assertSee('Days Sales Outstanding');
        $response->assertSee('Days Cash on Hand');

        $export = $this->get('/financial-reporting/kpi-dashboard/export');
        $export->assertStatus(200);
        $this->assertStringContainsString('text/csv', (string) $export->headers->get('Content-Type'));
    }

    /** @test */
    public function test_executive_report_dossier_compilation(): void
    {
        $this->actingAs($this->cfo);

        $response = $this->get('/financial-reporting/executive-reports?cutoff_date=2026-01-31');
        $response->assertStatus(200);
        $response->assertSee('St. Jude General Hospital');
        $response->assertSee('Executive Financial');
        $response->assertSee('Condensed Statement of Financial Position');
        $response->assertSee('Condensed Statement of Comprehensive Income');
    }

    /** @test */
    public function test_financial_reporting_role_authorization_gates(): void
    {
        $clerk = User::factory()->create(['role' => 'BillingClerk', 'name' => 'Clerk']);
        $this->actingAs($clerk);

        // Billing Clerk cannot view Executive Dossier
        $res1 = $this->get('/financial-reporting/executive-reports');
        $res1->assertStatus(403);

        // CFO can view Executive Dossier
        $this->actingAs($this->cfo);
        $res2 = $this->get('/financial-reporting/executive-reports');
        $res2->assertStatus(200);
    }
}
