<?php

declare(strict_types=1);

namespace Tests\Feature\Accounting;

use App\Models\Account;
use App\Models\BankAccount;
use App\Models\BankDeposit;
use App\Models\CheckRegister;
use App\Models\DisbursementVoucher;
use App\Models\FiscalPeriod;
use App\Models\FundTransfer;
use App\Models\HmoClaim;
use App\Models\Invoice;
use App\Models\JournalEntry;
use App\Models\PatientAccount;
use App\Models\PayrollRun;
use App\Models\PurchaseBill;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class CashManagementModuleTest extends TestCase
{
    use RefreshDatabase;

    private User $accountant;
    private User $manager;
    private User $cfo;
    private User $auditor;
    private BankAccount $sourceBank;
    private BankAccount $destBank;
    private Account $sourceGl;
    private Account $destGl;

    protected function setUp(): void
    {
        parent::setUp();

        $this->accountant = User::factory()->create(['role' => 'StaffAccountant', 'name' => 'Treasury Staff', 'email' => 'treasury@hospital.local']);
        $this->manager = User::factory()->create(['role' => 'FinanceManager', 'name' => 'Finance Manager', 'email' => 'treasury.mgr@hospital.local']);
        $this->cfo = User::factory()->create(['role' => 'CFO', 'name' => 'Chief Financial Officer', 'email' => 'cfo@hospital.local']);
        $this->auditor = User::factory()->create(['role' => 'Auditor', 'name' => 'Internal Auditor', 'email' => 'auditor@hospital.local']);

        FiscalPeriod::create([
            'period_code'   => '2026-M01',
            'fiscal_year'   => '2026',
            'period_number' => 1,
            'start_date'    => '2026-01-01',
            'end_date'      => '2026-12-31',
            'status'        => 'OPEN',
        ]);

        $this->sourceGl = Account::create([
            'code'           => '1020',
            'name'           => 'Cash in Bank - Operations',
            'category'       => 'ASSET',
            'normal_balance' => 'DEBIT',
        ]);

        $this->destGl = Account::create([
            'code'           => '1021',
            'name'           => 'Cash in Bank - Reserve',
            'category'       => 'ASSET',
            'normal_balance' => 'DEBIT',
        ]);

        $this->sourceBank = BankAccount::create([
            'name'            => 'Main Operating & Payroll Account',
            'bank_name'       => 'Metrobank Pasig Branch',
            'account_number'  => 'MB-1020-001',
            'gl_code'         => '1020',
            'gl_account_id'   => $this->sourceGl->id,
            'purpose'         => 'Hospital Operations & Staff Payroll',
            'currency'        => 'PHP',
            'opening_balance' => '500000.0000',
            'balance'         => '500000.0000',
            'minimum_balance' => '100000.0000',
            'status'          => 'Active',
            'is_active'       => true,
        ]);

        $this->destBank = BankAccount::create([
            'name'            => 'Emergency Capital Reserve',
            'bank_name'       => 'BDO Unibank Makati Head Office',
            'account_number'  => 'BDO-1021-002',
            'gl_code'         => '1021',
            'gl_account_id'   => $this->destGl->id,
            'purpose'         => 'Capital Expenditure & Reserve',
            'currency'        => 'PHP',
            'opening_balance' => '200000.0000',
            'balance'         => '200000.0000',
            'minimum_balance' => '50000.0000',
            'status'          => 'Active',
            'is_active'       => true,
        ]);
    }

    /** @test */
    public function test_bank_account_crud_and_status_toggling(): void
    {
        $this->actingAs($this->accountant);

        // 1. Create New Bank Account
        $createPayload = [
            'name'            => 'HMO & PhilHealth Collections Account',
            'bank_name'       => 'Landbank of the Philippines',
            'account_number'  => 'LBP-9988-112',
            'gl_code'         => '1022',
            'purpose'         => 'Government Health Subsidies & HMO Settlements',
            'currency'        => 'PHP',
            'opening_balance' => 150000.00,
            'minimum_balance' => 30000.00,
            'status'          => 'Active',
        ];

        $createResponse = $this->post('/cash-management/bank-accounts', $createPayload);
        $createResponse->assertRedirect();

        $account = BankAccount::where('account_number', 'LBP-9988-112')->first();
        $this->assertNotNull($account);
        $this->assertEquals('150000.0000', $account->balance);
        $this->assertEquals('150000.0000', $account->opening_balance);

        // 2. Update Bank Account (Manager)
        $this->actingAs($this->manager);
        $updatePayload = [
            'name'            => 'HMO & PhilHealth Settlement Fund Updated',
            'bank_name'       => 'Landbank of the Philippines - Pasig',
            'account_number'  => 'LBP-9988-112',
            'gl_code'         => '1022',
            'purpose'         => 'Government Subsidies & Insurance',
            'minimum_balance' => 45000.00,
            'status'          => 'Active',
        ];

        $updateResponse = $this->put("/cash-management/bank-accounts/{$account->id}", $updatePayload);
        $updateResponse->assertRedirect();

        $account->refresh();
        $this->assertEquals('HMO & PhilHealth Settlement Fund Updated', $account->name);
        $this->assertEquals('45000.0000', $account->minimum_balance);

        // 3. Toggle Status
        $toggleResponse = $this->patch("/cash-management/bank-accounts/{$account->id}/toggle");
        $toggleResponse->assertRedirect();

        $account->refresh();
        $this->assertFalse($account->is_active);
        $this->assertEquals('Inactive', $account->status);
    }

    /** @test */
    public function test_cash_flow_forecasting_inflows_outflows_and_export(): void
    {
        $this->actingAs($this->accountant);

        $patient = PatientAccount::create([
            'patient_id_number' => 'MRN-2026-FC01',
            'full_name'         => 'Elena Cruz',
            'admission_type'    => 'Inpatient',
            'current_balance'   => '25000.0000',
            'credit_limit'      => '50000.0000',
            'status'            => 'Active',
        ]);

        $invoice = Invoice::create([
            'invoice_number'    => 'INV-FC-001',
            'patient_account_id'=> $patient->id,
            'invoice_date'      => now()->addDays(5)->toDateString(),
            'total_amount'      => '25000.0000',
            'patient_payable'   => '25000.0000',
            'status'            => 'ISSUED',
        ]);

        HmoClaim::create([
            'invoice_id'         => $invoice->id,
            'hmo_provider'       => 'Maxicare HealthCare',
            'loa_number'         => 'HMO-LOA-9901',
            'card_number'        => '1122-3344-55',
            'approved_limit'     => '55000.0000',
            'claimed_amount'     => '60000.0000',
            'settled_amount'     => '0.0000',
            'status'             => 'SUBMITTED',
        ]);

        $vendor = Vendor::create([
            'name'           => 'MediPharm Lab Supplies Inc',
            'vendor_code'    => 'VND-FC-01',
            'tax_id_number'  => '223-456-789-000',
            'classification' => 'Medical Equipment',
            'current_balance'=> '40000.0000',
            'is_active'      => true,
        ]);

        PurchaseBill::create([
            'bill_number'  => 'PB-FC-001',
            'vendor_id'    => $vendor->id,
            'bill_date'    => now()->toDateString(),
            'due_date'     => now()->addDays(12)->toDateString(),
            'total_amount' => '40000.0000',
            'paid_amount'  => '0.0000',
            'status'       => 'APPROVED',
        ]);

        PayrollRun::create([
            'payroll_run_number'          => 'PR-2026-FC01',
            'cutoff_start'                => now()->toDateString(),
            'cutoff_end'                  => now()->addDays(14)->toDateString(),
            'payout_date'                 => now()->addDays(15)->toDateString(),
            'total_gross_pay'             => '80000.0000',
            'total_statutory_deductions'  => '10000.0000',
            'total_net_pay'               => '70000.0000',
            'employee_count'              => 15,
            'status'                      => 'APPROVED',
        ]);

        // 1. Check Cash Horizon View
        $viewResponse = $this->get('/cash-management/cash-flow-forecasting?horizon=30');
        $viewResponse->assertStatus(200);
        $viewResponse->assertSee('INV-FC-001');
        $viewResponse->assertSee('HMO-LOA-9901');
        $viewResponse->assertSee('PB-FC-001');
        $viewResponse->assertSee('PR-2026-FC01');

        // 2. Test Streamed CSV Export
        $exportResponse = $this->get('/cash-management/cash-flow-forecasting/export?horizon=30');
        $exportResponse->assertStatus(200);
        $this->assertStringContainsString('text/csv', (string) $exportResponse->headers->get('Content-Type'));
    }

    /** @test */
    public function test_inter_account_fund_transfer_and_balanced_gl_posting(): void
    {
        $this->actingAs($this->manager);

        $transferPayload = [
            'source_bank_account_id'      => $this->sourceBank->id,
            'destination_bank_account_id' => $this->destBank->id,
            'amount'                      => 150000.00,
            'transfer_date'               => '2026-01-20',
            'transfer_method'             => 'INSTAPAY_PESONET',
            'memo'                        => 'Reserve capital reinforcement for equipment purchase',
        ];

        $response = $this->post('/cash-management/fund-transfers', $transferPayload);
        $response->assertRedirect();

        // 1. Verify Bank Account Balances:
        // Source (500,000 - 150,000 = 350,000)
        $this->sourceBank->refresh();
        $this->assertEquals('350000.0000', $this->sourceBank->balance);

        // Destination (200,000 + 150,000 = 350,000)
        $this->destBank->refresh();
        $this->assertEquals('350000.0000', $this->destBank->balance);

        // 2. Verify Fund Transfer Record
        $transfer = FundTransfer::where('source_bank_account_id', $this->sourceBank->id)->first();
        $this->assertNotNull($transfer);
        $this->assertEquals('150000.0000', $transfer->amount);
        $this->assertEquals('Completed & Posted', $transfer->status);

        // 3. Verify Balanced Double-Entry GL Journal Entry
        $je = JournalEntry::with('lines')->find($transfer->journal_entry_id);
        $this->assertNotNull($je);
        $this->assertEquals('POSTED', $je->status);

        $totalDebit = (string) $je->lines->sum('debit');
        $totalCredit = (string) $je->lines->sum('credit');
        $this->assertEquals(0, bccomp($totalDebit, $totalCredit, 4));
        $this->assertEquals(0, bccomp($totalDebit, '150000.0000', 4));
    }

    /** @test */
    public function test_bank_reconciliation_zero_variance_validation_and_clearing(): void
    {
        $this->actingAs($this->accountant);

        // Create Disbursement Voucher
        $voucher = DisbursementVoucher::create([
            'voucher_number'       => 'DV-2026-9901',
            'bank_account_id'      => $this->sourceBank->id,
            'voucher_date'         => '2026-01-20',
            'payee_name'           => 'Apex Medical Distributors',
            'gross_amount'         => '50000.0000',
            'withheld_tax_amount'  => '0.0000',
            'net_disbursed_amount' => '50000.0000',
            'payment_method'       => 'CHECK',
            'status'               => 'APPROVED',
        ]);

        // Create Uncleared Check
        $check = CheckRegister::create([
            'disbursement_voucher_id' => $voucher->id,
            'bank_account_id'         => $this->sourceBank->id,
            'check_number'            => 'CHK-2026-9901',
            'check_date'              => '2026-01-20',
            'payee_name'              => 'Apex Medical Distributors',
            'amount'                  => '50000.0000',
            'status'                  => 'ISSUED',
        ]);

        // Create Deposit in Transit
        $deposit = BankDeposit::create([
            'bank_account_id'   => $this->sourceBank->id,
            'deposit_reference' => 'DEP-2026-9901',
            'deposit_date'      => '2026-01-20',
            'cash_amount'       => '50000.0000',
            'total_deposited'   => '50000.0000',
            'status'            => 'PREPARED',
        ]);

        // 1. Workspace View
        $viewResponse = $this->get("/cash-management/bank-reconciliation?bank_account_id={$this->sourceBank->id}&cutoff_date=2026-01-20");
        $viewResponse->assertStatus(200);
        $viewResponse->assertSee('CHK-2026-9901');
        $viewResponse->assertSee('DEP-2026-9901');

        // 2. Post Reconciliation with Zero Variance
        $this->actingAs($this->manager);
        $reconPayload = [
            'bank_account_id'     => $this->sourceBank->id,
            'statement_date'      => '2026-01-20',
            'cutoff_date'         => '2026-01-20',
            'statement_balance'   => 500000.00,
            'book_balance'        => 500000.00,
            'cleared_check_ids'   => [$check->id],
            'cleared_deposit_ids' => [$deposit->id],
            'notes'               => 'Monthly statement verified against Metrobank machine statement.',
        ];

        $postResponse = $this->post('/cash-management/bank-reconciliation/post', $reconPayload);
        $postResponse->assertRedirect();

        $check->refresh();
        $this->assertEquals('CLEARED', $check->status);

        // 3. Reject Post with Non-Zero Variance
        $badPayload = array_merge($reconPayload, ['statement_balance' => 520000.00]);
        $badResponse = $this->post('/cash-management/bank-reconciliation/post', $badPayload);
        $badResponse->assertRedirect();
        $badResponse->assertSessionHas('error');
    }

    /** @test */
    public function test_liquidity_ratios_and_days_cash_on_hand(): void
    {
        $this->actingAs($this->cfo);

        $response = $this->get('/cash-management/liquidity-management');
        $response->assertStatus(200);
        $response->assertSee('Main Operating & Payroll Account');
        $response->assertSee('Emergency Capital Reserve');

        $exportResponse = $this->get('/cash-management/liquidity-management/export');
        $exportResponse->assertStatus(200);
        $this->assertStringContainsString('text/csv', (string) $exportResponse->headers->get('Content-Type'));
    }

    /** @test */
    public function test_cash_management_sod_role_authorization_gates(): void
    {
        // 1. Accountant cannot execute fund transfers
        $this->actingAs($this->accountant);

        $transferPayload = [
            'source_bank_account_id'      => $this->sourceBank->id,
            'destination_bank_account_id' => $this->destBank->id,
            'amount'                      => 10000.00,
            'transfer_date'               => '2026-01-20',
        ];

        $res1 = $this->post('/cash-management/fund-transfers', $transferPayload);
        $res1->assertStatus(403);

        // 2. Manager can execute fund transfers
        $this->actingAs($this->manager);
        $res2 = $this->post('/cash-management/fund-transfers', $transferPayload);
        $res2->assertRedirect();
    }
}
