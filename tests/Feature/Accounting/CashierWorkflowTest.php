<?php

declare(strict_types=1);

namespace Tests\Feature\Accounting;

use App\Models\Account;
use App\Models\CashierShift;
use App\Models\Invoice;
use App\Models\JournalEntry;
use App\Models\OfficialReceipt;
use App\Models\PatientAccount;
use App\Models\Payment;
use App\Models\User;
use Database\Seeders\PhilippineHealthcareChartOfAccountsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CashierWorkflowTest extends TestCase
{
    use RefreshDatabase;

    private User $cashier;
    private CashierShift $shift;

    protected function setUp(): void
    {
        parent::setUp();

        // 1. Seed Chart of Accounts
        $this->seed(PhilippineHealthcareChartOfAccountsSeeder::class);

        // 2. Setup Cashier User and Open Shift
        $this->cashier = User::factory()->create([
            'name'  => 'Maria Santos (Cashier)',
            'role'  => 'Cashier',
            'email' => 'cashier@hospital.local',
        ]);

        $this->shift = CashierShift::create([
            'shift_code'         => 'SHIFT-TEST-001',
            'cashier_id'         => $this->cashier->id,
            'opened_at'          => now(),
            'opening_cash_float' => 5000.00,
            'status'             => 'OPEN',
        ]);
    }

    /** @test */
    public function test_cashier_can_settle_open_patient_invoice_and_generate_official_receipt(): void
    {
        $this->actingAs($this->cashier);

        // 1. Create Patient Account & Open Invoice
        $patient = PatientAccount::factory()->create([
            'full_name'       => 'Vicente Sotto',
            'current_balance' => 4500.00,
        ]);

        $invoice = Invoice::create([
            'invoice_number'     => 'INV-2026-9011',
            'patient_account_id' => $patient->id,
            'invoice_date'       => now()->toDateString(),
            'total_amount'       => 10000.00,
            'insurance_covered'  => 5500.00,
            'patient_payable'    => 4500.00,
            'status'             => 'UNPAID',
        ]);

        // 2. Submit Counter Settlement via Cashier POS
        $payload = [
            'invoice_id'      => $invoice->id,
            'amount'          => 4500.00,
            'payment_method'  => 'GCASH',
            'transaction_ref' => 'GCASH-TXN-88772211',
            'notes'           => 'Full copay settlement via GCash',
        ];

        $response = $this->post('/accounting/cashier/pay', $payload);

        $response->assertRedirect('/accounting/cashier')
            ->assertSessionHas('success');

        // 3. Assert Payment and Official Receipt Generation
        $payment = Payment::where('invoice_id', $invoice->id)->first();
        $this->assertNotNull($payment);
        $this->assertEquals('4500.0000', $payment->amount);
        $this->assertEquals('GCASH', $payment->payment_method);

        $or = OfficialReceipt::where('payment_id', $payment->id)->first();
        $this->assertNotNull($or);
        $this->assertEquals($payment->amount, $or->total_amount_collected);

        // 4. Assert Invoice Status and Balance Updated
        $this->assertEquals('SETTLED', $invoice->fresh()->status);
        $this->assertEquals('0.0000', $invoice->fresh()->patient_payable);

        // 5. Assert Balanced General Ledger Journal Posted
        $glEntry = JournalEntry::with('lines.account')
            ->where('reference_number', 'LIKE', '%' . $payment->payment_reference . '%')
            ->first();

        $this->assertNotNull($glEntry);
        $this->assertEquals('POSTED', $glEntry->status);
        $this->assertCount(2, $glEntry->lines);

        // Assert Double-Entry Invariance
        $totalDebit = (string) $glEntry->lines->sum('debit');
        $totalCredit = (string) $glEntry->lines->sum('credit');
        $this->assertEquals(0, bccomp($totalDebit, $totalCredit, 4));
        $this->assertEquals(0, bccomp($totalDebit, '4500.0000', 4));
    }
}
