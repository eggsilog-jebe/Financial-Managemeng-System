<?php

declare(strict_types=1);

namespace Tests\Feature\Accounting;

use App\Models\BankAccount;
use App\Models\CheckRegister;
use App\Models\DisbursementVoucher;
use App\Models\FiscalPeriod;
use App\Models\JournalEntry;
use App\Models\PayrollRun;
use App\Models\PettyCashExpense;
use App\Models\PettyCashFund;
use App\Models\PurchaseBill;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class DisbursementManagementModuleTest extends TestCase
{
    use RefreshDatabase;

    private User $cfo;
    private User $manager;
    private User $auditor;
    private User $accountant;
    private User $cashier;
    private BankAccount $bank;

    protected function setUp(): void
    {
        parent::setUp();

        $this->cfo = User::factory()->create(['role' => 'CFO', 'name' => 'CFO Exec', 'email' => 'cfo@hospital.local']);
        $this->manager = User::factory()->create(['role' => 'FinanceManager', 'name' => 'Finance Manager', 'email' => 'manager@hospital.local']);
        $this->auditor = User::factory()->create(['role' => 'Auditor', 'name' => 'Internal Auditor', 'email' => 'auditor@hospital.local']);
        $this->accountant = User::factory()->create(['role' => 'StaffAccountant', 'name' => 'Disbursement Accountant', 'email' => 'accountant@hospital.local']);
        $this->cashier = User::factory()->create(['role' => 'Cashier', 'name' => 'Hospital Cashier', 'email' => 'cashier@hospital.local']);

        FiscalPeriod::create([
            'period_code'   => '2026-M01',
            'fiscal_year'   => '2026',
            'period_number' => 1,
            'start_date'    => '2026-01-01',
            'end_date'      => '2026-12-31',
            'status'        => 'OPEN',
        ]);

        $this->bank = BankAccount::create([
            'name'           => 'Operating Treasury Account',
            'bank_name'      => 'Metrobank Pasig',
            'account_number' => '1029-9940-11',
            'gl_code'        => '1020',
            'purpose'        => 'Disbursement Payouts',
            'balance'        => '1000000.0000',
            'status'         => 'Active',
        ]);
    }

    /** @test */
    public function test_payment_request_creation_audit_and_void(): void
    {
        $this->actingAs($this->accountant);

        // 1. Create Payment Request
        $payload = [
            'bank_account_id' => $this->bank->id,
            'voucher_date'    => '2026-01-15',
            'payee_name'      => 'Bio-Rad Laboratories Inc',
            'description'     => 'Quarterly maintenance for diagnostic analyzer',
            'gross_amount'    => 45000.00,
            'payment_method'  => 'CHECK',
        ];

        $storeResponse = $this->post('/disbursement-management/payment-requests', $payload);
        $storeResponse->assertRedirect();

        $voucher = DisbursementVoucher::where('payee_name', 'Bio-Rad Laboratories Inc')->first();
        $this->assertNotNull($voucher);
        $this->assertEquals('PREPARED', $voucher->status);
        $this->assertEquals('45000.0000', $voucher->net_disbursed_amount);

        // 2. Audit Requisition (Internal Auditor)
        $this->actingAs($this->auditor);
        $auditResponse = $this->post("/disbursement-management/payment-requests/{$voucher->id}/audit");
        $auditResponse->assertRedirect();

        $voucher->refresh();
        $this->assertEquals('AUDITED', $voucher->status);
        $this->assertEquals($this->auditor->id, $voucher->audited_by);

        // 3. Void Requisition
        $voidResponse = $this->post("/disbursement-management/payment-requests/{$voucher->id}/void", ['reason' => 'Duplicate requisition']);
        $voidResponse->assertRedirect();

        $voucher->refresh();
        $this->assertEquals('VOIDED', $voucher->status);
    }

    /** @test */
    public function test_check_register_issuance_print_layout_and_clearing(): void
    {
        $this->actingAs($this->accountant);

        $voucher = DisbursementVoucher::create([
            'voucher_number'       => 'DV-CHK-TEST-01',
            'bank_account_id'      => $this->bank->id,
            'voucher_date'         => '2026-01-15',
            'payee_name'           => 'Philippine Heart Diagnostics Co',
            'gross_amount'         => '125000.5000',
            'withheld_tax_amount'  => '0.0000',
            'net_disbursed_amount' => '125000.5000',
            'payment_method'       => 'CHECK',
            'status'               => 'APPROVED',
            'approved_by'          => $this->manager->id,
        ]);

        // 1. Issue Check
        $checkPayload = [
            'disbursement_voucher_id' => $voucher->id,
            'bank_account_id'         => $this->bank->id,
            'check_number'            => 'CHK-2026-880011',
            'check_date'              => '2026-01-15',
            'payee_name'              => $voucher->payee_name,
            'amount'                  => 125000.50,
        ];

        $issueResponse = $this->post('/disbursement-management/check-register', $checkPayload);
        $issueResponse->assertRedirect();

        $check = CheckRegister::where('check_number', 'CHK-2026-880011')->first();
        $this->assertNotNull($check);
        $this->assertEquals('ISSUED', $check->status);
        $this->assertStringContainsString('One Hundred Twenty-Five Thousand Pesos and 50/100 Only', $check->amount_in_words);

        // 2. Render Check Print View
        $printResponse = $this->get("/disbursement-management/check-register/{$check->id}/print");
        $printResponse->assertStatus(200);
        $printResponse->assertSee('CHK-2026-880011');
        $printResponse->assertSee('Philippine Heart Diagnostics Co');
        $printResponse->assertSee('One Hundred Twenty-Five Thousand');

        // 3. Clear Check
        $clearResponse = $this->post("/disbursement-management/check-register/{$check->id}/clear");
        $clearResponse->assertRedirect();

        $check->refresh();
        $this->assertEquals('CLEARED', $check->status);
        $this->assertNotNull($check->cleared_at);
    }

    /** @test */
    public function test_eft_transfers_batch_csv_export(): void
    {
        $this->actingAs($this->accountant);

        DisbursementVoucher::create([
            'voucher_number'       => 'DV-EFT-PN-01',
            'bank_account_id'      => $this->bank->id,
            'voucher_date'         => '2026-01-18',
            'payee_name'           => 'Doctor Alfonso Martinez (PF)',
            'gross_amount'         => '85000.0000',
            'net_disbursed_amount' => '85000.0000',
            'payment_method'       => 'PESONET_EFT',
            'status'               => 'APPROVED',
        ]);

        // 1. View EFT Hub
        $viewResponse = $this->get('/disbursement-management/eft-transfers');
        $viewResponse->assertStatus(200);
        $viewResponse->assertSee('Doctor Alfonso Martinez');

        // 2. Export NACHA / Bank Batch CSV
        $exportResponse = $this->get('/disbursement-management/eft-transfers/export');
        $exportResponse->assertStatus(200);
        $exportResponse->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
    }

    /** @test */
    public function test_disbursement_approval_and_release_settlement(): void
    {
        $vendor = Vendor::create(['code' => 'VND-MED-01', 'name' => 'B. Braun Medical Supplies']);
        $bill = PurchaseBill::create([
            'bill_number'  => 'BILL-BB-01',
            'vendor_id'    => $vendor->id,
            'bill_date'    => '2026-01-10',
            'due_date'     => '2026-02-10',
            'total_amount' => '200000.0000',
            'paid_amount'  => '0.0000',
            'status'       => 'APPROVED',
        ]);

        $voucher = DisbursementVoucher::create([
            'voucher_number'       => 'DV-SETTLE-01',
            'purchase_bill_id'     => $bill->id,
            'bank_account_id'      => $this->bank->id,
            'voucher_date'         => '2026-01-20',
            'payee_name'           => $vendor->name,
            'gross_amount'         => '200000.0000',
            'withheld_tax_amount'  => '0.0000',
            'net_disbursed_amount' => '200000.0000',
            'payment_method'       => 'CHECK',
            'status'               => 'AUDITED',
        ]);

        // 1. Finance Manager Approval
        $this->actingAs($this->manager);
        $appResponse = $this->post("/disbursement-management/disbursement-approvals/{$voucher->id}/approve");
        $appResponse->assertRedirect();

        $voucher->refresh();
        $this->assertEquals('APPROVED', $voucher->status);

        // 2. CFO Release & Settlement
        $this->actingAs($this->cfo);
        $releasePayload = [
            'check_number' => 'CHK-2026-779911',
            'check_date'   => '2026-01-20',
            'notes'        => 'Released via Courier to B. Braun Pasig Depot',
        ];

        $relResponse = $this->post("/disbursement-management/disbursement-approvals/{$voucher->id}/release", $releasePayload);
        $relResponse->assertRedirect();

        $voucher->refresh();
        $this->assertEquals('RELEASED', $voucher->status);
        $this->assertEquals('CHK-2026-779911', $voucher->check_or_eft_ref);

        // Bank balance decreased (1,000,000 - 200,000 = 800,000)
        $this->bank->refresh();
        $this->assertEquals('800000.0000', $this->bank->balance);

        // Purchase Bill paid
        $bill->refresh();
        $this->assertEquals('200000.0000', $bill->paid_amount);
        $this->assertEquals('PAID', $bill->status);

        // Check Register created
        $check = CheckRegister::where('check_number', 'CHK-2026-779911')->first();
        $this->assertNotNull($check);
        $this->assertEquals('RELEASED', $check->status);

        // Balanced GL Journal Entry
        $je = JournalEntry::with('lines')->where('reference_number', 'JE-DISB-' . $voucher->voucher_number)->first();
        $this->assertNotNull($je);
        $this->assertEquals('POSTED', $je->status);

        $totalDebit = (string) $je->lines->sum('debit');
        $totalCredit = (string) $je->lines->sum('credit');
        $this->assertEquals(0, bccomp($totalDebit, $totalCredit, 4));
        $this->assertEquals(0, bccomp($totalDebit, '200000.0000', 4));
    }

    /** @test */
    public function test_petty_cash_expense_recording_and_fund_replenishment(): void
    {
        $this->actingAs($this->cashier);

        $fund = PettyCashFund::create([
            'fund_name'       => 'Hospital ER Petty Cash',
            'custodian_name'  => 'Nurse Supervisor ER',
            'float_limit'     => '20000.0000',
            'current_balance' => '20000.0000',
            'gl_code'         => '1030',
            'status'          => 'Active',
        ]);

        // 1. Record Expense Slip
        $expensePayload = [
            'petty_cash_fund_id' => $fund->id,
            'expense_date'       => '2026-01-18',
            'payee'              => 'Quick Courier Express',
            'department'         => 'CLINICAL',
            'particulars'        => 'Emergency blood units transport from Red Cross',
            'amount'             => 3500.00,
            'receipt_ref'        => 'OR-RC-99881',
        ];

        $expResponse = $this->post('/disbursement-management/petty-cash/expense', $expensePayload);
        $expResponse->assertRedirect();

        $expense = PettyCashExpense::where('petty_cash_fund_id', $fund->id)->first();
        $this->assertNotNull($expense);
        $this->assertEquals('UNREPLENISHED', $expense->status);

        $fund->refresh();
        $this->assertEquals('16500.0000', $fund->current_balance);

        // 2. Replenish Fund (Manager)
        $this->actingAs($this->manager);
        $replPayload = [
            'fund_id'         => $fund->id,
            'bank_account_id' => $this->bank->id,
        ];

        $replResponse = $this->post('/disbursement-management/petty-cash/replenish', $replPayload);
        $replResponse->assertRedirect();

        $expense->refresh();
        $this->assertEquals('REPLENISHED', $expense->status);

        $fund->refresh();
        $this->assertEquals('20000.0000', $fund->current_balance); // Restored to float limit

        $replVoucher = DisbursementVoucher::where('id', $expense->disbursement_voucher_id)->first();
        $this->assertNotNull($replVoucher);
        $this->assertEquals('3500.0000', $replVoucher->gross_amount);

        // Assert Balanced GL Journal Entry
        $je = JournalEntry::with('lines')->where('reference_number', 'JE-PC-' . $replVoucher->voucher_number)->first();
        $this->assertNotNull($je);
        $this->assertEquals('POSTED', $je->status);
    }

    /** @test */
    public function test_disbursement_role_authorization_and_sod(): void
    {
        $cashier = User::factory()->create(['role' => 'Cashier', 'name' => 'Cashier']);

        // Cashier cannot create general payment requests
        $this->actingAs($cashier);
        $res1 = $this->get('/disbursement-management/payment-requests');
        $res1->assertStatus(403);

        // Staff Accountant can create payment requests, but cannot audit
        $this->actingAs($this->accountant);
        $res2 = $this->get('/disbursement-management/payment-requests');
        $res2->assertStatus(200);

        $voucher = DisbursementVoucher::create([
            'voucher_number'       => 'DV-SOD-001',
            'bank_account_id'      => $this->bank->id,
            'voucher_date'         => '2026-01-20',
            'payee_name'           => 'Vendor SOD',
            'gross_amount'         => '1000.0000',
            'net_disbursed_amount' => '1000.0000',
            'status'               => 'PREPARED',
        ]);

        $res3 = $this->post("/disbursement-management/payment-requests/{$voucher->id}/audit");
        $res3->assertStatus(403);

        // Auditor can audit
        $this->actingAs($this->auditor);
        $res4 = $this->post("/disbursement-management/payment-requests/{$voucher->id}/audit");
        $res4->assertRedirect();

        // Finance Manager can approve, but cannot release
        $this->actingAs($this->manager);
        $res5 = $this->post("/disbursement-management/disbursement-approvals/{$voucher->id}/approve");
        $res5->assertRedirect();

        $res6 = $this->post("/disbursement-management/disbursement-approvals/{$voucher->id}/release", ['check_number' => 'CHK-SOD']);
        $res6->assertStatus(403);

        // CFO can release
        $this->actingAs($this->cfo);
        $res7 = $this->post("/disbursement-management/disbursement-approvals/{$voucher->id}/release", ['check_number' => 'CHK-SOD']);
        $res7->assertRedirect();
    }
}
