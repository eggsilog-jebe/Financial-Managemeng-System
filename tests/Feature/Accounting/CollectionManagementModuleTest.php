<?php

declare(strict_types=1);

namespace Tests\Feature\Accounting;

use App\Models\Account;
use App\Models\BankAccount;
use App\Models\BankDeposit;
use App\Models\CashierShift;
use App\Models\FiscalPeriod;
use App\Models\Invoice;
use App\Models\JournalEntry;
use App\Models\OfficialReceipt;
use App\Models\PatientAccount;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class CollectionManagementModuleTest extends TestCase
{
    use RefreshDatabase;

    private User $cashier;
    private User $accountant;
    private User $manager;
    private User $cfo;
    private User $auditor;
    private BankAccount $bank;
    private PatientAccount $patient;
    private Invoice $invoice;

    protected function setUp(): void
    {
        parent::setUp();

        $this->cashier = User::factory()->create(['role' => 'Cashier', 'name' => 'Front Desk Cashier', 'email' => 'cashier@hospital.local']);
        $this->accountant = User::factory()->create(['role' => 'StaffAccountant', 'name' => 'Treasury Staff', 'email' => 'accountant@hospital.local']);
        $this->manager = User::factory()->create(['role' => 'FinanceManager', 'name' => 'Finance Manager', 'email' => 'manager@hospital.local']);
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

        $this->bank = BankAccount::create([
            'name'           => 'Main Operating Bank',
            'bank_name'      => 'Metrobank Pasig Branch',
            'account_number' => '1029-9940-11',
            'gl_code'        => '1020',
            'purpose'        => 'Hospital Collections & Clearing',
            'balance'        => '250000.0000',
            'status'         => 'Active',
        ]);

        $this->patient = PatientAccount::create([
            'patient_id_number' => 'MRN-2026-0991',
            'full_name'         => 'Ricardo Dalisay',
            'admission_type'    => 'Inpatient',
            'current_balance'   => '15000.0000',
            'credit_limit'      => '50000.0000',
            'status'            => 'Active',
        ]);

        $this->invoice = Invoice::create([
            'invoice_number'   => 'INV-2026-0091',
            'patient_account_id'=> $this->patient->id,
            'invoice_date'     => '2026-01-15',
            'total_amount'     => '15000.0000',
            'patient_payable'  => '15000.0000',
            'status'           => 'ISSUED',
        ]);
    }

    /** @test */
    public function test_cashier_shift_lifecycle_open_close_and_reconcile(): void
    {
        $this->actingAs($this->cashier);

        // 1. Open Shift
        $openPayload = [
            'terminal_name'      => 'POS-MAIN-02',
            'opening_cash_float' => 5000.00,
        ];

        $openResponse = $this->post('/collection-management/shifts/open', $openPayload);
        $openResponse->assertRedirect();

        $shift = CashierShift::where('cashier_id', $this->cashier->id)->where('status', 'OPEN')->first();
        $this->assertNotNull($shift);
        $this->assertEquals('5000.0000', $shift->opening_cash_float);
        $this->assertEquals('5000.0000', $shift->expected_cash);

        // 2. Close Shift (with Cash Counted)
        $closePayload = [
            'shift_id'            => $shift->id,
            'actual_cash_counted' => 5200.00,
            'variance_reason'     => 'Minor cash overage',
        ];

        $closeResponse = $this->post('/collection-management/shifts/close', $closePayload);
        $closeResponse->assertRedirect();

        $shift->refresh();
        $this->assertEquals('CLOSED', $shift->status);
        $this->assertEquals('5200.0000', $shift->actual_cash_counted);
        $this->assertEquals('200.0000', $shift->cash_variance); // +200 overage

        // 3. Supervisor Reconciliation
        $this->actingAs($this->manager);
        $reconResponse = $this->post("/collection-management/shifts/{$shift->id}/reconcile");
        $reconResponse->assertRedirect();

        $shift->refresh();
        $this->assertEquals('RECONCILED', $shift->status);
    }

    /** @test */
    public function test_pos_payment_collection_change_calculation_and_gl_posting(): void
    {
        $this->actingAs($this->cashier);

        $shift = CashierShift::create([
            'shift_code'         => 'SHIFT-2026-TEST-01',
            'cashier_id'         => $this->cashier->id,
            'terminal_name'      => 'POS-MAIN-01',
            'opened_at'          => now(),
            'opening_cash_float' => '3000.0000',
            'expected_cash'      => '3000.0000',
            'status'             => 'OPEN',
        ]);

        $collectPayload = [
            'invoice_id'       => $this->invoice->id,
            'cashier_shift_id' => $shift->id,
            'payment_method'   => 'CASH',
            'amount'           => 10000.00,
            'tendered_amount'  => 10000.00,
            'payor_name'       => 'Ricardo Dalisay',
        ];

        $response = $this->post('/collection-management/cashier-desk/collect', $collectPayload);
        $response->assertRedirect();

        // 1. Verify Payment & OR Records
        $payment = Payment::where('invoice_id', $this->invoice->id)->first();
        $this->assertNotNull($payment);
        $this->assertEquals('10000.0000', $payment->amount);
        $this->assertEquals('CASH', $payment->payment_method);

        $or = OfficialReceipt::where('payment_id', $payment->id)->first();
        $this->assertNotNull($or);
        $this->assertEquals('VALID', $or->status);
        $this->assertEquals('10000.0000', $or->total_amount_collected);

        // 2. Verify Invoice & Patient Balances
        $this->invoice->refresh();
        $this->assertEquals('5000.0000', $this->invoice->patient_payable);
        $this->assertEquals('PARTIAL', $this->invoice->status);

        $this->patient->refresh();
        $this->assertEquals('5000.0000', $this->patient->current_balance);

        // 3. Verify Shift Balances
        $shift->refresh();
        $this->assertEquals('13000.0000', $shift->expected_cash); // 3000 float + 10000 collected
        $this->assertEquals('10000.0000', $shift->total_collections);

        // 4. Verify Balanced GL Journal Entry
        $je = JournalEntry::with('lines')->where('reference_number', 'JE-COL-' . $payment->payment_reference)->first();
        $this->assertNotNull($je);
        $this->assertEquals('POSTED', $je->status);

        $totalDebit = (string) $je->lines->sum('debit');
        $totalCredit = (string) $je->lines->sum('credit');
        $this->assertEquals(0, bccomp($totalDebit, $totalCredit, 4));
        $this->assertEquals(0, bccomp($totalDebit, '10000.0000', 4));
    }

    /** @test */
    public function test_payment_receipt_print_and_reversal_voiding(): void
    {
        $this->actingAs($this->cashier);

        $payment = Payment::create([
            'payment_reference'  => 'PAY-2026-PRINT-01',
            'invoice_id'         => $this->invoice->id,
            'patient_account_id' => $this->patient->id,
            'payment_date'       => '2026-01-15',
            'amount'             => '15000.0000',
            'payment_method'     => 'CASH',
            'payment_type'       => 'PATIENT_COPAY',
        ]);

        $or = OfficialReceipt::create([
            'or_number'              => 'OR-2026-000991',
            'payment_id'             => $payment->id,
            'invoice_id'             => $this->invoice->id,
            'patient_account_id'     => $this->patient->id,
            'or_date'                => '2026-01-15',
            'payor_name'             => 'Ricardo Dalisay',
            'total_amount_collected' => '15000.0000',
            'status'                 => 'VALID',
        ]);

        $this->invoice->update(['patient_payable' => '0.0000', 'status' => 'SETTLED']);
        $this->patient->update(['current_balance' => '0.0000']);

        // 1. Render BIR EOPT Print View
        $printResponse = $this->get("/collection-management/payment-receipts/{$payment->id}/print");
        $printResponse->assertStatus(200);
        $printResponse->assertSee('OR-2026-000991');
        $printResponse->assertSee('Ricardo Dalisay');

        // 2. Void Payment (Manager authorization)
        $this->actingAs($this->manager);
        $voidResponse = $this->post("/collection-management/payment-receipts/{$payment->id}/void", [
            'reason' => 'Erroneous cashier tender entry',
        ]);
        $voidResponse->assertRedirect();

        $or->refresh();
        $this->assertEquals('CANCELLED', $or->status);

        $this->invoice->refresh();
        $this->assertEquals('15000.0000', $this->invoice->patient_payable);
        $this->assertEquals('PARTIAL', $this->invoice->status);

        // Assert Reversing Journal Entry
        $revJe = JournalEntry::with('lines')->where('reference_number', 'JE-REV-' . $payment->payment_reference)->first();
        $this->assertNotNull($revJe);
        $this->assertEquals('POSTED', $revJe->status);
    }

    /** @test */
    public function test_bank_deposit_creation_and_clearing(): void
    {
        $this->actingAs($this->accountant);

        // 1. Create Deposit Slip
        $depPayload = [
            'bank_account_id' => $this->bank->id,
            'deposit_date'    => '2026-01-16',
            'cash_amount'     => 75000.00,
            'check_amount'    => 25000.00,
        ];

        $createResponse = $this->post('/collection-management/bank-deposits', $depPayload);
        $createResponse->assertRedirect();

        $deposit = BankDeposit::where('bank_account_id', $this->bank->id)->first();
        $this->assertNotNull($deposit);
        $this->assertEquals('PREPARED', $deposit->status);
        $this->assertEquals('100000.0000', $deposit->total_deposited);

        // 2. Clear & Validate Deposit (Manager / CFO)
        $this->actingAs($this->manager);
        $clearPayload = [
            'bank_reference_number' => 'MB-DEP-991823',
            'validated_by_teller'   => 'Teller #04 Pasig',
        ];

        $clearResponse = $this->post("/collection-management/bank-deposits/{$deposit->id}/clear", $clearPayload);
        $clearResponse->assertRedirect();

        $deposit->refresh();
        $this->assertEquals('DEPOSITED', $deposit->status);
        $this->assertEquals('MB-DEP-991823', $deposit->bank_reference_number);

        // Bank balance incremented (250,000 + 100,000 = 350,000)
        $this->bank->refresh();
        $this->assertEquals('350000.0000', $this->bank->balance);

        // Balanced GL entry ($DR 1020 Bank, $CR 1011 Undeposited)
        $je = JournalEntry::with('lines')->where('reference_number', 'JE-DEP-' . $deposit->deposit_reference)->first();
        $this->assertNotNull($je);
        $this->assertEquals('POSTED', $je->status);

        $totalDebit = (string) $je->lines->sum('debit');
        $totalCredit = (string) $je->lines->sum('credit');
        $this->assertEquals(0, bccomp($totalDebit, $totalCredit, 4));
        $this->assertEquals(0, bccomp($totalDebit, '100000.0000', 4));
    }

    /** @test */
    public function test_payment_gateway_logs_and_gl_retrigger(): void
    {
        $this->actingAs($this->accountant);

        $digitalPayment = Payment::create([
            'payment_reference'       => 'PAY-EWALLET-01',
            'invoice_id'              => $this->invoice->id,
            'patient_account_id'      => $this->patient->id,
            'payment_date'            => '2026-01-18',
            'amount'                  => '8000.0000',
            'payment_method'          => 'GCASH',
            'transaction_channel_ref' => 'GCASH-REF-889911',
            'payment_type'            => 'PATIENT_COPAY',
        ]);

        // View Gateway Stream
        $viewResponse = $this->get('/collection-management/payment-gateway-logs');
        $viewResponse->assertStatus(200);
        $viewResponse->assertSee('GCASH-REF-889911');

        // Re-Trigger GL
        $retriggerResponse = $this->post("/collection-management/payment-gateway-logs/{$digitalPayment->id}/retrigger-gl");
        $retriggerResponse->assertRedirect();

        $je = JournalEntry::where('description', 'LIKE', "%{$digitalPayment->payment_reference}%")->first();
        $this->assertNotNull($je);
        $this->assertEquals('POSTED', $je->status);
    }

    /** @test */
    public function test_collection_role_authorization_and_sod(): void
    {
        // 1. Cashier cannot void official receipts
        $this->actingAs($this->cashier);

        $payment = Payment::create([
            'payment_reference'  => 'PAY-SOD-TEST',
            'patient_account_id' => $this->patient->id,
            'payment_date'       => '2026-01-18',
            'amount'             => '1000.0000',
            'payment_method'     => 'CASH',
        ]);

        $res1 = $this->post("/collection-management/payment-receipts/{$payment->id}/void", ['reason' => 'Test']);
        $res1->assertStatus(403);

        // 2. Cashier cannot clear bank deposits
        $deposit = BankDeposit::create([
            'deposit_reference' => 'DEP-SOD-TEST',
            'bank_account_id'   => $this->bank->id,
            'deposit_date'      => '2026-01-18',
            'cash_amount'       => '5000.0000',
            'total_deposited'   => '5000.0000',
            'status'            => 'PREPARED',
        ]);

        $res2 = $this->post("/collection-management/bank-deposits/{$deposit->id}/clear", ['bank_reference_number' => 'REF']);
        $res2->assertStatus(403);

        // 3. Manager can clear bank deposits
        $this->actingAs($this->manager);
        $res3 = $this->post("/collection-management/bank-deposits/{$deposit->id}/clear", ['bank_reference_number' => 'REF-OK']);
        $res3->assertRedirect();
    }
}
