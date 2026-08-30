<?php

declare(strict_types=1);

namespace Tests\Feature\Accounting;

use App\Models\CreditNote;
use App\Models\FiscalPeriod;
use App\Models\Invoice;
use App\Models\JournalEntry;
use App\Models\PatientAccount;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class AccountsReceivableModuleTest extends TestCase
{
    use RefreshDatabase;

    private User $cfo;
    private User $manager;
    private User $accountant;
    private User $billingClerk;

    protected function setUp(): void
    {
        parent::setUp();

        $this->cfo = User::factory()->create(['role' => 'CFO', 'name' => 'CFO Exec', 'email' => 'cfo@hospital.local']);
        $this->manager = User::factory()->create(['role' => 'FinanceManager', 'name' => 'Finance Manager', 'email' => 'manager@hospital.local']);
        $this->accountant = User::factory()->create(['role' => 'StaffAccountant', 'name' => 'AR Accountant', 'email' => 'accountant@hospital.local']);
        $this->billingClerk = User::factory()->create(['role' => 'BillingClerk', 'name' => 'Billing Specialist', 'email' => 'billing@hospital.local']);

        // Open fiscal period
        FiscalPeriod::create([
            'period_code'   => '2026-M01',
            'fiscal_year'   => '2026',
            'period_number' => 1,
            'start_date'    => '2026-01-01',
            'end_date'      => '2026-12-31',
            'status'        => 'OPEN',
        ]);
    }

    /** @test */
    public function test_patient_account_registration_and_validation(): void
    {
        $this->actingAs($this->billingClerk);

        // 1. View Directory
        $response = $this->get('/accounts-receivable/patients');
        $response->assertStatus(200);

        // 2. Register Patient
        $payload = [
            'patient_mrn'    => 'MRN-2026-88019',
            'full_name'      => 'Maria Clara Del Rosario',
            'admission_type' => 'Inpatient',
            'hmo_provider'   => 'Maxicare HealthCare',
            'phone'          => '+63 917 555 1234',
            'email'          => 'maria.clara@gmail.com',
            'address'        => 'Unit 802 Ayala Towers, Makati City',
            'status'         => 'Active',
        ];

        $postResponse = $this->post('/accounts-receivable/patients', $payload);
        $postResponse->assertRedirect();

        $patient = PatientAccount::where('patient_id_number', 'MRN-2026-88019')->first();
        $this->assertNotNull($patient);
        $this->assertEquals('Maria Clara Del Rosario', $patient->full_name);
        $this->assertEquals('Inpatient', $patient->admission_type);
        $this->assertEquals('Maxicare HealthCare', $patient->hmo_provider);
        $this->assertEquals('+63 917 555 1234', $patient->phone);
    }

    /** @test */
    public function test_patient_invoice_creation_with_statutory_discounts_and_gl_posting(): void
    {
        $this->actingAs($this->accountant);

        $patient = PatientAccount::create([
            'patient_id_number' => 'MRN-SENIOR-01',
            'full_name'         => 'Eduardo Ramos (Senior Citizen)',
            'admission_type'    => 'Inpatient',
            'hmo_provider'      => 'Intellicare',
            'status'            => 'Active',
        ]);

        $payload = [
            'patient_account_id'                  => $patient->id,
            'invoice_date'                        => '2026-01-15',
            'due_date'                            => '2026-02-15',
            'discount_type'                       => 'SENIOR_CITIZEN',
            'id_card_number'                      => 'OSCA-99210-PASIG',
            'philhealth_primary_case_rate_amount' => 10000.00,
            'hmo_provider'                        => 'Intellicare',
            'hmo_approved_limit'                  => 15000.00,
            'items'                               => [
                [
                    'item_code'              => 'ROOM-PRV-01',
                    'description'            => 'Private Deluxe Room 5 Days',
                    'department'             => 'CLINICAL',
                    'quantity'               => 5,
                    'unit_price'             => 5000.00, // 25,000 gross
                    'is_vatable'             => true,
                    'is_senior_pwd_eligible' => true,
                ],
                [
                    'item_code'              => 'LAB-CBC-01',
                    'description'            => 'Complete Blood Count & Electrolytes',
                    'department'             => 'LIS',
                    'quantity'               => 2,
                    'unit_price'             => 2500.00, // 5,000 gross
                    'is_vatable'             => true,
                    'is_senior_pwd_eligible' => true,
                ],
            ], // Total Gross = 30,000.00
        ];

        $response = $this->post('/accounts-receivable/invoices', $payload);
        $response->assertRedirect();

        $invoice = Invoice::with(['items', 'philhealthClaim', 'hmoClaims', 'statutoryDiscounts'])->where('patient_account_id', $patient->id)->first();
        $this->assertNotNull($invoice);
        $this->assertEquals('30000.0000', $invoice->total_amount);

        // Assert Statutory Discounts & Deductions
        $this->assertNotNull($invoice->statutoryDiscounts->first());
        $this->assertNotNull($invoice->philhealthClaim);
        $this->assertNotNull($invoice->hmoClaims->first());

        // Assert Patient Account Updated Balances
        $patient->refresh();
        $this->assertEquals('30000.0000', $patient->total_billed);
        $this->assertEquals($invoice->patient_payable, $patient->current_balance);

        // Assert Balanced General Ledger Journal Entry
        $je = JournalEntry::with('lines')->where('reference_number', 'JE-REV-' . $invoice->invoice_number)->first();
        $this->assertNotNull($je);
        $this->assertEquals('POSTED', $je->status);

        $totalDebit = (string) $je->lines->sum('debit');
        $totalCredit = (string) $je->lines->sum('credit');
        $this->assertEquals(0, bccomp($totalDebit, $totalCredit, 4));
        $this->assertEquals(0, bccomp($totalDebit, '30000.0000', 4));
    }

    /** @test */
    public function test_patient_invoice_print_rendering(): void
    {
        $this->actingAs($this->accountant);

        $patient = PatientAccount::create([
            'patient_id_number' => 'MRN-PRINT-01',
            'full_name'         => 'Corazon Aquino-Test',
            'admission_type'    => 'Inpatient',
            'status'            => 'Active',
        ]);

        $invoice = Invoice::create([
            'invoice_number'     => 'INV-PRINT-999',
            'patient_account_id' => $patient->id,
            'invoice_date'       => '2026-01-20',
            'total_amount'       => '15000.0000',
            'insurance_covered'  => '5000.0000',
            'discount_amount'    => '1000.0000',
            'patient_payable'    => '9000.0000',
            'paid_amount'        => '0.0000',
            'status'             => 'UNPAID',
        ]);

        $response = $this->get("/accounts-receivable/invoices/{$invoice->id}/print");
        $response->assertStatus(200);
        $response->assertSee('Billing Statement');
        $response->assertSee('Corazon Aquino-Test');
        $response->assertSee('INV-PRINT-999');
    }

    /** @test */
    public function test_receivable_aging_schedule_calculation_and_csv_export(): void
    {
        $this->actingAs($this->accountant);

        $patient1 = PatientAccount::create(['patient_id_number' => 'MRN-AGE-01', 'full_name' => 'Current Patient', 'current_balance' => '10000.0000']);
        $patient2 = PatientAccount::create(['patient_id_number' => 'MRN-AGE-02', 'full_name' => 'Overdue Patient', 'current_balance' => '25000.0000']);

        // Current invoice (10 days old as of 2026-01-20)
        Invoice::create([
            'invoice_number'     => 'INV-CURR-01',
            'patient_account_id' => $patient1->id,
            'invoice_date'       => '2026-01-10',
            'total_amount'       => '10000.0000',
            'patient_payable'    => '10000.0000',
            'paid_amount'        => '0.0000',
            'status'             => 'UNPAID',
        ]);

        // 45 days overdue invoice (as of 2026-01-20)
        Invoice::create([
            'invoice_number'     => 'INV-OVD-01',
            'patient_account_id' => $patient2->id,
            'invoice_date'       => '2025-12-06',
            'total_amount'       => '25000.0000',
            'patient_payable'    => '25000.0000',
            'paid_amount'        => '0.0000',
            'status'             => 'UNPAID',
        ]);

        // 1. View HTML Table
        $response = $this->get('/accounts-receivable/receivable-aging?as_of_date=2026-01-20');
        $response->assertStatus(200);
        $response->assertSee('Current Patient');
        $response->assertSee('Overdue Patient');

        // 2. Export CSV
        $exportResponse = $this->get('/accounts-receivable/receivable-aging/export?as_of_date=2026-01-20');
        $exportResponse->assertStatus(200);
        $exportResponse->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
    }

    /** @test */
    public function test_credit_note_creation_approval_and_gl_settlement(): void
    {
        $this->actingAs($this->billingClerk);

        $patient = PatientAccount::create([
            'patient_id_number' => 'MRN-CN-01',
            'full_name'         => 'Lourdes Bautista',
            'current_balance'   => '10000.0000',
            'status'            => 'Active',
        ]);

        $invoice = Invoice::create([
            'invoice_number'     => 'INV-CN-01',
            'patient_account_id' => $patient->id,
            'invoice_date'       => '2026-01-10',
            'total_amount'       => '10000.0000',
            'patient_payable'    => '10000.0000',
            'paid_amount'        => '0.0000',
            'discount_amount'    => '0.0000',
            'status'             => 'UNPAID',
        ]);

        // 1. Create Credit Note (Billing Clerk)
        $cnPayload = [
            'invoice_id' => $invoice->id,
            'amount'     => 2000.00,
            'reason'     => 'CHARITY_SUBSIDY',
            'issue_date' => '2026-01-15',
        ];

        $cnResponse = $this->post('/accounts-receivable/credit-notes', $cnPayload);
        $cnResponse->assertRedirect();

        $creditNote = CreditNote::where('invoice_id', $invoice->id)->first();
        $this->assertNotNull($creditNote);
        $this->assertEquals('DRAFT', $creditNote->status);
        $this->assertEquals('2000.0000', $creditNote->amount);

        // Assert NO GL Journal Entry and NO balance reduction while in DRAFT status
        $this->assertFalse(JournalEntry::where('reference_number', 'JE-CN-' . $creditNote->credit_note_number)->exists());
        $invoice->refresh();
        $this->assertEquals('10000.0000', $invoice->patient_payable);
        $patient->refresh();
        $this->assertEquals('10000.0000', $patient->current_balance);

        // 2. Approve & Post Credit Note (Finance Manager)
        $this->actingAs($this->manager);
        $approveResponse = $this->post("/accounts-receivable/credit-notes/{$creditNote->id}/post");
        $approveResponse->assertRedirect();

        $creditNote->refresh();
        $this->assertEquals('POSTED', $creditNote->status);
        $this->assertEquals($this->manager->id, $creditNote->approved_by);

        // Assert Invoice Balance Reduced (10,000 - 2,000 = 8,000)
        $invoice->refresh();
        $this->assertEquals('8000.0000', $invoice->patient_payable);
        $this->assertEquals('2000.0000', $invoice->discount_amount);

        // Assert Patient Account Balance Reduced
        $patient->refresh();
        $this->assertEquals('8000.0000', $patient->current_balance);

        // Assert Balanced GL Journal Entry
        $je = JournalEntry::with('lines')->where('reference_number', 'JE-CN-' . $creditNote->credit_note_number)->first();
        $this->assertNotNull($je);
        $this->assertEquals('POSTED', $je->status);

        $totalDebit = (string) $je->lines->sum('debit');
        $totalCredit = (string) $je->lines->sum('credit');
        $this->assertEquals(0, bccomp($totalDebit, $totalCredit, 4));
        $this->assertEquals(0, bccomp($totalDebit, '2000.0000', 4));
    }

    /** @test */
    public function test_credit_note_direct_immediate_posting(): void
    {
        $this->actingAs($this->manager);

        $patient = PatientAccount::create([
            'patient_id_number' => 'MRN-CN-DIRECT',
            'full_name'         => 'Direct Post Patient',
            'current_balance'   => '5000.0000',
            'status'            => 'Active',
        ]);

        $invoice = Invoice::create([
            'invoice_number'     => 'INV-CN-DIRECT',
            'patient_account_id' => $patient->id,
            'invoice_date'       => '2026-01-10',
            'total_amount'       => '5000.0000',
            'patient_payable'    => '5000.0000',
            'paid_amount'        => '0.0000',
            'discount_amount'    => '0.0000',
            'status'             => 'UNPAID',
        ]);

        // Direct Post (save_as_draft = false)
        $response = $this->post('/accounts-receivable/credit-notes', [
            'invoice_id'    => $invoice->id,
            'amount'        => 1500.00,
            'reason'        => 'BILLING_ADJUSTMENT',
            'issue_date'    => '2026-01-15',
            'save_as_draft' => 0,
        ]);
        $response->assertRedirect();

        $creditNote = CreditNote::where('invoice_id', $invoice->id)->first();
        $this->assertNotNull($creditNote);
        $this->assertEquals('POSTED', $creditNote->status);

        $invoice->refresh();
        $this->assertEquals('3500.0000', $invoice->patient_payable);

        $patient->refresh();
        $this->assertEquals('3500.0000', $patient->current_balance);

        $je = JournalEntry::where('reference_number', 'JE-CN-' . $creditNote->credit_note_number)->first();
        $this->assertNotNull($je);
        $this->assertEquals('POSTED', $je->status);
    }

    /** @test */
    public function test_statutory_discount_duplicate_prevention_and_reversal_allowance(): void
    {
        $this->actingAs($this->manager);

        $patient = PatientAccount::create([
            'patient_id_number' => 'MRN-STAT-01',
            'full_name'         => 'Statutory Patient',
            'current_balance'   => '10000.0000',
            'status'            => 'Active',
        ]);

        $invoice = Invoice::create([
            'invoice_number'     => 'INV-STAT-01',
            'patient_account_id' => $patient->id,
            'invoice_date'       => '2026-01-10',
            'total_amount'       => '10000.0000',
            'patient_payable'    => '10000.0000',
            'paid_amount'        => '0.0000',
            'discount_amount'    => '0.0000',
            'status'             => 'UNPAID',
        ]);

        // 1. Issue 1st Statutory Discount (Senior Citizen)
        $res1 = $this->post('/accounts-receivable/credit-notes', [
            'invoice_id' => $invoice->id,
            'amount'     => 2000.00,
            'reason'     => 'SENIOR_CITIZEN_DISCOUNT',
            'issue_date' => '2026-01-15',
        ]);
        $res1->assertRedirect();
        $cn1 = CreditNote::where('invoice_id', $invoice->id)->first();
        $this->assertNotNull($cn1);

        // 2. Attempt 2nd Statutory Discount (PWD) on same invoice -> MUST FAIL VALIDATION
        $res2 = $this->post('/accounts-receivable/credit-notes', [
            'invoice_id' => $invoice->id,
            'amount'     => 1000.00,
            'reason'     => 'PWD_DISCOUNT',
            'issue_date' => '2026-01-16',
        ]);
        $res2->assertSessionHasErrors('reason');

        // 3. Issue valid non-statutory discount (Charity Subsidy) on same invoice -> MUST SUCCEED
        $res3 = $this->post('/accounts-receivable/credit-notes', [
            'invoice_id' => $invoice->id,
            'amount'     => 1000.00,
            'reason'     => 'CHARITY_SUBSIDY',
            'issue_date' => '2026-01-17',
        ]);
        $res3->assertRedirect();
        $this->assertEquals(2, CreditNote::where('invoice_id', $invoice->id)->count());

        // 4. Void 1st statutory credit note -> allows new statutory credit note
        $cn1->update(['status' => 'VOID']);
        $res4 = $this->post('/accounts-receivable/credit-notes', [
            'invoice_id' => $invoice->id,
            'amount'     => 2000.00,
            'reason'     => 'PWD_DISCOUNT',
            'issue_date' => '2026-01-18',
        ]);
        $res4->assertRedirect();
        $this->assertEquals(3, CreditNote::where('invoice_id', $invoice->id)->count());
    }

    /** @test */
    public function test_customer_statement_of_account_generation_and_export(): void
    {
        $this->actingAs($this->accountant);

        $patient = PatientAccount::create([
            'patient_id_number' => 'MRN-SOA-01',
            'full_name'         => 'Manuel L. Quezon',
            'current_balance'   => '15000.0000',
            'status'            => 'Active',
        ]);

        $invoice = Invoice::create([
            'invoice_number'     => 'INV-SOA-01',
            'patient_account_id' => $patient->id,
            'invoice_date'       => '2026-01-10',
            'total_amount'       => '20000.0000',
            'patient_payable'    => '20000.0000',
            'paid_amount'        => '5000.0000',
            'status'             => 'PARTIAL',
        ]);

        Payment::create([
            'invoice_id'        => $invoice->id,
            'patient_account_id'=> $patient->id,
            'payment_date'      => '2026-01-12',
            'amount'            => '5000.0000',
            'payment_method'    => 'CASH',
            'payment_reference' => 'OR-2026-0019',
            'status'            => 'COMPLETED',
        ]);

        // Add a posted credit note for ₱3,000.00
        CreditNote::create([
            'credit_note_number' => 'CN-SOA-POSTED',
            'invoice_id'         => $invoice->id,
            'patient_account_id' => $patient->id,
            'issue_date'         => '2026-01-14',
            'amount'             => '3000.0000',
            'reason'             => 'SENIOR_CITIZEN_DISCOUNT',
            'status'             => 'POSTED',
        ]);

        // Add a VOID credit note for ₱2,000.00 (should be excluded from SOA movements)
        CreditNote::create([
            'credit_note_number' => 'CN-SOA-VOIDED',
            'invoice_id'         => $invoice->id,
            'patient_account_id' => $patient->id,
            'issue_date'         => '2026-01-14',
            'amount'             => '2000.0000',
            'reason'             => 'ERRONEOUS_ADJUSTMENT',
            'status'             => 'VOID',
        ]);

        // 1. View SOA in App
        $response = $this->get("/accounts-receivable/customer-statements?patient_id={$patient->id}&start_date=2026-01-01&end_date=2026-01-31");
        $response->assertStatus(200);
        $response->assertSee('Manuel L. Quezon');
        $response->assertSee('INV-SOA-01');
        $response->assertSee('OR-2026-0019');
        $response->assertSee('CN-SOA-POSTED');
        $response->assertDontSee('CN-SOA-VOIDED');

        // 2. Printable SOA
        $printResponse = $this->get("/accounts-receivable/customer-statements/print?patient_id={$patient->id}&start_date=2026-01-01&end_date=2026-01-31");
        $printResponse->assertStatus(200);
        $printResponse->assertSee('Statement of Account');

        // 3. Export CSV
        $exportResponse = $this->get("/accounts-receivable/customer-statements/export?patient_id={$patient->id}&start_date=2026-01-01&end_date=2026-01-31");
        $exportResponse->assertStatus(200);
        $exportResponse->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
    }

    /** @test */
    public function test_accounts_receivable_role_authorization_and_sod(): void
    {
        $cashier = User::factory()->create(['role' => 'Cashier', 'name' => 'Cashier Only']);

        // Cashier can view patient accounts (needed for collections)
        $this->actingAs($cashier);
        $res1 = $this->get('/accounts-receivable/patients');
        $res1->assertStatus(200);

        // Cashier cannot access invoicing creation
        $res2 = $this->get('/accounts-receivable/invoices');
        $res2->assertStatus(403);

        // Billing clerk can view and create invoices, but cannot approve credit notes
        $this->actingAs($this->billingClerk);
        $res3 = $this->get('/accounts-receivable/invoices');
        $res3->assertStatus(200);

        $patient = PatientAccount::create(['patient_id_number' => 'MRN-SOD-01', 'full_name' => 'SOD Patient']);
        $inv = Invoice::create([
            'invoice_number'     => 'INV-SOD-01',
            'patient_account_id' => $patient->id,
            'invoice_date'       => '2026-01-10',
            'total_amount'       => '5000.0000',
            'patient_payable'    => '5000.0000',
            'status'             => 'UNPAID',
        ]);
        $cn = CreditNote::create([
            'credit_note_number' => 'CN-SOD-01',
            'invoice_id'         => $inv->id,
            'patient_account_id' => $patient->id,
            'issue_date'         => '2026-01-10',
            'amount'             => '1000.0000',
            'reason'             => 'DISCOUNT',
            'status'             => 'DRAFT',
        ]);

        // BillingClerk cannot approve credit note
        $res4 = $this->post("/accounts-receivable/credit-notes/{$cn->id}/approve");
        $res4->assertStatus(403);

        // FinanceManager can approve credit note
        $this->actingAs($this->manager);
        $res5 = $this->post("/accounts-receivable/credit-notes/{$cn->id}/approve");
        $res5->assertRedirect();
    }

    /** @test */
    public function test_credit_note_validation_rejects_amount_exceeding_invoice_balance(): void
    {
        $this->actingAs($this->billingClerk);

        $patient = PatientAccount::create([
            'patient_id_number' => 'MRN-CN-EXCEED-01',
            'full_name'         => 'Validation Test Patient',
            'current_balance'   => '3000.0000',
            'status'            => 'Active',
        ]);

        $invoice = Invoice::create([
            'invoice_number'     => 'INV-CN-EXCEED-01',
            'patient_account_id' => $patient->id,
            'invoice_date'       => '2026-01-10',
            'total_amount'       => '3000.0000',
            'patient_payable'    => '3000.0000',
            'paid_amount'        => '0.0000',
            'discount_amount'    => '0.0000',
            'status'             => 'UNPAID',
        ]);

        // Attempt to create credit note with amount > patient_payable
        $response = $this->from('/accounts-receivable/credit-notes')->post('/accounts-receivable/credit-notes', [
            'invoice_id' => $invoice->id,
            'amount'     => 5000.00,
            'reason'     => 'SENIOR_CITIZEN_DISCOUNT',
            'issue_date' => '2026-01-15',
        ]);

        $response->assertRedirect('/accounts-receivable/credit-notes');
        $response->assertSessionHasErrors('amount');
        $this->assertDatabaseMissing('credit_notes', ['invoice_id' => $invoice->id]);
    }

    /** @test */
    public function test_credit_note_voiding_and_reversal_gl_posting(): void
    {
        $this->actingAs($this->manager);

        $patient = PatientAccount::create([
            'patient_id_number' => 'MRN-CN-VOID-01',
            'full_name'         => 'Void Test Patient',
            'current_balance'   => '10000.0000',
            'status'            => 'Active',
        ]);

        $invoice = Invoice::create([
            'invoice_number'     => 'INV-CN-VOID-01',
            'patient_account_id' => $patient->id,
            'invoice_date'       => '2026-01-10',
            'total_amount'       => '10000.0000',
            'patient_payable'    => '10000.0000',
            'paid_amount'        => '0.0000',
            'discount_amount'    => '0.0000',
            'status'             => 'UNPAID',
        ]);

        // 1. Create and Approve Credit Note for ₱4,000.00
        $cn = CreditNote::create([
            'credit_note_number' => 'CN-VOID-001',
            'invoice_id'         => $invoice->id,
            'patient_account_id' => $patient->id,
            'issue_date'         => '2026-01-12',
            'amount'             => '4000.0000',
            'reason'             => 'SENIOR_CITIZEN_DISCOUNT',
            'status'             => 'DRAFT',
        ]);

        $this->post("/accounts-receivable/credit-notes/{$cn->id}/approve");

        $invoice->refresh();
        $patient->refresh();
        $this->assertEquals('6000.0000', $invoice->patient_payable);
        $this->assertEquals('6000.0000', $patient->current_balance);

        // 2. Void the Credit Note
        $voidResponse = $this->post("/accounts-receivable/credit-notes/{$cn->id}/void", [
            'void_reason' => 'Erroneous Senior Citizen Discount applied',
        ]);
        $voidResponse->assertRedirect();

        $cn->refresh();
        $invoice->refresh();
        $patient->refresh();

        $this->assertEquals('VOID', $cn->status);
        $this->assertEquals('10000.0000', $invoice->patient_payable);
        $this->assertEquals('0.0000', $invoice->discount_amount);
        $this->assertEquals('10000.0000', $patient->current_balance);

        // 3. Verify Reversing Journal Entry
        $revJe = JournalEntry::with('lines')->where('reference_number', 'JE-REV-CN-' . $cn->credit_note_number)->first();
        $this->assertNotNull($revJe);
        $this->assertEquals('POSTED', $revJe->status);
        $this->assertEquals(0, bccomp((string) $revJe->lines->sum('debit'), (string) $revJe->lines->sum('credit'), 4));
        $this->assertEquals(0, bccomp((string) $revJe->lines->sum('debit'), '4000.0000', 4));
    }

    /** @test */
    public function test_credit_note_model_scopes(): void
    {
        $patient = PatientAccount::create(['patient_id_number' => 'MRN-SCOPE-01', 'full_name' => 'Scope Patient']);
        $inv = Invoice::create([
            'invoice_number'     => 'INV-SCOPE-01',
            'patient_account_id' => $patient->id,
            'invoice_date'       => '2026-01-10',
            'total_amount'       => '5000.0000',
            'patient_payable'    => '5000.0000',
            'status'             => 'UNPAID',
        ]);

        CreditNote::create([
            'credit_note_number' => 'CN-SCOPE-DRAFT',
            'invoice_id'         => $inv->id,
            'patient_account_id' => $patient->id,
            'issue_date'         => '2026-01-10',
            'amount'             => '1000.0000',
            'reason'             => 'DISCOUNT',
            'status'             => 'DRAFT',
        ]);

        CreditNote::create([
            'credit_note_number' => 'CN-SCOPE-POSTED',
            'invoice_id'         => $inv->id,
            'patient_account_id' => $patient->id,
            'issue_date'         => '2026-01-11',
            'amount'             => '2000.0000',
            'reason'             => 'DISCOUNT',
            'status'             => 'POSTED',
        ]);

        $this->assertCount(1, CreditNote::draft()->get());
        $this->assertCount(1, CreditNote::posted()->get());
        $this->assertCount(2, CreditNote::forInvoice($inv->id)->get());
    }

    /** @test */
    public function test_patient_accounts_directory_renders_effective_statutory_badge_from_active_credit_note(): void
    {
        $this->actingAs($this->accountant);

        $patient = PatientAccount::create([
            'patient_id_number' => 'MRN-BADGE-01',
            'full_name'         => 'Badge Test Patient',
            'discount_category' => 'NONE',
            'current_balance'   => '8000.0000',
            'status'            => 'Active',
        ]);

        $invoice = Invoice::create([
            'invoice_number'     => 'INV-BADGE-01',
            'patient_account_id' => $patient->id,
            'invoice_date'       => '2026-01-10',
            'total_amount'       => '10000.0000',
            'patient_payable'    => '8000.0000',
            'status'             => 'PARTIAL',
        ]);

        CreditNote::create([
            'credit_note_number' => 'CN-BADGE-01',
            'invoice_id'         => $invoice->id,
            'patient_account_id' => $patient->id,
            'issue_date'         => '2026-01-10',
            'amount'             => '2000.0000',
            'reason'             => 'PWD_DISCOUNT',
            'status'             => 'POSTED',
        ]);

        $response = $this->get('/accounts-receivable/patient-accounts?search=Badge+Test+Patient');
        $response->assertStatus(200);
        $response->assertSee('PWD 20%');
        $response->assertSee('PWD (RA 10754)');

        // Assert Invoicing & Billing view displays PWD badge and payload
        $invResponse = $this->get('/accounts-receivable/invoices?search=INV-BADGE-01');
        $invResponse->assertStatus(200);
        $invResponse->assertSee('PWD 20%');
        $invResponse->assertSee('&quot;statutory_category&quot;:&quot;PWD&quot;', false);

        // Assert Receivable Aging displays PWD badge beside admission type
        $agingResponse = $this->get('/accounts-receivable/receivable-aging?search=Badge+Test+Patient');
        $agingResponse->assertStatus(200);
        $agingResponse->assertSee('PWD 20%');
    }

    /** @test */
    public function test_customer_statement_filters_by_admission_type_and_patient(): void
    {
        $this->actingAs($this->accountant);

        $inpatient = PatientAccount::create([
            'patient_id_number' => 'MRN-IPD-01',
            'full_name'         => 'Inpatient John Doe',
            'admission_type'    => 'INPATIENT',
            'current_balance'   => '50000.0000',
            'status'            => 'Active',
        ]);

        $outpatient = PatientAccount::create([
            'patient_id_number' => 'MRN-OPD-01',
            'full_name'         => 'Outpatient Jane Smith',
            'admission_type'    => 'OUTPATIENT',
            'current_balance'   => '1500.0000',
            'status'            => 'Active',
        ]);

        Invoice::create([
            'invoice_number'     => 'INV-OPD-001',
            'patient_account_id' => $outpatient->id,
            'invoice_date'       => '2026-01-15',
            'total_amount'       => '1500.0000',
            'patient_payable'    => '1500.0000',
            'status'             => 'UNPAID',
        ]);

        // 1. Filter statements by OUTPATIENT
        $response = $this->get('/accounts-receivable/customer-statements?admission_type=OUTPATIENT');
        $response->assertStatus(200);
        $response->assertSee('Outpatient Jane Smith');
        $response->assertDontSee('Inpatient John Doe');

        // 2. Filter statements for Outpatient Jane Smith's specific ledger
        $soaResponse = $this->get("/accounts-receivable/customer-statements?patient_id={$outpatient->id}&admission_type=OUTPATIENT");
        $soaResponse->assertStatus(200);
        $soaResponse->assertSee('Statement Ledger for Outpatient Jane Smith');
        $soaResponse->assertSee('INV-OPD-001');
        $soaResponse->assertSee('1,500.00');
    }
}
