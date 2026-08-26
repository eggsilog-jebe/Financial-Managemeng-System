<?php

declare(strict_types=1);

namespace Tests\Feature\Accounting;

use App\Models\Account;
use App\Models\BankAccount;
use App\Models\Bir2307Certificate;
use App\Models\CheckRegister;
use App\Models\DisbursementVoucher;
use App\Models\FiscalPeriod;
use App\Models\JournalEntry;
use App\Models\PurchaseBill;
use App\Models\ThreeWayMatch;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class AccountsPayableModuleTest extends TestCase
{
    use RefreshDatabase;

    private User $cfo;
    private User $manager;
    private User $accountant;

    protected function setUp(): void
    {
        parent::setUp();

        $this->cfo = User::factory()->create(['role' => 'CFO', 'name' => 'Dr. CFO Master', 'email' => 'cfo@hospital.local']);
        $this->manager = User::factory()->create(['role' => 'FinanceManager', 'name' => 'Manager One', 'email' => 'manager@hospital.local']);
        $this->accountant = User::factory()->create(['role' => 'StaffAccountant', 'name' => 'Staff AP', 'email' => 'accountant@hospital.local']);

        // Initialize open fiscal period
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
    public function test_vendor_management_crud_and_status_toggle(): void
    {
        $this->actingAs($this->accountant);

        // 1. View Vendors Table
        $indexResponse = $this->get('/accounts-payable/vendors');
        $indexResponse->assertStatus(200);

        // 2. Create New Vendor
        $createPayload = [
            'vendor_code'        => 'VND-TEST-001',
            'name'               => 'Metro Bio-Pharma Solutions Inc',
            'tin'                => '401-998-112-000',
            'contact_person'     => 'Dr. Eduardo Santos',
            'phone'              => '+63 (02) 8811-2233',
            'email'              => 'billing@metropharma.ph',
            'payment_terms_days' => 45,
            'is_active'          => 1,
        ];

        $storeResponse = $this->post('/accounts-payable/vendors', $createPayload);
        $storeResponse->assertRedirect();

        $vendor = Vendor::where('code', 'VND-TEST-001')->first();
        $this->assertNotNull($vendor);
        $this->assertEquals('Metro Bio-Pharma Solutions Inc', $vendor->name);
        $this->assertEquals(45, $vendor->payment_terms_days);
        $this->assertTrue($vendor->is_active);

        // 3. Update Vendor
        $updatePayload = [
            'name'               => 'Metro Bio-Pharma Corp',
            'tin'                => '401-998-112-000',
            'payment_terms_days' => 60,
        ];

        $updateResponse = $this->put("/accounts-payable/vendors/{$vendor->id}", $updatePayload);
        $updateResponse->assertRedirect();
        $vendor->refresh();
        $this->assertEquals('Metro Bio-Pharma Corp', $vendor->name);
        $this->assertEquals(60, $vendor->payment_terms_days);

        // 4. Toggle Active Status
        $toggleResponse = $this->post("/accounts-payable/vendors/{$vendor->id}/toggle-status");
        $toggleResponse->assertRedirect();
        $vendor->refresh();
        $this->assertFalse($vendor->is_active);
        $this->assertEquals('Inactive', $vendor->status);
    }

    /** @test */
    public function test_purchase_bill_ingestion_3_way_matching_and_bir2307(): void
    {
        $this->actingAs($this->accountant);

        $vendor = Vendor::create([
            'code'               => 'VND-SUP-01',
            'name'               => 'Alpha Medical Gases',
            'tin'                => '100-200-300-000',
            'payment_terms_days' => 30,
            'status'             => 'Active',
        ]);

        $payload = [
            'vendor_id'             => $vendor->id,
            'bill_date'             => '2026-01-15',
            'due_date'              => '2026-02-15',
            'po_number'             => 'PO-2026-0991',
            'grn_number'            => 'GRN-2026-0991',
            'vendor_invoice_number' => 'SI-ALPHA-8811',
            'po_amount'             => 50000.00,
            'grn_amount'            => 50000.00,
            'items'                 => [
                [
                    'item_code'    => 'MED-GAS-01',
                    'description'  => 'Liquid Oxygen Tank Refill 500L',
                    'expense_type' => 'GOODS_INVENTORY',
                    'quantity'     => 10,
                    'unit_price'   => 5000.00, // 10 * 5000 = 50,000.00 gross
                    'atc_code'     => 'WI158', // 1% EWT = 500.00
                ],
            ],
        ];

        $response = $this->post('/accounts-payable/purchase-bills', $payload);
        $response->assertRedirect();

        $bill = PurchaseBill::with(['items', 'threeWayMatch', 'birCertificate'])->where('vendor_id', $vendor->id)->first();
        $this->assertNotNull($bill);
        $this->assertEquals('50000.0000', $bill->total_amount);

        // 3-Way Match evaluation: Matched with zero price variance
        $this->assertNotNull($bill->threeWayMatch);
        $this->assertEquals('MATCHED', $bill->threeWayMatch->match_status);
        $this->assertEquals('0.0000', $bill->threeWayMatch->price_variance);

        // BIR Form 2307 Certificate generation
        $this->assertNotNull($bill->birCertificate);
        $this->assertEquals('50000.0000', $bill->birCertificate->tax_base_amount);
        $this->assertEquals('0.0100', $bill->birCertificate->tax_rate);
        $this->assertEquals('500.0000', $bill->birCertificate->tax_withheld);
        $this->assertEquals('Alpha Medical Gases', $bill->birCertificate->payee_name);

        // Double-Entry AP Journal Entry
        $je = JournalEntry::with('lines')->where('reference_number', 'JE-AP-' . $bill->bill_number)->first();
        $this->assertNotNull($je);
        $this->assertEquals('POSTED', $je->status);

        $totalDebit = (string) $je->lines->sum('debit');
        $totalCredit = (string) $je->lines->sum('credit');
        $this->assertEquals(0, bccomp($totalDebit, $totalCredit, 4));
        $this->assertEquals(0, bccomp($totalDebit, '50000.0000', 4));
    }

    /** @test */
    public function test_purchase_bill_3_way_match_approval(): void
    {
        $this->actingAs($this->manager);

        $vendor = Vendor::create(['code' => 'VND-002', 'name' => 'Beta Lab Supplies', 'tin' => '222-333-444-000']);
        $bill = PurchaseBill::create([
            'bill_number'  => 'BILL-TEST-900',
            'vendor_id'    => $vendor->id,
            'bill_date'    => '2026-01-10',
            'due_date'     => '2026-02-10',
            'total_amount' => '30000.0000',
            'paid_amount'  => '0.0000',
            'status'       => 'UNPAID',
        ]);

        ThreeWayMatch::create([
            'purchase_bill_id'      => $bill->id,
            'po_number'             => 'PO-881',
            'grn_number'            => 'GRN-881',
            'vendor_invoice_number' => 'INV-881',
            'po_amount'             => '30000.0000',
            'grn_amount'            => '30000.0000',
            'invoice_amount'        => '30000.0000',
            'price_variance'        => '0.0000',
            'match_status'          => 'PENDING',
        ]);

        $approveResponse = $this->post("/accounts-payable/purchase-bills/{$bill->id}/approve");
        $approveResponse->assertRedirect();

        $bill->refresh();
        $this->assertEquals('APPROVED', $bill->status);
        $this->assertEquals('MATCHED', $bill->threeWayMatch->match_status);
        $this->assertEquals($this->manager->id, $bill->threeWayMatch->approved_by);
    }

    /** @test */
    public function test_disbursement_voucher_preparation_and_finance_approval(): void
    {
        $this->actingAs($this->accountant);

        $bank = BankAccount::create([
            'name'           => 'Operating Treasury Account',
            'bank_name'      => 'Metrobank Medical Center',
            'account_number' => '1029-9940-11',
            'gl_code'        => '1020',
            'purpose'        => 'AP Vendor Payouts',
            'balance'        => '500000.0000',
            'status'         => 'Active',
        ]);

        $vendor = Vendor::create(['code' => 'VND-003', 'name' => 'Gamma Pharmaceuticals', 'tin' => '333-444-555-000']);
        $bill = PurchaseBill::create([
            'bill_number'  => 'BILL-GAMMA-01',
            'vendor_id'    => $vendor->id,
            'bill_date'    => '2026-01-10',
            'due_date'     => '2026-02-10',
            'total_amount' => '100000.0000',
            'paid_amount'  => '0.0000',
            'status'       => 'APPROVED',
        ]);

        // 1. Prepare Voucher (Accountant)
        $prepPayload = [
            'purchase_bill_id' => $bill->id,
            'bank_account_id'  => $bank->id,
            'amount'           => 100000.00,
            'payment_method'   => 'CHECK',
            'voucher_date'     => '2026-01-20',
        ];

        $prepResponse = $this->post('/accounts-payable/invoices-vouchers/prepare-voucher', $prepPayload);
        $prepResponse->assertRedirect();

        $voucher = DisbursementVoucher::where('purchase_bill_id', $bill->id)->first();
        $this->assertNotNull($voucher);
        $this->assertEquals('DRAFT', $voucher->status);
        $this->assertEquals('100000.0000', $voucher->net_disbursed_amount);

        // 2. Finance Manager Approval
        $this->actingAs($this->manager);
        $approveResponse = $this->post("/accounts-payable/payment-approvals/{$voucher->id}/approve");
        $approveResponse->assertRedirect();

        $voucher->refresh();
        $this->assertEquals('APPROVED', $voucher->status);
        $this->assertEquals($this->manager->id, $voucher->approved_by);
    }

    /** @test */
    public function test_disbursement_release_settlement_and_check_register(): void
    {
        $this->actingAs($this->cfo);

        $bank = BankAccount::create([
            'name'           => 'Operating Account',
            'bank_name'      => 'BDO Pasig',
            'account_number' => '4499-1122-00',
            'gl_code'        => '1020',
            'purpose'        => 'Vendor Disbursements',
            'balance'        => '200000.0000',
            'status'         => 'Active',
        ]);

        $vendor = Vendor::create(['code' => 'VND-004', 'name' => 'Delta Bio-Instruments', 'tin' => '444-555-666-000']);
        $bill = PurchaseBill::create([
            'bill_number'  => 'BILL-DELTA-101',
            'vendor_id'    => $vendor->id,
            'bill_date'    => '2026-01-12',
            'due_date'     => '2026-02-12',
            'total_amount' => '75000.0000',
            'paid_amount'  => '0.0000',
            'status'       => 'APPROVED',
        ]);

        $voucher = DisbursementVoucher::create([
            'voucher_number'       => 'DV-20260120-DEL',
            'purchase_bill_id'     => $bill->id,
            'bank_account_id'      => $bank->id,
            'voucher_date'         => '2026-01-20',
            'payee_name'           => $vendor->name,
            'gross_amount'         => '75000.0000',
            'withheld_tax_amount'  => '0.0000',
            'net_disbursed_amount' => '75000.0000',
            'payment_method'       => 'CHECK',
            'status'               => 'APPROVED',
            'approved_by'          => $this->manager->id,
        ]);

        // Release Payment Action
        $releasePayload = [
            'check_number' => 'CHK-2026-990188',
            'check_date'   => '2026-01-20',
            'notes'        => 'Released to Delta Authorized Courier',
        ];

        $releaseResponse = $this->post("/accounts-payable/payment-approvals/{$voucher->id}/release", $releasePayload);
        $releaseResponse->assertRedirect();

        $voucher->refresh();
        $this->assertEquals('RELEASED', $voucher->status);
        $this->assertEquals('CHK-2026-990188', $voucher->check_or_eft_ref);

        // Assert Check Register entry
        $check = CheckRegister::where('disbursement_voucher_id', $voucher->id)->first();
        $this->assertNotNull($check);
        $this->assertEquals('CHK-2026-990188', $check->check_number);
        $this->assertEquals('75000.0000', $check->amount);
        $this->assertEquals('RELEASED', $check->status);

        // Assert Bank Account Balance Deducted (200,000 - 75,000 = 125,000)
        $bank->refresh();
        $this->assertEquals('125000.0000', $bank->balance);

        // Assert Purchase Bill Settled
        $bill->refresh();
        $this->assertEquals('75000.0000', $bill->paid_amount);
        $this->assertEquals('PAID', $bill->status);

        // Assert Balanced GL Settlement Journal Entry
        $je = JournalEntry::with('lines')->where('reference_number', 'JE-DISB-' . $voucher->voucher_number)->first();
        $this->assertNotNull($je);
        $this->assertEquals('POSTED', $je->status);
        $this->assertCount(2, $je->lines);

        $totalDebit = (string) $je->lines->sum('debit');
        $totalCredit = (string) $je->lines->sum('credit');
        $this->assertEquals(0, bccomp($totalDebit, $totalCredit, 4));
        $this->assertEquals(0, bccomp($totalDebit, '75000.0000', 4));
    }

    /** @test */
    public function test_payable_aging_schedule_and_csv_export(): void
    {
        $this->actingAs($this->accountant);

        $vendor1 = Vendor::create(['code' => 'VND-001', 'name' => 'Current Vendor Co', 'tin' => '111-111-111-000', 'payment_terms_days' => 30]);
        $vendor2 = Vendor::create(['code' => 'VND-002', 'name' => 'Overdue Vendor Inc', 'tin' => '222-222-222-000', 'payment_terms_days' => 15]);

        // Current Bill (Due in future: 2026-02-15)
        PurchaseBill::create([
            'bill_number'  => 'BILL-CUR-01',
            'vendor_id'    => $vendor1->id,
            'bill_date'    => '2026-01-15',
            'due_date'     => '2026-02-15',
            'total_amount' => '20000.0000',
            'paid_amount'  => '0.0000',
            'status'       => 'APPROVED',
        ]);

        // Overdue Bill (Due 40 days prior: 2025-12-06, falls in 31-60 days bucket as of 2026-01-15)
        PurchaseBill::create([
            'bill_number'  => 'BILL-OVD-01',
            'vendor_id'    => $vendor2->id,
            'bill_date'    => '2025-11-20',
            'due_date'     => '2025-12-06',
            'total_amount' => '35000.0000',
            'paid_amount'  => '0.0000',
            'status'       => 'APPROVED',
        ]);

        // 2. Export Payable Aging CSV
        $exportResponse = $this->get('/accounts-payable/payable-aging/export?as_of_date=2026-01-15');
        $exportResponse->assertStatus(200);
        $exportResponse->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
    }

    /** @test */
    public function test_segregation_of_duties_role_authorization(): void
    {
        $cashier = User::factory()->create(['role' => 'Cashier', 'name' => 'Cashier Only']);

        // Cashier cannot access vendor management
        $this->actingAs($cashier);
        $res1 = $this->get('/accounts-payable/vendors');
        $res1->assertStatus(403);

        // Staff Accountant can access, but cannot approve disbursement or release
        $this->actingAs($this->accountant);
        $res2 = $this->get('/accounts-payable/vendors');
        $res2->assertStatus(200);

        $bank = BankAccount::create([
            'name'           => 'Bank Acc',
            'bank_name'      => 'BPI',
            'account_number' => '112233',
            'gl_code'        => '1020',
            'purpose'        => 'Treasury',
            'balance'        => '10000.00',
            'status'         => 'Active',
        ]);
        $vendor = Vendor::create(['code' => 'VND-009', 'name' => 'Test Vendor']);
        $voucher = DisbursementVoucher::create([
            'voucher_number'       => 'DV-SOD-001',
            'bank_account_id'      => $bank->id,
            'voucher_date'         => '2026-01-20',
            'payee_name'           => 'Test Vendor',
            'gross_amount'         => '1000.0000',
            'net_disbursed_amount' => '1000.0000',
            'payment_method'       => 'CHECK',
            'status'               => 'DRAFT',
        ]);

        // StaffAccountant cannot approve voucher
        $res3 = $this->post("/accounts-payable/payment-approvals/{$voucher->id}/approve");
        $res3->assertStatus(403);

        // FinanceManager can approve voucher, but cannot release
        $this->actingAs($this->manager);
        $res4 = $this->post("/accounts-payable/payment-approvals/{$voucher->id}/approve");
        $res4->assertRedirect();

        $res5 = $this->post("/accounts-payable/payment-approvals/{$voucher->id}/release", ['check_number' => 'CHK-123']);
        $res5->assertStatus(403);

        // CFO can release
        $this->actingAs($this->cfo);
        $res6 = $this->post("/accounts-payable/payment-approvals/{$voucher->id}/release", ['check_number' => 'CHK-123']);
        $res6->assertRedirect();
    }
}
